<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_searches', function (Blueprint $table) {
            $table->id();

            $table->string('search');

            $table->string('status')
                ->default('pending');

            $table->json('products')
                ->nullable();

            $table->text('error')
                ->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_searches');
    }
};