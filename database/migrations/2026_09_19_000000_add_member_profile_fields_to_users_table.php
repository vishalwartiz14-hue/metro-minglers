<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('email');
            $table->string('gender', 30)->nullable()->after('date_of_birth');
            $table->string('city', 100)->nullable()->after('gender');
            $table->string('zip_code', 20)->nullable()->after('city');
            $table->text('about_me')->nullable()->after('zip_code');
            $table->string('occupation')->nullable()->after('about_me');
            $table->string('education')->nullable()->after('occupation');
            $table->json('interests')->nullable()->after('education');
            $table->string('profile_photo_path')->nullable()->after('interests');
            $table->string('cover_photo_path')->nullable()->after('profile_photo_path');
            $table->timestamp('deactivated_at')->nullable()->after('cover_photo_path');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['date_of_birth','gender','city','zip_code','about_me','occupation','education','interests','profile_photo_path','cover_photo_path','deactivated_at']);
        });
    }
};
