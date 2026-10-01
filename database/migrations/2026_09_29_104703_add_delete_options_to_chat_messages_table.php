<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->boolean('deleted_for_sender')->default(false)->after('deleted_at');
            $table->boolean('deleted_for_receiver')->default(false)->after('deleted_for_sender');
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['deleted_for_sender', 'deleted_for_receiver']);
        });
    }
};