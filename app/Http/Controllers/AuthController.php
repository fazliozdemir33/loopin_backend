<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function social(Request $request)
    {
        $request->validate([
            'token'    => 'required|string',
            'provider' => 'required|string|in:google,apple',
            // email: Apple ikinci girişte göndermez, bu yüzden zorunlu değil
        ]);

        $provider  = $request->provider;
        $email     = $request->email;
        $appleSub  = $request->apple_sub; // Apple'ın stabil userIdentifier'ı
        $deviceId  = $request->device_id;
        $name      = $request->name;
        $isNewUser = false;

        // ── Kullanıcıyı bul ──────────────────────────────────────────────
        if ($provider === 'apple' && $appleSub) {
            // Apple: önce apple_sub ile ara (her oturumda değişmez)
            $user = User::where('apple_sub', $appleSub)->first();

            // apple_sub eşleşmedi ama email varsa email ile de dene
            // (eski kayıtlar için geriye dönük uyumluluk)
            if (!$user && $email) {
                $user = User::where('email', $email)->where('provider', 'apple')->first();
                // Bulunduysa apple_sub'ı ekle
                if ($user) {
                    $user->apple_sub = $appleSub;
                    $user->save();
                }
            }
        } else {
            // Google: email zorunlu
            if (!$email) {
                return response()->json(['message' => 'Email zorunludur.'], 422);
            }
            $user = User::where('email', $email)->where('provider', $provider)->first();
        }

        // ── Güvenlik kontrolleri ─────────────────────────────────────────
        if ($user && $user->is_banned) {
            return response()->json(['message' => 'BANNED', 'error' => 'Hesabınız yasaklanmıştır.'], 403);
        }

        if ($user && $user->is_suspended) {
            return response()->json([
                'message' => 'SUSPENDED',
                'error'   => $user->suspension_reason ?? 'Hesabınız geçici olarak askıya alınmıştır.',
            ], 403);
        }

        if ($deviceId) {
            $deviceBanned = User::where('device_id', $deviceId)->where('is_banned', true)->exists();
            if ($deviceBanned) {
                if ($user) {
                    $user->is_banned = true;
                    $user->save();
                }
                return response()->json(['message' => 'BANNED', 'error' => 'Bu cihazdan erişim yasaklanmıştır.'], 403);
            }
        }

        // ── Yeni kullanıcı oluştur ───────────────────────────────────────
        if (!$user) {
            $userData = [
                'name'      => $name ?? 'Kullanıcı',
                'email'     => $email ?? ($appleSub ? $appleSub . '@privaterelay.apple.com' : null),
                'provider'  => $provider,
                'password'  => Hash::make(Str::random(24)),
                'device_id' => $deviceId,
            ];

            if ($provider === 'apple' && $appleSub) {
                $userData['apple_sub'] = $appleSub;
            }

            $user = User::create($userData);

            \App\Models\Notification::create([
                'user_id'   => $user->id,
                'title'     => "Loopn'e Hoş Geldin!",
                'message'   => 'Eşleşmeyi unut, at mesajı etkile!',
                'type'      => 'system',
                'is_unread' => true,
            ]);

            $isNewUser = true;
        } else {
            // Mevcut kullanıcı — device_id ve apple_sub güncelle
            $dirty = false;
            if ($deviceId && $user->device_id !== $deviceId) {
                $user->device_id = $deviceId;
                $dirty = true;
            }
            if ($provider === 'apple' && $appleSub && !$user->apple_sub) {
                $user->apple_sub = $appleSub;
                $dirty = true;
            }
            if ($dirty) $user->save();
        }

        // ── Token üret ───────────────────────────────────────────────────
        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'is_new_user'  => $isNewUser,
            'user'         => $user,
        ]);
    }
}
