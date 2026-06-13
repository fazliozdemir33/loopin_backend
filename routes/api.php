<?php

function getCityFromCoords(?float $lat, ?float $lng): ?string {
    if ($lat === null || $lng === null) return null;
    $cities = [
        'Adana'=>[37.00,35.32],'Ankara'=>[39.92,32.85],'Antalya'=>[36.88,30.71],
        'Bursa'=>[40.18,29.07],'Diyarbak\u0131r'=>[37.91,40.23],'Edirne'=>[41.68,26.56],
        'Erzurum'=>[39.90,41.27],'Eski\u015fehir'=>[39.78,30.52],'Gaziantep'=>[37.07,37.38],
        '\u0130stanbul'=>[41.01,28.98],'\u0130zmir'=>[38.42,27.13],'Kayseri'=>[38.73,35.48],
        'Kocaeli'=>[40.85,29.88],'Konya'=>[37.87,32.48],'Malatya'=>[38.36,38.31],
        'Mersin'=>[36.81,34.64],'Samsun'=>[41.29,36.33],'Trabzon'=>[41.00,39.72],
        'Sakarya'=>[40.76,30.44],'Bal\u0131kesir'=>[39.65,27.88],'Manisa'=>[38.62,27.43],
        'Kahramanmara\u015f'=>[37.59,36.94],'Van'=>[38.49,43.41],'Hatay'=>[36.40,36.35],
        'Ayd\u0131n'=>[37.84,27.85],'Denizli'=>[37.78,29.09],'Mu\u011fla'=>[37.22,28.36],
        'Tekirda\u011f'=>[40.98,27.51],'\u00c7anakkale'=>[40.16,26.41],'Yalova'=>[40.65,29.27],
        'K\u0131rklareli'=>[41.73,27.22],'Bolu'=>[40.74,31.61],'D\u00fczce'=>[40.84,31.16],
        'Zonguldak'=>[41.46,31.80],'Karab\u00fck'=>[41.21,32.62],'Kastamonu'=>[41.39,33.78],
        'Sinop'=>[42.02,35.15],'Amasya'=>[40.65,35.84],'\u00c7orum'=>[40.55,34.96],
        'Tokat'=>[40.32,36.55],'Sivas'=>[39.75,37.02],'Yozgat'=>[39.82,34.81],
        'Nev\u015fehir'=>[38.69,34.69],'Ni\u011fde'=>[37.97,34.68],'Aksaray'=>[38.37,34.04],
        'Karaman'=>[37.18,33.23],'Isparta'=>[37.76,30.56],'Burdur'=>[37.72,30.29],
        'Afyonkarahisar'=>[38.75,30.56],'K\u00fctahya'=>[39.42,29.98],'U\u015fak'=>[38.68,29.41],
        'Bilecik'=>[40.14,29.98],'K\u0131r\u0131kkale'=>[39.85,33.52],'\u00c7ank\u0131r\u0131'=>[40.60,33.61],
        'Bart\u0131n'=>[41.63,32.34],'Ardahan'=>[41.11,42.70],'Kars'=>[40.60,43.10],
        'A\u011fr\u0131'=>[39.72,43.05],'I\u011fd\u0131r'=>[39.92,44.03],'Erzincan'=>[39.75,39.50],
        'Bayburt'=>[40.26,40.22],'G\u00fcm\u00fc\u015fhane'=>[40.44,39.51],'Giresun'=>[40.91,38.39],
        'Ordu'=>[40.99,37.88],'Rize'=>[41.02,40.52],'Artvin'=>[41.18,41.82],
        'Tunceli'=>[39.11,39.55],'Elaz\u0131\u011f'=>[38.68,39.23],'Bing\u00f6l'=>[38.89,40.50],
        'Mu\u015f'=>[38.75,41.51],'Bitlis'=>[38.39,42.12],'Siirt'=>[37.93,41.95],
        '\u015e\u0131rnak'=>[37.42,42.49],'Hakkari'=>[37.57,43.74],'Mardin'=>[37.32,40.72],
        '\u015eanl\u0131urfa'=>[37.16,38.80],'Batman'=>[37.88,41.14],'Ad\u0131yaman'=>[37.76,38.28],
        'Osmaniye'=>[37.07,36.25],'Kilis'=>[36.72,37.12],'Adana'=>[37.00,35.32],
        'K\u0131rklareli'=>[41.73,27.22],'K\u0131r\u015fehir'=>[39.14,34.17],
    ];
    $min = PHP_FLOAT_MAX; $nearest = null;
    foreach ($cities as $name => $c) {
        $d = sqrt(pow($lat-$c[0],2)+pow($lng-$c[1],2));
        if ($d < $min) { $min = $d; $nearest = $name; }
    }
    return $nearest;
}

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\AuthController;

