<?php

namespace App\Support;

use App\Models\PlatformInvoice;
use Illuminate\Support\Facades\Http;

/**
 * Thin Razorpay Orders + signature-verification wrapper. No SDK dependency.
 * Disabled (manual payments only) when keys are not configured.
 */
class RazorpayGateway
{
    public function __construct(
        protected ?string $keyId = null,
        protected ?string $keySecret = null,
        protected ?string $webhookSecret = null,
    ) {
        $this->keyId ??= config('services.razorpay.key');
        $this->keySecret ??= config('services.razorpay.secret');
        $this->webhookSecret ??= config('services.razorpay.webhook_secret');
    }

    public function enabled(): bool
    {
        return filled($this->keyId) && filled($this->keySecret);
    }

    public function keyId(): ?string
    {
        return $this->keyId;
    }

    /** Create a Razorpay order for an invoice. Returns the order array. */
    public function createOrder(PlatformInvoice $invoice): array
    {
        $response = Http::withBasicAuth($this->keyId, $this->keySecret)
            ->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => (int) round(((float) $invoice->balance) * 100), // paise
                'currency' => 'INR',
                'receipt' => $invoice->number,
                'notes' => [
                    'platform_invoice_id' => (string) $invoice->id,
                    'hospital_id' => (string) $invoice->hospital_id,
                ],
            ])->throw();

        return $response->json();
    }

    /** Verify checkout callback signature: HMAC-SHA256(order_id|payment_id, secret). */
    public function verifyPayment(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->keySecret);

        return hash_equals($expected, $signature);
    }

    /** Verify a webhook payload signature. */
    public function verifyWebhook(string $rawBody, string $signature): bool
    {
        if (blank($this->webhookSecret)) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', $rawBody, $this->webhookSecret), $signature);
    }
}
