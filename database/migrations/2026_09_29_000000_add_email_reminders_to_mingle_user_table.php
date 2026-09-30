<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('mingle_user', function (Blueprint $table) {
            $table->boolean('email_reminders')->default(true);
            $table->timestamp('reminder_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('mingle_user', function (Blueprint $table) {
            $table->dropColumn(['email_reminders', 'reminder_sent_at']);
        });
    }
};
