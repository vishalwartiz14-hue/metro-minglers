<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->json('joining_reasons')->nullable()->after('interests');
            $table->boolean('dating_mode')->default(false)->after('joining_reasons');
            $table->timestamp('onboarding_completed_at')->nullable()->after('dating_mode');
            $table->timestamp('last_active_at')->nullable()->index()->after('onboarding_completed_at');
        });

        // Existing accounts have already completed the original signup flow.
        DB::table('users')->whereNull('onboarding_completed_at')->update(['onboarding_completed_at' => now()]);

        Schema::table('cities', function (Blueprint $table): void {
            $table->decimal('latitude', 10, 7)->nullable()->after('code');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        $cityCoordinates = [
            'ATL' => [33.7490, -84.3880],
            'CLT' => [35.2271, -80.8431],
            'DAL' => [32.7767, -96.7970],
            'HOU' => [29.7604, -95.3698],
            'MIA' => [25.7617, -80.1918],
        ];

        foreach ($cityCoordinates as $code => [$latitude, $longitude]) {
            DB::table('cities')->where('code', $code)->update([
                'latitude' => $latitude,
                'longitude' => $longitude,
            ]);
        }

        Schema::create('user_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->unique(['sender_id', 'recipient_id']);
            $table->index(['recipient_id', 'status']);
        });

        Schema::create('direct_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('recipient_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['sender_id', 'recipient_id', 'created_at']);
            $table->index(['recipient_id', 'read_at']);
        });

        Schema::create('mingle_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mingle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['mingle_id', 'created_at']);
        });

        Schema::create('mingle_updates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mingle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['mingle_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('user_blocks', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('blocked_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['blocker_id', 'blocked_id']);
        });

        Schema::create('user_reports', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reported_id')->constrained('users')->cascadeOnDelete();
            $table->string('reason', 40);
            $table->text('details')->nullable();
            $table->string('status', 20)->default('open');
            $table->timestamps();
            $table->index(['status', 'created_at']);
        });

        // Keep the canonical profile and mingle category list aligned with the 14-category product flow.
        DB::table('interests')->where('name', 'Gaming & Tech')->delete();
    }

    public function down(): void
    {
        if (Schema::hasTable('interests') && ! DB::table('interests')->where('name', 'Gaming & Tech')->exists()) {
            DB::table('interests')->insert([
                'name' => 'Gaming & Tech',
                'is_active' => true,
                'sort_order' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::dropIfExists('user_reports');
        Schema::dropIfExists('user_blocks');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('mingle_updates');
        Schema::dropIfExists('mingle_messages');
        Schema::dropIfExists('direct_messages');
        Schema::dropIfExists('user_connections');

        Schema::table('cities', function (Blueprint $table): void {
            $table->dropColumn(['latitude', 'longitude']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['joining_reasons', 'dating_mode', 'onboarding_completed_at', 'last_active_at']);
        });
    }
};
