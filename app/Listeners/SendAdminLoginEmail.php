<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendAdminLoginEmail
{
    /**
     * Handle the event.
     */
    public function handle(Login $event): void
    {
        $user = $event->user;

        if (!$user || ($user->role ?? null) !== 'admin') {
            return;
        }

        $to = $user->email ?? null;
        if (!$to) {
            return;
        }

        try {
            $appName = config('app.name', 'Application');
            $time = now('Asia/Kolkata')->format('d M Y h:i A');

            Mail::raw(
                "Hello {$user->name},\n\nYour admin account was logged in on {$time}.\n\n- {$appName}",
                static function ($message) use ($to, $appName): void {
                    $message->to($to)->subject("{$appName}: Admin Login Alert");
                }
            );
        } catch (\Throwable $e) {
            // Never block login flow because of mail issues.
            Log::warning('Admin login email skipped: ' . $e->getMessage());
        }
    }
}

