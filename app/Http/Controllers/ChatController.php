<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    public function send(Request $request, \App\Services\MessageService $messageService)
    {
        $request->validate([
            'receiver_id' => 'required',
            'message' => 'required|string',
            'type' => 'nullable|string',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $this->cleanExpiredVoiceMessages();
        
        $result = $messageService->processMessage(
            $user, 
            $request->receiver_id, 
            $request->message, 
            $request->type ?? 'text'
        );

        return response()->json($result);
    }

    public function getMessages($receiverId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $conversation = DB::table('conversations')
            ->where(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $user->id)->where('user2_id', $receiverId);
            })
            ->orWhere(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $receiverId)->where('user2_id', $user->id);
            })
            ->first();

        if (!$conversation) {
            return response()->json(['data' => []]);
        }

        // Mark messages as read
        Message::where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $deletedAt = $conversation->user1_id == $user->id ? $conversation->deleted_at_user1 : $conversation->deleted_at_user2;

        $messagesQuery = Message::where('conversation_id', $conversation->id);
        
        if ($deletedAt) {
            $messagesQuery->where('created_at', '>', $deletedAt);
        }

        $this->cleanExpiredVoiceMessages();

        $messages = $messagesQuery->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($msg) use ($user) {
                return [
                    'id' => $msg->id,
                    'text' => $msg->text,
                    'sender_type' => $msg->sender_id == $user->id ? 'user' : 'other',
                    'created_at' => $msg->created_at,
                    'type' => $msg->type,
                    'listen_count' => $msg->listen_count,
                ];
            });

        return response()->json(['data' => $messages]);
    }

    public function deleteConversation($receiverId)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $conversation = DB::table('conversations')
            ->where(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $user->id)->where('user2_id', $receiverId);
            })
            ->orWhere(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $receiverId)->where('user2_id', $user->id);
            })
            ->first();

        if ($conversation) {
            $column = $conversation->user1_id == $user->id ? 'deleted_at_user1' : 'deleted_at_user2';
            DB::table('conversations')
                ->where('id', $conversation->id)
                ->update([$column => now()]);
        }

        return response()->json(['status' => 'success']);
    }

    public function getConversations()
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $conversations = DB::table('conversations')
            ->where('user1_id', $user->id)
            ->orWhere('user2_id', $user->id)
            ->get()
            ->filter(function ($conv) use ($user) {
                $deletedAt = $conv->user1_id == $user->id ? $conv->deleted_at_user1 : $conv->deleted_at_user2;
                if ($deletedAt) {
                    return DB::table('messages')
                        ->where('conversation_id', $conv->id)
                        ->where('created_at', '>', $deletedAt)
                        ->exists();
                }
                // If there are no messages at all, you might want to hide it too, but let's just return true if not deleted
                return true;
            })
            ->map(function ($conv) use ($user) {
                $otherUserId = $conv->user1_id == $user->id ? $conv->user2_id : $conv->user1_id;
                $otherUser = User::find($otherUserId);
                
                if ($otherUser) {
                    $hasBlockedMe = DB::table('blocks')
                        ->where('user_id', $otherUserId)
                        ->where('blocked_id', $user->id)
                        ->exists();

                    if ($hasBlockedMe) {
                        $otherUser->name = 'Loopin Kullanıcısı';
                        $otherUser->avatar_url = '';
                        $otherUser->bio = '';
                        $otherUser->photos = [];
                    }
                }

                $limit = \App\Models\InteractionLimit::where('conversation_id', $conv->id)->first();
                
                // Get the last message
                $lastMsgQuery = Message::where('conversation_id', $conv->id);
                $deletedAt = $conv->user1_id == $user->id ? $conv->deleted_at_user1 : $conv->deleted_at_user2;
                if ($deletedAt) {
                    $lastMsgQuery->where('created_at', '>', $deletedAt);
                }
                
                $lastMessage = $lastMsgQuery->orderBy('created_at', 'desc')->first();
                
                // Get unread message count
                $unreadCount = Message::where('conversation_id', $conv->id)
                    ->where('sender_id', '!=', $user->id)
                    ->where('is_read', false)
                    ->count();
                
                return [
                    'id' => $conv->id,
                    'user' => $otherUser,
                    'message_count' => $limit ? $limit->message_count : 0,
                    'is_unlocked' => $limit ? $limit->is_paid : false,
                    'last_message' => $lastMessage ? $lastMessage->text : 'Sohbete devam et',
                    'last_message_time' => $lastMessage ? $lastMessage->created_at : null,
                    'unread_count' => $unreadCount,
                ];
            })
            ->sortByDesc('last_message_time')
            ->values();

        return response()->json(['data' => $conversations]);
    }

    private function isModerationBlocked($text)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type'  => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model'       => 'gpt-4o-mini',
                'messages'    => [
                    [
                        'role' => 'system', 
                        'content' => 'Sen bir güvenlik moderatörüsün. Kullanıcının mesajında telefon numarası, Instagram, Snapchat, X, Twitter, Telegram, WhatsApp gibi iletişim veya sosyal medya bilgileri verilmeye çalışılıyor mu? SADECE "EVET" veya "HAYIR" yaz.'
                    ],
                    ['role' => 'user', 'content' => $text]
                ],
                'temperature' => 0.0,
            ]);

            $content = trim($response->json('choices.0.message.content'));
            return str_contains(strtoupper($content), 'EVET');
        } catch (\Exception $e) {
            \Log::error('Moderation error: ' . $e->getMessage());
            return false; // Fail open if API fails
        }
    }

    public function listenMessage($id)
    {
        $user = Auth::user();
        if (!$user) return response()->json(['message' => 'Unauthorized'], 401);

        $message = Message::findOrFail($id);
        
        if ($message->type === 'voice') {
            if ($message->listen_count >= 3) {
                return response()->json([
                    'status' => 'expired',
                    'listen_count' => $message->listen_count,
                    'message' => 'Bu ses kaydı tekrar dinlenemez.'
                ]);
            }

            $message->increment('listen_count');

            if ($message->listen_count >= 3) {
                try {
                    $oldPath = "voice_messages/message_{$message->id}.mp3";
                    $newPath = "silinen_ses_kayitlari/message_{$message->id}.mp3";
                    
                    if (\Storage::disk('r2')->exists($oldPath)) {
                        \Storage::disk('r2')->copy($oldPath, $newPath);
                        \Storage::disk('r2')->delete($oldPath);
                    }
                } catch (\Exception $e) {
                    \Log::error('R2 Voice move failed: ' . $e->getMessage());
                }
            }

            return response()->json([
                'status' => $message->listen_count >= 3 ? 'expired' : 'success',
                'listen_count' => $message->listen_count
            ]);
        } elseif ($message->type === 'image') {
            if ($message->listen_count >= 1) {
                return response()->json([
                    'status' => 'expired',
                    'listen_count' => $message->listen_count,
                    'message' => 'Bu resim tekrar görüntülenemez.'
                ]);
            }

            $message->increment('listen_count');

            try {
                $oldPath = "images/message_{$message->id}.jpg";
                $newPath = "silinen_resimler/message_{$message->id}.jpg";
                
                if (\Storage::disk('r2')->exists($oldPath)) {
                    \Storage::disk('r2')->copy($oldPath, $newPath);
                    \Storage::disk('r2')->delete($oldPath);
                }
            } catch (\Exception $e) {
                \Log::error('R2 Image move failed: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'expired',
                'listen_count' => $message->listen_count
            ]);
        }

        return response()->json(['message' => 'Invalid message type'], 400);
    }

    private function cleanExpiredVoiceMessages()
    {
        $expiredMessages = Message::where('type', 'voice')
            ->where('listen_count', '<', 3)
            ->where('created_at', '<', now()->subDays(7))
            ->get();

        foreach ($expiredMessages as $message) {
            $message->update(['listen_count' => 3]);
            
            try {
                $oldPath = "voice_messages/message_{$message->id}.mp3";
                $newPath = "silinen_ses_kayitlari/message_{$message->id}.mp3";
                
                if (\Storage::disk('r2')->exists($oldPath)) {
                    \Storage::disk('r2')->copy($oldPath, $newPath);
                    \Storage::disk('r2')->delete($oldPath);
                }
            } catch (\Exception $e) {
                \Log::error('R2 Voice move on timeout failed: ' . $e->getMessage());
            }
        }
    }
}
