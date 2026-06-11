<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Apple'ın stabil kullanıcı tanımlayıcısı (userIdentifier / sub)
            // Email'den bağımsız, her oturumda aynı kalır.
            $table->string('apple_sub')->nullable()->unique()->after('provider');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('apple_sub');
        });
    }
};
