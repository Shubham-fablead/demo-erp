<?php

namespace App\Mail;

use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class StaffCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $staff;
    public $password;
    public $setting;

    public function __construct($staff, $password)
    {
        $this->staff = $staff;
        $this->password = $password;
        $this->setting = Setting::where('branch_id', $staff->branch_id ?? null)->first() ?? Setting::first();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = config('mail.from.address');
        $fromName = $this->setting?->name ?? config('mail.from.name');

        if (!empty($fromAddress)) {
            return new Envelope(
                from: new Address($fromAddress, $fromName),
                subject: 'Your Staff Account Created',
            );
        }

        return new Envelope(
            subject: 'Your Staff Account Created',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.staff-created',
            with: [
                'staff' => $this->staff,
                'password' => $this->password,
                'setting' => $this->setting,
            ],
        );
    }

    /**
     * Get the message content definition.
     */

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