use App\Http\Controllers\UserController;

Route::post('/auth/social', [AuthController::class, 'social']);
Route::post('/chat/send', [ChatController::class, 'send']);
Route::get('/chat/messages/{receiver_id}', [ChatController::class, 'getMessages']);
Route::delete('/chat/conversations/{receiver_id}', [ChatController::class, 'deleteConversation']);
Route::get('/conversations', [ChatController::class, 'getConversations']);
Route::post('/chat/messages/{id}/listen', [ChatController::class, 'listenMessage']);
Route::get('/users/me', [UserController::class, 'me']);
Route::post('/users/profile', [UserController::class, 'updateProfile']);
Route::get('/users/notifications', [UserController::class, 'notifications']);
Route::post('/users/notification-settings', [UserController::class, 'updateNotificationSettings']);
Route::post('/users/block', [UserController::class, 'blockUser']);
Route::post('/users/unblock', [UserController::class, 'unblockUser']);
Route::post('/users/report', [UserController::class, 'reportUser']);
Route::post('/users/delete', [UserController::class, 'deleteAccount']);
Route::post('/users/fcm-token', [UserController::class, 'updateFcmToken']);
Route::get('/users/explore', function (Request $request) {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    // Get list of blocked user IDs and users who blocked current user
    $blockedUserIds = \Illuminate\Support\Facades\DB::table('blocks')
        ->where('user_id', $user->id)
        ->pluck('blocked_id')
        ->toArray();

    $blockerUserIds = \Illuminate\Support\Facades\DB::table('blocks')
        ->where('blocked_id', $user->id)
        ->pluck('user_id')
        ->toArray();

    $excludeIds = array_merge($blockedUserIds, $blockerUserIds);

    $query = \App\Models\User::where('id', '!=', $user->id)
        ->where('is_banned', false)
        ->where('is_suspended', false)
        ->whereNotIn('id', $excludeIds)
        ->whereNotNull('avatar_url')
        ->where('avatar_url', '!=', '')
        ->where('name', 'not like', 'Loopn_%')
        ->where('name', 'not like', 'Loopin_%')
        ->where('name', 'not like', 'Loopn User%')
        ->where('name', 'not like', 'Loopin User%')
        ->where('name', '!=', 'Kullanıcı');

    if ($user->latitude && $user->longitude) {
        $lat = (float) $user->latitude;
        $lng = (float) $user->longitude;
        
        $maxDistance = \App\Models\Setting::where('key', 'max_distance_km')->value('value') ?? 150;
        
        $haversine = "(6371 * acos(cos(radians($lat)) * cos(radians(latitude)) * cos(radians(longitude) - radians($lng)) + sin(radians($lat)) * sin(radians(latitude))))";

        $users = (clone $query)
            ->selectRaw("*, $haversine as distance")
            ->where(function($q) use ($haversine, $maxDistance) {
                $q->whereRaw("$haversine <= ?", [$maxDistance])
                  ->orWhereNull('latitude')
                  ->orWhereNull('longitude');
            })
            ->orderByRaw("CASE WHEN latitude IS NOT NULL AND longitude IS NOT NULL THEN 0 ELSE 1 END")
            ->orderBy('distance', 'asc')
            ->get();
    } else {
        $users = $query->inRandomOrder()->get();
    }

    $usersWithCity = $users->map(function ($u) {
        $arr = $u->toArray();
        $lat = isset($arr['latitude']) && $arr['latitude'] !== null ? (float)$arr['latitude'] : null;
        $lng = isset($arr['longitude']) && $arr['longitude'] !== null ? (float)$arr['longitude'] : null;
        $arr['city_name'] = getCityFromCoords($lat, $lng);
        return $arr;
    });
    return response()->json(['data' => $usersWithCity]);
});
Route::post('/payment/verify', function (Request $request) {
    $convId = $request->input('conversation_id');
    $receiverId = $request->input('receiver_id');
    $packageId = $request->input('package_id');
    $user = \Illuminate\Support\Facades\Auth::user();

    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    if ($packageId && $user) {
        $count = 1;
        if (str_contains($packageId, '3')) {
            $count = 3;
        } elseif (str_contains($packageId, '5')) {
            $count = 5;
        }
        $user->increment('wallet_balance', $count);
        
        // Record transaction
        \Illuminate\Support\Facades\DB::table('wallet_transactions')->insert([
            'user_id' => $user->id,
            'title' => "Anahtar Alımı ($count Adet)",
            'amount' => "+$count Anahtar",
            'is_debit' => false,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json(['status' => 'success', 'wallet_balance' => $user->wallet_balance]);
    }

    if ($user) {
        $conv = null;
        if ($convId) {
            $conv = \Illuminate\Support\Facades\DB::table('conversations')->where('id', $convId)->first();
        } elseif ($receiverId) {
            $conv = \Illuminate\Support\Facades\DB::table('conversations')
                ->where(function($q) use ($user, $receiverId) {
                    $q->where('user1_id', $user->id)->where('user2_id', $receiverId);
                })
                ->orWhere(function($q) use ($user, $receiverId) {
                    $q->where('user1_id', $receiverId)->where('user2_id', $user->id);
                })
                ->first();
        }

        if ($conv) {
            $otherUserId = $conv->user1_id == $user->id ? $conv->user2_id : $conv->user1_id;
            $otherUser = \App\Models\User::find($otherUserId);
            $otherUserName = $otherUser ? $otherUser->name : 'Kullanıcı';

            // Check if user has keys to spend
            $isDebit = false;
            if ($user->wallet_balance > 0) {
                $user->decrement('wallet_balance', 1);
                $isDebit = true;
            }
            
            \App\Models\InteractionLimit::where('conversation_id', $conv->id)->update(['is_paid' => true]);

            // Record transaction
            \Illuminate\Support\Facades\DB::table('wallet_transactions')->insert([
                'user_id' => $user->id,
                'title' => "Sohbet Kilidi: $otherUserName",
                'amount' => $isDebit ? "-1 Anahtar" : "Ödeme (50 TL)",
                'is_debit' => $isDebit,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['status' => 'success', 'wallet_balance' => $user->wallet_balance]);
        }
    }

    return response()->json(['status' => 'success']);
});

Route::get('/wallet/history', function () {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }
    
    $history = \Illuminate\Support\Facades\DB::table('wallet_transactions')
        ->where('user_id', $user->id)
        ->orderBy('created_at', 'desc')
        ->get();
        
    return response()->json(['data' => $history]);
});

Route::get('/chat/status/{receiver_id}', function ($receiverId) {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) return response()->json(['is_unlocked' => false, 'message_count' => 0, 'is_blocked' => false, 'am_i_blocked' => false]);
    
    $conv = \Illuminate\Support\Facades\DB::table('conversations')
        ->where(function($q) use ($user, $receiverId) {
            $q->where('user1_id', $user->id)->where('user2_id', $receiverId);
        })
        ->orWhere(function($q) use ($user, $receiverId) {
            $q->where('user1_id', $receiverId)->where('user2_id', $user->id);
        })
        ->first();
        
    $isBlockedByMe = \Illuminate\Support\Facades\DB::table('blocks')
        ->where('user_id', $user->id)
        ->where('blocked_id', $receiverId)
        ->exists();

    $amIBlocked = \Illuminate\Support\Facades\DB::table('blocks')
        ->where('user_id', $receiverId)
        ->where('blocked_id', $user->id)
        ->exists();

    if ($conv) {
        $limit = \App\Models\InteractionLimit::where('conversation_id', $conv->id)->first();
        
        $userMessageCount = \Illuminate\Support\Facades\DB::table('messages')
            ->where('conversation_id', $conv->id)
            ->where('sender_id', $user->id)
            ->count();
            
        return response()->json([
            'is_unlocked' => $limit ? (bool)$limit->is_paid : false,
            'message_count' => $userMessageCount,
            'is_blocked' => $isBlockedByMe,
            'am_i_blocked' => $amIBlocked,
        ]);
    }
    
    return response()->json([
        'is_unlocked' => false, 
        'message_count' => 0,
        'is_blocked' => $isBlockedByMe,
        'am_i_blocked' => $amIBlocked,
    ]);
});

Route::get('/support/tickets', [UserController::class, 'getSupportTickets']);
Route::post('/support/tickets', [UserController::class, 'createSupportTicket']);
Route::post('/users/upload', [UserController::class, 'uploadPhoto']);



// ── Paket Yapılandırması (public) ──
Route::get('/packages', function () {
    $value = \Illuminate\Support\Facades\DB::table('settings')
        ->where('key', 'packages')
        ->value('value');

    $packages = $value ? json_decode($value, true) : [];
    return response()->json(['data' => $packages]);
});

// ── Promosyon / Referans Kodu Kullan ──
Route::post('/promo/redeem', function (\Illuminate\Http\Request $request) {
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user) {
        return response()->json(['message' => 'Unauthorized'], 401);
    }

    $request->validate(['code' => 'required|string|max:30']);
    $code = strtoupper(trim($request->code));

    $promo = \Illuminate\Support\Facades\DB::table('promo_codes')
        ->where('code', $code)
        ->where('is_active', true)
        ->first();

    if (!$promo) {
        return response()->json(['message' => 'Geçersiz veya süresi dolmuş bir kod girdiniz.'], 422);
    }

    // Süre kontrolü
    if ($promo->expires_at && now()->isAfter($promo->expires_at)) {
        return response()->json(['message' => 'Bu kodun kullanım süresi dolmuş.'], 422);
    }

    // Kullanım limiti kontrolü (0 = sınırsız)
    if ($promo->max_uses > 0 && $promo->used_count >= $promo->max_uses) {
        return response()->json(['message' => 'Bu kod maksimum kullanım sayısına ulaşmış.'], 422);
    }

    // Daha önce kullanmış mı?
    $alreadyUsed = \Illuminate\Support\Facades\DB::table('promo_code_usages')
        ->where('promo_code_id', $promo->id)
        ->where('user_id', $user->id)
        ->exists();

    if ($alreadyUsed) {
        return response()->json(['message' => 'Bu kodu daha önce kullandınız.'], 422);
    }

    // Kodu uygula
    \Illuminate\Support\Facades\DB::table('promo_code_usages')->insert([
        'promo_code_id' => $promo->id,
        'user_id'       => $user->id,
        'used_at'       => now(),
    ]);

    \Illuminate\Support\Facades\DB::table('promo_codes')
        ->where('id', $promo->id)
        ->increment('used_count');

    $user->increment('wallet_balance', $promo->reward_keys);

    // İşlem geçmişine ekle
    \Illuminate\Support\Facades\DB::table('wallet_transactions')->insert([
        'user_id'    => $user->id,
        'title'      => 'Promosyon Kodu: ' . $code,
        'amount'     => '+' . $promo->reward_keys . ' Anahtar',
        'is_debit'   => false,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return response()->json([
        'message'        => $promo->reward_keys . ' anahtar hesabınıza eklendi! 🎉',
        'reward_keys'    => $promo->reward_keys,
        'wallet_balance' => $user->fresh()->wallet_balance,
    ]);
});

