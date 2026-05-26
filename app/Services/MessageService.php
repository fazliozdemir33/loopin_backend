<?php

namespace App\Services;

use App\Models\Message;
use App\Models\InteractionLimit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class MessageService
{
    public function processMessage($user, $receiverId, $text)
    {
        $conversation = DB::table('conversations')
            ->where(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $user->id)->where('user2_id', $receiverId);
            })
            ->orWhere(function($q) use ($user, $receiverId) {
                $q->where('user1_id', $receiverId)->where('user2_id', $user->id);
            })
            ->first();

        if (!$conversation) {
            $convId = DB::table('conversations')->insertGetId([
                'user1_id' => $user->id,
                'user2_id' => $receiverId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $conversation = DB::table('conversations')->where('id', $convId)->first();
            
            InteractionLimit::create([
                'conversation_id' => $convId,
                'message_count' => 0,
                'is_paid' => false,
            ]);
        }

        $limit = InteractionLimit::where('conversation_id', $conversation->id)->first();
        if (!$limit) {
            $limit = InteractionLimit::create([
                'conversation_id' => $conversation->id,
                'message_count' => 0,
                'is_paid' => false,
            ]);
        }

        // Limit Guard / Payment Wall Check
        if (!$limit->is_paid) {
            if ($limit->message_count >= 5) {
                // Sınır aşıldıysa mesaj kaydedilmeden 402 döndür.
                throw new \App\Exceptions\PaymentWallException('5 mesaj hakkınız doldu. Sınırsız mesajlaşmak için kilidi açmalısınız (50 TL).');
            }

            if ($this->isModerationBlocked($text)) {
                abort(403, 'İlk 5 mesajda iletişim bilgisi (telefon, instagram vb.) paylaşmak yasaktır.');
            }
        }

        // Mesaj SQL veritabanına kaydedilir (veya Firebase kullanılıyorsa Firebase'e itilir)
        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $user->id, 
            'text'            => $text,
        ]);

        $limit->increment('message_count');

        return [
            'status' => 'success',
            'message_count' => $limit->message_count
        ];
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
            return false;
        }
    }
}
