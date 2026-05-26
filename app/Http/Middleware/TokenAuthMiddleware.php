<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TokenAuthMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        if ($token) {
            $user = User::where('remember_token', $token)->first();
            if ($user) {
                if ($user->is_banned) {
                    return response()->json(['message' => 'BANNED', 'error' => 'Hesabınız yasaklanmıştır.'], 403);
                }
                Auth::setUser($user);
                try {
                    if (!$user->last_seen_at || now()->diffInMinutes($user->last_seen_at) > 2) {
                        $user->update(['last_seen_at' => now()]);
                    }
                } catch (\Exception $e) {
                    // Ignore migration errors on production
                }
            }
        }
        return $next($request);
    }
}
