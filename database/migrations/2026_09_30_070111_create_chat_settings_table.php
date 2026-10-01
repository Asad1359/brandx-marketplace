<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Default settings
        DB::table('chat_settings')->insert([
            ['key' => 'chat_hours_enabled', 'value' => '0', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'chat_hours_start', 'value' => '09:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'chat_hours_end', 'value' => '18:00', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'chat_hours_days', 'value' => '1,2,3,4,5', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'chat_offline_message', 'value' => 'We are currently offline. Our team will respond during office hours.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_settings');
    }
};