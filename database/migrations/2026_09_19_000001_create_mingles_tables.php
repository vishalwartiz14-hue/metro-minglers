<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mingles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('event');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('city', 100);
            $table->string('venue')->nullable();
            $table->string('address_details')->nullable();
            $table->text('meeting_instructions')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->string('category')->nullable();
            $table->json('interests')->nullable();
            $table->json('tags')->nullable();
            $table->string('visibility')->default('open');
            $table->unsignedInteger('maximum_attendees')->nullable();
            $table->boolean('allow_guests')->default(true);
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('members_can_create_events')->default(false);
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
        Schema::create('mingle_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mingle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['mingle_id', 'user_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('mingle_user'); Schema::dropIfExists('mingles'); }
};
