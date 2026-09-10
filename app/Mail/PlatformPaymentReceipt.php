<?php

namespace App\Mail;

use App\Models\PlatformPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlatformPaymentReceipt extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public PlatformPayment $payment)
    {
        $this->payment->loadMissing('invoice.subscription.plan', 'hospital');
    }

    public function envelope(): Envelope
    {
        $activated = $this->payment->invoice && $this->payment->invoice->status === 'paid';

        return new Envelope(
            subject: ($activated
                ? 'Payment received — your '.config('app.name').' account is active'
                : 'Payment receipt '.$this->payment->number).' · '.config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.platform-payment-receipt',
            with: self::viewData($this->payment),
        );
    }

    /** Shared by the email and the on-screen / printable receipt pages. */
    public static function viewData(PlatformPayment $payment): array
    {
        $payment->loadMissing('invoice.subscription.plan', 'hospital');
        $invoice = $payment->invoice;
        $subscription = $invoice?->subscription;

        return [
            'payment' => $payment,
            'invoice' => $invoice,
            'hospital' => $payment->hospital,
            'subscription' => $subscription,
            'fullyPaid' => $invoice && $invoice->status === 'paid',
            'validUntil' => $subscription?->periodEndsOn(),
        ];
    }
}
