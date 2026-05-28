<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class UserController extends Controller
{
    public function me(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return response()->json(['data' => $user]);
    }

    public function notifications(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $notifications = \App\Models\Notification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['data' => $notifications]);
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $user->delete();
        return response()->json(['status' => 'success']);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'age' => 'nullable|integer',
            'bio' => 'nullable|string',
            'gender' => 'nullable|string',
            'zodiac_sign' => 'nullable|string',
            'avatar_url' => 'nullable|string',
            'interests' => 'nullable|array',
            'photos' => 'nullable|array',
            'name' => 'nullable|string',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'location' => 'nullable|string',
        ]);
        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $user->fresh()
        ]);
    }

    public function updateNotificationSettings(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'notif_new_messages' => 'required|boolean',
            'notif_new_likes' => 'required|boolean',
            'notif_profile_visits' => 'required|boolean',
            'notif_weekly_summaries' => 'required|boolean',
            'notif_campaigns' => 'required|boolean',
            'notif_in_app_sounds' => 'required|boolean',
            'notif_vibration' => 'required|boolean',
        ]);

        $user->update($validated);

        return response()->json([
            'status' => 'success',
            'data' => $user->fresh()
        ]);
    }

    public function blockUser(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'blocked_id' => 'required|integer|exists:users,id',
        ]);
        
        \Illuminate\Support\Facades\DB::table('blocks')->updateOrInsert([
            'user_id' => $user->id,
            'blocked_id' => $validated['blocked_id']
        ], [
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Kullanıcı başarıyla engellendi.'
        ]);
    }

    public function unblockUser(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'blocked_id' => 'required|integer|exists:users,id',
        ]);
        
        \Illuminate\Support\Facades\DB::table('blocks')
            ->where('user_id', $user->id)
            ->where('blocked_id', $validated['blocked_id'])
            ->delete();
            
        return response()->json([
            'status' => 'success',
            'message' => 'Engelleme kaldırıldı.'
        ]);
    }

    public function reportUser(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'reported_id' => 'required|integer|exists:users,id',
            'reason' => 'required|string',
            'details' => 'nullable|string',
        ]);
        
        \Illuminate\Support\Facades\DB::table('reports')->insert([
            'reporter_id' => $user->id,
            'reported_id' => $validated['reported_id'],
            'reason' => $validated['reason'],
            'details' => $validated['details'] ?? null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
        
        return response()->json([
            'status' => 'success',
            'message' => 'Şikayetiniz alındı, incelenecektir.'
        ]);
    }

    public function getSupportTickets(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $tickets = \App\Models\SupportTicket::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json(['data' => $tickets]);
    }

    public function createSupportTicket(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);
        
        $ticket = \App\Models\SupportTicket::create([
            'user_id' => $user->id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'status' => 'open',
        ]);
        
        return response()->json([
            'status' => 'success',
            'data' => $ticket
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|max:10240',
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = uniqid('photo_') . '.' . $file->getClientOriginalExtension();
            
            if (env('R2_ACCESS_KEY_ID') && env('R2_SECRET_ACCESS_KEY')) {
                try {
                    $path = \Illuminate\Support\Facades\Storage::disk('r2')->putFileAs(
                        'photos',
                        $file,
                        $filename,
                        'public'
                    );
                    $url = rtrim(env('R2_URL'), '/') . '/' . $path;
                    return response()->json(['status' => 'success', 'url' => $url]);
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('R2 Upload failed: ' . $e->getMessage());
                    // Fallback to local storage if R2 fails
                }
            }
            
            $path = $file->storeAs('photos', $filename, 'public');
            $url = asset('storage/' . $path);
            
            return response()->json([
                'status' => 'success',
                'url' => $url
            ]);
        }

        return response()->json(['status' => 'error', 'message' => 'No file uploaded'], 400);
    }
}
