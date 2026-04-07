<?php

namespace App\Services;

use App\Mail\StaffCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\MailConfigService;

class StaffService
{
    public static function sendStaffCreatedEmail($staff, string $plainPassword): bool
    {
        if (empty($staff?->email)) {
            Log::warning('Staff creation email skipped: missing recipient email.');
            return false;
        }

        try {
            // Load dynamic SMTP
            MailConfigService::setSMTP($staff->branch_id);
        } catch (\Throwable $e) {
            // Continue with default mailer if branch SMTP setup fails.
            Log::warning('Branch SMTP load failed for staff email: ' . $e->getMessage());
        }

        try {
            // Send Mail
            Mail::to($staff->email)
                ->send(new StaffCreatedMail($staff, $plainPassword));
            Log::info('Staff creation email sent to ' . $staff->email);
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send staff creation email to ' . $staff->email . ': ' . $e->getMessage());
            return false;
        }
    }
}
