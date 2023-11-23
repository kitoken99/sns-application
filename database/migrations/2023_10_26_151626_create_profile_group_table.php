<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('profile_group', function (Blueprint $table) {
            $table->foreignId('group_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('profile_id')->constrained();
            $table->string('state')->default("invited");
            $table->primary(['user_id', 'group_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_group');
    }
};
