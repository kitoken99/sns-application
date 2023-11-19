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
        Schema::create('friends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->unsignedBigInteger('friend_user_id');
            $table->unsignedBigInteger('permitting_id');
            $table->unsignedBigInteger('permitted_id');
            $table->foreignId('profile_id')->constrained()->name('main_profile_id');
            $table->foreignId('room_id')->constrained();
            $table->string('state')->default("not_friend");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('friends');
    }
};
