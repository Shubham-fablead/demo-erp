@component('mail::message')
# Hello {{ $user->name ?? 'Customer' }},

Your invoice **#{{ $invoice->invoice_number }}** has been successfully created!

**Total Amount:** {{ $invoice->grand_total }}
@if($invoice->discount > 0)
**Discount Applied:** {{ $invoice->discount }}
@endif
**Shipping:** {{ $invoice->shipping }}

Thank you for your business!

@component('mail::button', ['url' => route('custom-invoice.view', $invoice->id)])
View Invoice
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent