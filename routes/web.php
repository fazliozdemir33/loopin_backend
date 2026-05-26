<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    
    // Login routes
    Route::get('/login', function () {
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    })->name('admin.login');

    Route::post('/login', function (\Illuminate\Http\Request $request) {
        $username = \App\Models\Setting::where('key', 'admin_username')->value('value') ?? env('ADMIN_USERNAME', 'admin');
        $password = \App\Models\Setting::where('key', 'admin_password')->value('value') ?? env('ADMIN_PASSWORD', 'loopin2026');

        if ($request->username === $username && $request->password === $password) {
            session(['admin_logged_in' => true]);
            return redirect()->route('admin.dashboard');
        }

        return back()->with('error', 'Kullanıcı adı veya şifre hatalı!');
    })->name('admin.login.post');

    Route::get('/logout', function () {
        session()->forget('admin_logged_in');
        return redirect()->route('admin.login');
    })->name('admin.logout');

    // Protected routes
    Route::middleware([\App\Http\Middleware\AdminAuth::class])->group(function () {
        Route::get('/', function () {
            if (\App\Models\Setting::count() == 0) {
                \App\Models\Setting::create(['key' => 'max_distance_km', 'value' => '150']);
            }
            if (!\App\Models\Setting::where('key', 'admin_username')->exists()) {
                \App\Models\Setting::create(['key' => 'admin_username', 'value' => env('ADMIN_USERNAME', 'admin')]);
            }
            if (!\App\Models\Setting::where('key', 'admin_password')->exists()) {
                \App\Models\Setting::create(['key' => 'admin_password', 'value' => env('ADMIN_PASSWORD', 'loopin2026')]);
            }
            
            $settings = \App\Models\Setting::all();
            $userCount = \App\Models\User::count();
            $messageCount = \App\Models\Message::count();
            $convCount = \App\Models\Conversation::count();
            return view('admin.dashboard', compact('settings', 'userCount', 'messageCount', 'convCount'));
        })->name('admin.dashboard');

        Route::post('/', function (\Illuminate\Http\Request $request) {
            foreach ($request->except('_token') as $key => $value) {
                \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
            return back()->with('success', 'Ayarlar başarıyla güncellendi!');
        });

        Route::get('/users', function () {
            $users = \App\Models\User::orderBy('created_at', 'desc')->paginate(20);
            return view('admin.users', compact('users'));
        })->name('admin.users');

        Route::post('/users/{id}/wallet', function (\Illuminate\Http\Request $request, $id) {
            $user = \App\Models\User::findOrFail($id);
            $user->wallet_balance = $request->input('wallet_balance', 0);
            $user->save();
            return back()->with('success', $user->name . ' adlı kullanıcının anahtar sayısı güncellendi!');
        })->name('admin.users.wallet');

        Route::post('/users/{id}/toggle-ban', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $newStatus = !$user->is_banned;
            $user->is_banned = $newStatus;
            $user->save();
            
            if ($user->device_id) {
                \App\Models\User::where('device_id', $user->device_id)
                    ->where('id', '!=', $user->id)
                    ->update(['is_banned' => $newStatus]);
            }
            
            $status = $newStatus ? 'yasaklandı' : 'yasağı kaldırıldı';
            return back()->with('success', $user->name . ' adlı kullanıcı başarıyla ' . $status . '!');
        })->name('admin.users.toggle_ban');

        Route::get('/users/{id}/messages', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $conversations = \App\Models\Conversation::with(['user1', 'user2', 'messages' => function($q) {
                $q->orderBy('created_at', 'desc')->take(10);
            }])
            ->where('user1_id', $id)
            ->orWhere('user2_id', $id)
            ->orderBy('updated_at', 'desc')
            ->get();
            return view('admin.user_messages', compact('user', 'conversations'));
        })->name('admin.user_messages');
    });
});
