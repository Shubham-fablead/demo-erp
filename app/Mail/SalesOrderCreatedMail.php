<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SalesOrderCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Order $order;
    public User $customer;
    public bool $isQuotation;
    public ?Setting $setting;

    public function __construct(Order $order, User $customer, bool $isQuotation = false)
    {
        $this->order = $order;
        $this->customer = $customer;
        $this->isQuotation = $isQuotation;
        $this->setting = Setting::where('branch_id', $order->branch_id ?? null)->first() ?? Setting::first();
    }

    public function envelope(): Envelope
    {
        $typeLabel = $this->isQuotation ? 'Quotation' : 'Order';
        $subject = "{$typeLabel} #{$this->order->order_number} Created";

        $fromAddress = config('mail.from.address');
        $fromName = $this->setting?->name ?? config('mail.from.name');

        if (!empty($fromAddress)) {
            return new Envelope(
                from: new Address($fromAddress, $fromName),
                subject: $subject,
            );
        }

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.sales-order-created',
            with: [
                'order' => $this->order,
                'customer' => $this->customer,
                'isQuotation' => $this->isQuotation,
                'setting' => $this->setting,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
