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
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->string('account_type');
            $table->string('name');
            $table->boolean('exist')->nullable()->default(true);
            $table->text('caption')->nullable();
            $table->string('image')->default('user_default.image.png');
            $table->boolean('show_birthday')->default(false);
            $table->boolean('is_main')->default(false);
            $table->unique(['user_id', 'account_type', 'exist']);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
