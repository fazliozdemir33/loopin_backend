<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->string('price_label');
            $table->string('store_product_id')->unique();
            $table->integer('keys_count')->default(1);
            $table->boolean('is_popular')->default(false);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed default packages matching App Store Connect IDs
        DB::table('packages')->insert([
            [
                'title'            => '1 Adet Anahtar',
                'subtitle'         => '1 kilit açmak için ideal',
                'price_label'      => '50 TL',
                'store_product_id' => 'com.dmrsoft.fisilti.chatkeys.1',
                'keys_count'       => 1,
                'is_popular'       => false,
                'sort_order'       => 1,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => '3 Adet Anahtar (Popüler)',
                'subtitle'         => 'Daha fazla sohbet özgürlüğü',
                'price_label'      => '120 TL',
                'store_product_id' => 'com.dmrsoft.fisilti.chatkeys.3',
                'keys_count'       => 3,
                'is_popular'       => true,
                'sort_order'       => 2,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
            [
                'title'            => '5 Adet Anahtar (Avantajlı)',
                'subtitle'         => 'En uygun fiyat avantajı',
                'price_label'      => '180 TL',
                'store_product_id' => 'com.dmrsoft.fisilti.chatkeys.5',
                'keys_count'       => 5,
                'is_popular'       => false,
                'sort_order'       => 3,
                'is_active'        => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
