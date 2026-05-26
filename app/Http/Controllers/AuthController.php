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
            'token' => 'required|string',
            'provider' => 'required|string|in:google,apple',
            'email' => 'required|email' // In a real app, you get this from validating the token with Google/Apple API
        ]);

        $email = $request->email;
        $provider = $request->provider;
        $deviceId = $request->device_id;
        $isNewUser = false;

        $user = User::where('email', $email)->where('provider', $provider)->first();

        // Check if user is banned directly or if device is banned
        if ($user && $user->is_banned) {
            return response()->json(['message' => 'BANNED', 'error' => 'Hesabınız yasaklanmıştır.'], 403);
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

        if (!$user) {
            // Create a new user
            $user = User::create([
                'name' => 'Kullanıcı', // Will be updated in Onboarding
                'email' => $email,
                'provider' => $provider,
                'password' => Hash::make(Str::random(24)),
                'device_id' => $deviceId,
            ]);
            
            \App\Models\Notification::create([
                'user_id' => $user->id,
                'title' => "Loopn'e Hoş Geldin!",
                'message' => 'Eşleşmeyi unut, at mesajı etkile!',
                'type' => 'system',
                'is_unread' => true,
            ]);
            
            $isNewUser = true;
        } else {
            // Update device ID if not set or changed
            if ($deviceId && $user->device_id !== $deviceId) {
                $user->device_id = $deviceId;
                $user->save();
            }
        }

        // Generate a random token
        $token = Str::random(60);
        $user->remember_token = $token;
        $user->save();

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'is_new_user' => $isNewUser,
            'user' => $user
        ]);
    }
}
