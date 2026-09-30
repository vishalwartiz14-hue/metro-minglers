<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mingle_user', function (Blueprint $table): void {
            $table->string('status', 20)->default('accepted')->after('user_id');
        });

        Schema::create('mingle_invites', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mingle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('inviter_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['mingle_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mingle_invites');
        Schema::table('mingle_user', function (Blueprint $table): void {
            $table->dropColumn('status');
        });
    }
};
