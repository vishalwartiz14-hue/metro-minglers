<?php

namespace App\Console\Commands;

use App\Models\Mingle;
use App\Notifications\MingleReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class SendMingleReminders extends Command
{
    protected $signature = 'mingles:send-reminders';

    protected $description = 'Send opted-in email reminders for upcoming Mingles';

    public function handle(): int
    {
        $sent = 0;
        $failed = 0;

        Mingle::query()
            ->events()
            ->whereBetween('starts_at', [now(), now()->addHours(24)])
            ->with('reminderRecipients')
            ->orderBy('starts_at')
            ->chunkById(50, function ($mingles) use (&$sent, &$failed) {
                foreach ($mingles as $mingle) {
                    foreach ($mingle->reminderRecipients as $recipient) {
                        try {
                            $recipient->notify(new MingleReminder($mingle));
                        } catch (Throwable $exception) {
                            report($exception);
                            $failed++;
                            continue;
                        }

                        DB::table('mingle_user')
                            ->where('mingle_id', $mingle->id)
                            ->where('user_id', $recipient->id)
                            ->whereNull('reminder_sent_at')
                            ->update(['reminder_sent_at' => now()]);

                        $sent++;
                    }
                }
            });

        $this->info("Sent {$sent} Mingle reminder(s); {$failed} failed.");

        return self::SUCCESS;
    }
}
