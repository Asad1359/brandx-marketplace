<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_messages', 'starred_at')) {
                $table->timestamp('starred_at')->nullable()->after('is_starred');
            }
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'is_blocked')) {
                $table->boolean('is_blocked')->default(false)->after('assigned_to');
            }

            if (!Schema::hasColumn('chat_conversations', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('is_blocked');
            }

            if (!Schema::hasColumn('chat_conversations', 'blocked_reason')) {
                $table->string('blocked_reason')->nullable()->after('blocked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            if (Schema::hasColumn('chat_messages', 'starred_at')) {
                $table->dropColumn('starred_at');
            }
        });

        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'is_blocked')) {
                $table->dropColumn('is_blocked');
            }
            if (Schema::hasColumn('chat_conversations', 'blocked_at')) {
                $table->dropColumn('blocked_at');
            }
            if (Schema::hasColumn('chat_conversations', 'blocked_reason')) {
                $table->dropColumn('blocked_reason');
            }
        });
    }
};