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
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $result = $messageService->processMessage($user, $request->receiver_id, $request->message);

        return response()->json($result);
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
            ->map(function ($conv) use ($user) {
                $otherUserId = $conv->user1_id == $user->id ? $conv->user2_id : $conv->user1_id;
                $otherUser = User::find($otherUserId);
                $limit = \App\Models\InteractionLimit::where('conversation_id', $conv->id)->first();
                
                return [
                    'id' => $conv->id,
                    'user' => $otherUser,
                    'message_count' => $limit ? $limit->message_count : 0,
                    'is_unlocked' => $limit ? $limit->is_paid : false,
                ];
            });

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
}
