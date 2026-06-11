<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|----------------------------------------------------------------------
| Web Routes — Loopin Admin Panel
|----------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/csae-policy', function () {
    return view('csae-policy');
})->name('csae.policy');

Route::prefix('admin')->group(function () {

    /* ─── Login ─── */
    Route::get('/login', function () {
        if (session('admin_logged_in')) return redirect()->route('admin.dashboard');
        return view('admin.login');
    })->name('admin.login');

    Route::post('/login', function (Request $request) {
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

    /* ─── Protected Routes ─── */
    Route::middleware([\App\Http\Middleware\AdminAuth::class])->group(function () {

        /* ── Dashboard ── */
        Route::get('/', function () {
            // Ensure default settings exist
            foreach ([
                'max_distance_km' => '150',
                'admin_username'  => env('ADMIN_USERNAME', 'admin'),
                'admin_password'  => env('ADMIN_PASSWORD', 'loopin2026'),
                'firebase_service_account_json' => ''
            ] as $key => $default) {
                if (!\App\Models\Setting::where('key', $key)->exists()) {
                    \App\Models\Setting::create(['key' => $key, 'value' => $default]);
                }
            }

            $userCount    = \App\Models\User::count();
            $messageCount = \App\Models\Message::count();
            $convCount    = \App\Models\Conversation::count();
            $bannedCount     = \App\Models\User::where('is_banned', true)->count();
            $suspendedCount  = \App\Models\User::where('is_suspended', true)->where('is_banned', false)->count();
            $onlineCount  = \App\Models\User::where('last_seen_at', '>=', now()->subMinutes(5))->count();
            $totalKeys    = \App\Models\User::sum('wallet_balance');
            $unlockedConvs = \App\Models\Conversation::where('is_unlocked', true)->count();
            $openReports  = \Illuminate\Support\Facades\DB::table('reports')->count();
            $newUsersToday = \App\Models\User::whereDate('created_at', today())->count();

            $recentUsers  = \App\Models\User::orderBy('created_at', 'desc')->take(8)->get();
            $settings     = \App\Models\Setting::whereNotIn('key', ['admin_username', 'admin_password'])->get();

            // Gender stats — null değerleri "Belirtilmemiş" olarak grupla
            $rawGender = \App\Models\User::selectRaw("COALESCE(NULLIF(gender,''), 'Belirtilmemiş') as gender_label, count(*) as count")
                ->groupBy('gender_label')
                ->get();

            // Sort in PHP (SQLite doesn't support FIELD function)
            $sortOrder = ['Kadın' => 1, 'Erkek' => 2, 'Belirtilmemiş' => 3];
            $rawGender = $rawGender->sortBy(function($item) use ($sortOrder) {
                return $sortOrder[$item->gender_label] ?? 99;
            })->values();

            $total = $rawGender->sum('count');
            $genderStats = $rawGender->map(function($row) use ($total) {
                return (object)[
                    'gender' => $row->gender_label,
                    'count'  => $row->count,
                    'pct'    => $total > 0 ? round(($row->count / $total) * 100) : 0,
                ];
            });

            // Provider stats
            $providerStats = \App\Models\User::selectRaw('provider, count(*) as count')
                ->groupBy('provider')
                ->get();

            return view('admin.dashboard', compact(
                'userCount', 'messageCount', 'convCount', 'bannedCount', 'suspendedCount',
                'onlineCount', 'totalKeys', 'unlockedConvs', 'openReports',
                'newUsersToday', 'recentUsers', 'settings', 'genderStats', 'providerStats'
            ));
        })->name('admin.dashboard');

        Route::post('/', function (Request $request) {
            foreach ($request->except('_token') as $key => $value) {
                if ($value === '' || $value === null) continue; // skip empty fields
                \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
            return back()->with('success', 'Ayarlar başarıyla güncellendi!');
        });

        /* ── Users ── */
        Route::get('/users', function (Request $request) {
            $query = \App\Models\User::orderBy('created_at', 'desc');

            if ($search = $request->get('search')) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhereRaw('LOWER(email) LIKE ?', ['%' . strtolower($search) . '%'])
                      ->orWhere('id', $search);
                });
            }

            if ($status = $request->get('status')) {
                if ($status === 'banned') {
                    $query->where('is_banned', true);
                } elseif ($status === 'suspended') {
                    $query->where('is_suspended', true)->where('is_banned', false);
                } elseif ($status === 'active') {
                    $query->where('is_banned', false)->where('is_suspended', false)->whereNotNull('avatar_url');
                } elseif ($status === 'incomplete') {
                    $query->where('is_banned', false)->where('is_suspended', false)->whereNull('avatar_url');
                }
            }

            if ($provider = $request->get('provider')) {
                $query->where('provider', $provider);
            }

            $users = $query->paginate(25)->withQueryString();
            return view('admin.users', compact('users'));
        })->name('admin.users');

        Route::get('/users/banned', function () {
            $users = \App\Models\User::where('is_banned', true)
                ->orderBy('updated_at', 'desc')
                ->paginate(25);
            return view('admin.banned_users', compact('users'));
        })->name('admin.users.banned');

        Route::post('/users/{id}/wallet', function (Request $request, $id) {
            $user = \App\Models\User::findOrFail($id);
            $user->wallet_balance = $request->input('wallet_balance', 0);
            $user->save();
            return back()->with('success', $user->getRawOriginal('name') . ' adlı kullanıcının anahtar sayısı güncellendi!');
        })->name('admin.users.wallet');

        Route::post('/users/{id}/update', function (Request $request, $id) {
            $user = \App\Models\User::findOrFail($id);
            
            $request->validate([
                'name' => 'nullable|string|max:255',
                'email' => 'nullable|email|max:255',
                'gender' => 'nullable|string',
                'age' => 'nullable|integer',
                'avatar_url' => 'nullable|url',
                'bio' => 'nullable|string'
            ]);

            $user->name = $request->input('name') ?: $user->name;
            $user->email = $request->input('email') ?: $user->email;
            $user->gender = $request->input('gender') ?: $user->gender;
            $user->age = $request->input('age') ?: $user->age;
            $user->avatar_url = $request->input('avatar_url') ?: $user->avatar_url;
            $user->bio = $request->input('bio') ?: $user->bio;
            $user->save();

            return back()->with('success', 'Kullanıcı bilgileri başarıyla güncellendi!');
        })->name('admin.users.update');

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
            return back()->with('success', $user->getRawOriginal('name') . ' adlı kullanıcı başarıyla ' . $status . '!');
        })->name('admin.users.toggle_ban');

        Route::post('/users/{id}/toggle-suspend', function (Request $request, $id) {
            $user = \App\Models\User::findOrFail($id);
            $newStatus = !$user->is_suspended;
            $user->is_suspended = $newStatus;
            if ($newStatus && $request->filled('suspension_reason')) {
                $user->suspension_reason = $request->input('suspension_reason');
            } elseif (!$newStatus) {
                $user->suspension_reason = null;
            }
            $user->save();

            $status = $newStatus ? 'askıya alındı' : 'askısı kaldırıldı';
            return back()->with('success', $user->getRawOriginal('name') . ' adlı kullanıcı başarıyla ' . $status . '!');
        })->name('admin.users.toggle_suspend');

        Route::delete('/users/{id}', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $name = $user->getRawOriginal('name') ?? 'Kullanıcı';

            // İlişkili mesajları ve sohbetleri temizle
            $convIds = \App\Models\Conversation::where('user1_id', $id)
                ->orWhere('user2_id', $id)
                ->pluck('id');
            \App\Models\Message::whereIn('conversation_id', $convIds)->delete();
            \App\Models\Conversation::whereIn('id', $convIds)->delete();

            // Bloklar, raporlar, bildirimler
            \Illuminate\Support\Facades\DB::table('blocks')
                ->where('user_id', $id)->orWhere('blocked_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('reports')
                ->where('reporter_id', $id)->orWhere('reported_id', $id)->delete();
            \App\Models\Notification::where('user_id', $id)->delete();

            // Kullanıcıyı sil
            $user->delete();

            return back()->with('success', '"' . $name . '" adlı kullanıcı ve tüm verileri kalıcı olarak silindi!');
        })->name('admin.users.delete');

        Route::get('/users/{id}/messages', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $conversations = \App\Models\Conversation::with(['user1', 'user2', 'messages' => function ($q) {
                $q->orderBy('created_at', 'desc')->take(20);
            }])
            ->where('user1_id', $id)
            ->orWhere('user2_id', $id)
            ->orderBy('updated_at', 'desc')
            ->get();
            return view('admin.user_messages', compact('user', 'conversations'));
        })->name('admin.user_messages');

        /* ── Fake Accounts ── */
        Route::get('/fake-accounts', function () {
            return view('admin.fake_accounts');
        })->name('admin.fake_accounts');

        Route::post('/fake-accounts', function (Request $request) {
            $request->validate([
                'name' => 'required|string',
                'gender' => 'required|string',
                'age' => 'required|integer',
                'height' => 'nullable|integer',
                'latitude' => 'required|numeric',
                'longitude' => 'required|numeric',
                'relationship_goal' => 'nullable|string',
                'avatar_url' => 'nullable|url',
                'extra_photos' => 'nullable|string'
            ]);

            $photos = [];
            if ($request->extra_photos) {
                $lines = explode("\n", str_replace("\r", "", $request->extra_photos));
                foreach ($lines as $line) {
                    $url = trim($line);
                    if (filter_var($url, FILTER_VALIDATE_URL)) {
                        $photos[] = $url;
                    }
                }
            }

            $user = new \App\Models\User();
            $user->name = $request->name;
            $user->email = 'fake_' . uniqid() . '@loopn.app';
            $user->password = bcrypt(uniqid()); // random password
            $user->age = $request->age;
            $user->height = $request->height;
            $user->gender = $request->gender;
            $user->relationship_goal = $request->relationship_goal;
            $user->zodiac_sign = $request->zodiac_sign;
            $user->bio = $request->bio;
            $user->avatar_url = $request->avatar_url;
            $user->photos = $photos;
            $user->latitude = $request->latitude;
            $user->longitude = $request->longitude;
            $user->provider = 'fake'; // tag them as fake
            $user->save();

            return back()->with('success', 'Sahte hesap (' . $user->name . ') başarıyla oluşturuldu!');
        })->name('admin.fake_accounts.store');

        Route::get('/fake-conversations', function (Request $request) {
            $fakeUserIds = \App\Models\User::where('provider', 'fake')->pluck('id');

            $conversations = \App\Models\Conversation::with(['user1', 'user2', 'messages' => function($q) {
                    $q->orderBy('created_at', 'desc')->take(1);
                }])
                ->where(function($query) use ($fakeUserIds) {
                    $query->whereIn('user1_id', $fakeUserIds)
                          ->orWhereIn('user2_id', $fakeUserIds);
                })
                ->orderBy('updated_at', 'desc')
                ->paginate(25);

            return view('admin.fake_conversations', compact('conversations', 'fakeUserIds'));
        })->name('admin.fake_conversations');

        Route::get('/fake-conversations/{id}', function ($id) {
            $conversation = \App\Models\Conversation::with(['user1', 'user2', 'messages' => function($q) {
                $q->orderBy('created_at', 'asc');
            }])->findOrFail($id);

            $fakeUserIds = \App\Models\User::where('provider', 'fake')->pluck('id')->toArray();
            
            if (!in_array($conversation->user1_id, $fakeUserIds) && !in_array($conversation->user2_id, $fakeUserIds)) {
                return redirect()->route('admin.fake_conversations')->with('error', 'Bu sohbette bot hesap bulunmuyor.');
            }

            return view('admin.fake_conversations_show', compact('conversation', 'fakeUserIds'));
        })->name('admin.fake_conversations.show');

        Route::post('/fake-conversations/{id}/reply', function (Request $request, $id) {
            $request->validate(['text' => 'required|string']);

            $conversation = \App\Models\Conversation::findOrFail($id);
            $fakeUserIds = \App\Models\User::where('provider', 'fake')->pluck('id')->toArray();

            $botId = null;
            if (in_array($conversation->user1_id, $fakeUserIds)) $botId = $conversation->user1_id;
            elseif (in_array($conversation->user2_id, $fakeUserIds)) $botId = $conversation->user2_id;

            if (!$botId) return back()->with('error', 'Bot hesap bulunamadı.');

            \App\Models\Message::create([
                'conversation_id' => $conversation->id,
                'sender_id' => $botId,
                'text' => $request->text,
                'type' => 'text'
            ]);

            $conversation->touch();

            return back()->with('success', 'Yanıt gönderildi.');
        })->name('admin.fake_conversations.reply');

        /* ── Conversations ── */
        Route::get('/conversations', function (Request $request) {
            $query = \App\Models\Conversation::with(['user1', 'user2'])
                ->orderBy('updated_at', 'desc');

            if ($status = $request->get('status')) {
                if ($status === 'unlocked') $query->where('is_unlocked', true);
                if ($status === 'locked')   $query->where('is_unlocked', false);
            }

            $conversations = $query->paginate(25)->withQueryString();
            return view('admin.conversations', compact('conversations'));
        })->name('admin.conversations');

        /* ── Reports ── */
        Route::get('/reports', function () {
            $reports = \Illuminate\Support\Facades\DB::table('reports')
                ->orderBy('created_at', 'desc')
                ->paginate(25);

            // Attach user models manually
            $reports->getCollection()->transform(function ($report) {
                $report->reporter = \App\Models\User::find($report->reporter_id);
                $report->reported = \App\Models\User::find($report->reported_id);
                $report->created_at = $report->created_at ? \Carbon\Carbon::parse($report->created_at) : null;
                return $report;
            });

            return view('admin.reports', compact('reports'));
        })->name('admin.reports');

        /* ── Support ── */
        Route::get('/support', function () {
            $tickets = \App\Models\SupportTicket::with('user')
                ->orderBy('created_at', 'desc')
                ->paginate(25);
            return view('admin.support', compact('tickets'));
        })->name('admin.support');

        /* ── Settings ── */

        Route::get('/packages', function () {
            $value = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'packages')->value('value');
            $packages = $value ? json_decode($value, true) : [];
            return view('admin.packages', compact('packages'));
        })->name('admin.packages');

        Route::post('/packages', function (\Illuminate\Http\Request $request) {
            $packages = [];
            $ids = $request->input('id', []);
            $titles = $request->input('title', []);
            $subtitles = $request->input('subtitle', []);
            $priceLabels = $request->input('price_label', []);
            $keysCounts = $request->input('keys_count', []);
            $storeProductIds = $request->input('store_product_id', []);
            $populars = $request->input('is_popular', []);

            foreach ($ids as $i => $id) {
                $packages[] = [
                    'id' => (int)$id,
                    'title' => $titles[$i] ?? '',
                    'subtitle' => $subtitles[$i] ?? '',
                    'price_label' => $priceLabels[$i] ?? '',
                    'keys_count' => (int)($keysCounts[$i] ?? 1),
                    'store_product_id' => $storeProductIds[$i] ?? '',
                    'is_popular' => isset($populars[$i]),
                ];
            }

            \Illuminate\Support\Facades\DB::table('settings')->updateOrInsert(
                ['key' => 'packages'],
                ['value' => json_encode($packages, JSON_UNESCAPED_UNICODE), 'updated_at' => now()]
            );

            return back()->with('success', 'Paket yapılandırması güncellendi!');
        })->name('admin.packages.update');

        Route::get('/settings', function () {
            $settings = \App\Models\Setting::whereNotIn('key', ['admin_username', 'admin_password'])->get();
            return view('admin.settings', compact('settings'));
        })->name('admin.settings');

        Route::post('/settings', function (Request $request) {
            foreach ($request->except('_token') as $key => $value) {
                if ($value === '' || $value === null) continue;
                \App\Models\Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            }
            return back()->with('success', 'Ayarlar başarıyla güncellendi!');
        });

        /* ── Promo Codes ── */
        Route::get('/promo-codes', function () {
            $codes = \Illuminate\Support\Facades\DB::table('promo_codes')
                ->orderBy('created_at', 'desc')
                ->paginate(25);
            return view('admin.promo_codes', compact('codes'));
        })->name('admin.promo');

        Route::post('/promo-codes', function (Request $request) {
            $request->validate([
                'code'        => 'required|string|max:30|unique:promo_codes,code',
                'reward_keys' => 'required|integer|min:1',
                'max_uses'    => 'nullable|integer|min:0',
                'expires_at'  => 'nullable|date',
            ]);

            \Illuminate\Support\Facades\DB::table('promo_codes')->insert([
                'code'        => strtoupper(trim($request->code)),
                'description' => $request->description,
                'reward_keys' => $request->reward_keys,
                'max_uses'    => $request->max_uses ?? 1,
                'used_count'  => 0,
                'is_active'   => true,
                'expires_at'  => $request->expires_at ?: null,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            return back()->with('success', '"' . strtoupper($request->code) . '" kodu başarıyla oluşturuldu!');
        })->name('admin.promo.store');

        Route::post('/promo-codes/{id}/toggle', function ($id) {
            $code = \Illuminate\Support\Facades\DB::table('promo_codes')->where('id', $id)->first();
            if (!$code) return back()->with('error', 'Kod bulunamadı.');
            \Illuminate\Support\Facades\DB::table('promo_codes')
                ->where('id', $id)
                ->update(['is_active' => !$code->is_active, 'updated_at' => now()]);
            $status = $code->is_active ? 'pasife alındı' : 'aktive edildi';
            return back()->with('success', '"' . $code->code . '" kodu ' . $status . '.');
        })->name('admin.promo.toggle');

        Route::delete('/promo-codes/{id}', function ($id) {
            $code = \Illuminate\Support\Facades\DB::table('promo_codes')->where('id', $id)->first();
            \Illuminate\Support\Facades\DB::table('promo_code_usages')->where('promo_code_id', $id)->delete();
            \Illuminate\Support\Facades\DB::table('promo_codes')->where('id', $id)->delete();
            return back()->with('success', '"' . ($code->code ?? '') . '" kodu silindi.');
        })->name('admin.promo.delete');

        /* ── Notifications ── */
        Route::get('/notifications', function () {
            return view('admin.notifications');
        })->name('admin.notifications');

        Route::post('/notifications', function (Request $request) {
            $request->validate([
                'title' => 'required|string',
                'body' => 'required|string',
                'target' => 'required|in:all,women,men,user',
            ]);

            $query = \App\Models\User::whereNotNull('fcm_token')->where('fcm_token', '!=', '');

            if ($request->target === 'women') {
                $query->where('gender', 'Kadın');
            } elseif ($request->target === 'men') {
                $query->where('gender', 'Erkek');
            } elseif ($request->target === 'user') {
                $request->validate(['user_id' => 'required|integer']);
                $query->where('id', $request->user_id);
            }

            $tokens = $query->pluck('fcm_token')->toArray();

            if (empty($tokens)) {
                return back()->with('error', 'Seçilen kritere uygun FCM token bulunamadı.');
            }

            $serviceAccount = json_decode(\App\Models\Setting::where('key', 'firebase_service_account_json')->value('value'), true);
            
            if (!$serviceAccount || !isset($serviceAccount['client_email']) || !isset($serviceAccount['private_key'])) {
                return back()->with('error', 'Firebase Service Account JSON eksik. Lütfen Ayarlar sayfasından ekleyin.');
            }

            try {
                $credentials = new \Google\Auth\Credentials\ServiceAccountCredentials(
                    'https://www.googleapis.com/auth/firebase.messaging',
                    $serviceAccount
                );
                $authToken = $credentials->fetchAuthToken(\Google\Auth\HttpHandler\HttpHandlerFactory::build());
                $accessToken = $authToken['access_token'] ?? null;
            } catch (\Exception $e) {
                return back()->with('error', 'Google API Hata: ' . $e->getMessage());
            }

            if (!$accessToken) {
                return back()->with('error', 'Firebase Access Token alınamadı. JSON bilgilerini kontrol edin.');
            }

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            $projectId = $serviceAccount['project_id'];
            $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

            $client = new \GuzzleHttp\Client();

            foreach ($tokens as $token) {
                try {
                    $client->post($url, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . trim($accessToken),
                            'Content-Type'  => 'application/json',
                        ],
                        'json' => [
                            'message' => [
                                'token' => $token,
                                'notification' => [
                                    'title' => $request->title,
                                    'body' => $request->body,
                                ]
                            ]
                        ]
                    ]);
                    $successCount++;
                } catch (\GuzzleHttp\Exception\ClientException $e) {
                    $failCount++;
                    if ($e->hasResponse()) {
                        $errorBody = json_decode($e->getResponse()->getBody()->getContents(), true);
                        $errors[] = $errorBody['error']['message'] ?? $e->getMessage();
                    } else {
                        $errors[] = $e->getMessage();
                    }
                } catch (\Exception $e) {
                    $failCount++;
                    $errors[] = $e->getMessage();
                }
            }

            if ($failCount > 0) {
                $errorDetails = implode(' | ', array_unique($errors));
                if ($successCount > 0) {
                    return back()->with('error', "Uyarı: $successCount cihaza iletildi, $failCount cihaza iletilemedi. Hata: " . substr($errorDetails, 0, 250));
                }
                return back()->with('error', "Başarısız: Tüm gönderimler ($failCount) hatalı. Hata: " . substr($errorDetails, 0, 250));
            }

            return back()->with('success', "Bildirimler başarıyla gönderildi. Başarılı: $successCount");
        })->name('admin.notifications.send');
    });
});
