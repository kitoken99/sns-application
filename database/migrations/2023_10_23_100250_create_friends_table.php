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
            $table->foreignId('user_id')->constrained()->name('my_user_id');
            $table->foreignId('profile_id')->constrained();
            $table->unsignedBigInteger('friend_user_id');
            $table->unsignedBigInteger('friend_profile_id');
            $table->foreignId('room_id')->constrained();
            $table->string('state')->default("not_friend");
            $table->boolean('is_top')->default(false);
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
