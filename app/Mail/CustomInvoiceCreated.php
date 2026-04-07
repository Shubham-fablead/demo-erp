<?php

namespace App\Mail;

use App\Models\CustomInvoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomInvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $user;

    public function __construct(CustomInvoice $invoice, $user)
    {
        $this->invoice = $invoice;
        $this->user = $user;
    }

    public function build()
    {
        $invoice = $this->invoice ?? null;
        $user = $this->user ?? null;
        

        return $this->subject('Your Invoice #' . ($invoice->invoice_number ?? ''))
            ->markdown('emails.custom_invoice_created')
            ->with([
                'invoice' => $invoice,
                'user' => $user,
            ]);
    }
}
