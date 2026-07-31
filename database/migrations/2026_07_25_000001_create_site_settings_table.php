<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_settings', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Default values
        DB::table('site_settings')->insert([
            [
                'id' => 'social_media',
                'value' => json_encode([
                    'instagram' => 'https://instagram.com/woxoyazilim',
                    'facebook' => 'https://facebook.com/woxoyazilim',
                    'twitter' => 'https://twitter.com/woxoyazilim',
                    'linkedin' => 'https://linkedin.com/company/woxoyazilim',
                    'youtube' => '',
                    'tiktok' => '',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 'contact_info',
                'value' => json_encode([
                    'email' => 'info@woxoyazilim.com',
                    'phone_primary' => '0531 254 71 51',
                    'phone_istanbul' => '0535 813 14 63',
                    'phone_dubai' => '+971 50 927 8032',
                    'whatsapp' => '905312547151',
                    'address_hatay' => 'Antakya, Hatay, Türkiye',
                    'address_istanbul' => 'İstanbul, Türkiye',
                    'address_dubai' => 'Business Bay, Dubai, UAE',
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('site_settings');
    }
};
