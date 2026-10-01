<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (!Schema::hasColumn('chat_conversations', 'type')) {
                $table->string('type')->default('direct')->after('id');
                // values: 'direct' (existing 1-to-1), 'group'
            }

            if (!Schema::hasColumn('chat_conversations', 'name')) {
                $table->string('name')->nullable()->after('type');
            }

            if (!Schema::hasColumn('chat_conversations', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('name');
            }

            if (!Schema::hasColumn('chat_conversations', 'avatar_path')) {
                $table->string('avatar_path')->nullable()->after('created_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('chat_conversations', function (Blueprint $table) {
            if (Schema::hasColumn('chat_conversations', 'avatar_path')) {
                $table->dropColumn('avatar_path');
            }
            if (Schema::hasColumn('chat_conversations', 'created_by')) {
                $table->dropColumn('created_by');
            }
            if (Schema::hasColumn('chat_conversations', 'name')) {
                $table->dropColumn('name');
            }
            if (Schema::hasColumn('chat_conversations', 'type')) {
                $table->dropColumn('type');
            }
        });
    }
};