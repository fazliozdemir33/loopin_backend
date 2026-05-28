<?php

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
        ->whereNotIn('id', $excludeIds)
        ->whereNotNull('avatar_url')
        ->where('avatar_url', '!=', '');

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
            ->take(20)
            ->get();
    } else {
        $users = $query->inRandomOrder()->take(20)->get();
    }

    return response()->json(['data' => $users]);
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
        return response()->json([
            'is_unlocked' => $limit ? (bool)$limit->is_paid : false,
            'message_count' => $limit ? $limit->message_count : 0,
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


