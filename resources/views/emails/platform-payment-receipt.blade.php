@php
    $app = config('app.name');
    $c = config('hms.contact');
    $money = fn ($n) => '₹'.number_format((float) $n, 2);
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment receipt {{ $payment->number }} — {{ $app }}</title>
</head>
<body style="margin:0;background:#f1f5f9;font-family:-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif;color:#334155;">
<div style="max-width:600px;margin:0 auto;padding:24px 16px;">

    <table role="presentation" width="100%" style="background:#fff;border-radius:12px;overflow:hidden;box-shadow:0 1px 3px rgba(15,23,42,.08);">
        <tr>
            <td style="background:#0d9488;padding:22px 28px;color:#fff;">
                <div style="font-size:18px;font-weight:700;letter-spacing:-.3px;">{{ $app }}</div>
                <div style="font-size:13px;opacity:.85;">Payment receipt</div>
            </td>
        </tr>

        <tr><td style="padding:28px;">

            @if ($fullyPaid)
                <p style="margin:0 0 18px;padding:12px 14px;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:8px;font-size:14px;color:#065f46;">
                    <strong>Payment received in full.</strong>
                    @if ($subscription && $validUntil)
                        Your {{ $app }} account is active until <strong>{{ \Illuminate\Support\Carbon::parse($validUntil)->format('d M Y') }}</strong>.
                    @else
                        Your {{ $app }} account is active.
                    @endif
                </p>
            @else
                <p style="margin:0 0 18px;padding:12px 14px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;font-size:14px;color:#92400e;">
                    Part payment received. Balance of {{ $money($invoice?->balance ?? 0) }} is still due.
                </p>
            @endif

            <table role="presentation" width="100%" style="font-size:14px;">
                <tr>
                    <td style="padding:3px 0;color:#64748b;">Receipt no.</td>
                    <td style="padding:3px 0;text-align:right;font-weight:600;">{{ $payment->number }}</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;color:#64748b;">Date</td>
                    <td style="padding:3px 0;text-align:right;">{{ $payment->paid_at?->format('d M Y, g:i A') }}</td>
                </tr>
                @if ($invoice)
                <tr>
                    <td style="padding:3px 0;color:#64748b;">Against invoice</td>
                    <td style="padding:3px 0;text-align:right;">{{ $invoice->number }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding:3px 0;color:#64748b;">Hospital</td>
                    <td style="padding:3px 0;text-align:right;">{{ $hospital->name }}</td>
                </tr>
                <tr>
                    <td style="padding:3px 0;color:#64748b;">Payment method</td>
                    <td style="padding:3px 0;text-align:right;text-transform:capitalize;">
                        {{ str_replace('_', ' ', $payment->method) }}@if ($payment->reference) · {{ $payment->reference }}@endif
                    </td>
                </tr>
            </table>

            @if ($invoice)
            <table role="presentation" width="100%" style="margin-top:18px;border-top:1px solid #e2e8f0;font-size:14px;">
                <tr>
                    <td style="padding:12px 0 4px;">{{ $invoice->description }}</td>
                    <td style="padding:12px 0 4px;text-align:right;">{{ $money($invoice->subtotal) }}</td>
                </tr>
                @if ($invoice->period_start)
                <tr>
                    <td colspan="2" style="padding:0 0 8px;color:#94a3b8;font-size:12px;">
                        Period {{ \Illuminate\Support\Carbon::parse($invoice->period_start)->format('d M Y') }} – {{ \Illuminate\Support\Carbon::parse($invoice->period_end)->format('d M Y') }}
                    </td>
                </tr>
                @endif
                <tr>
                    <td style="padding:4px 0;color:#64748b;">GST (18%)</td>
                    <td style="padding:4px 0;text-align:right;">{{ $money($invoice->tax) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 0;border-top:1px solid #e2e8f0;font-weight:700;">Invoice total</td>
                    <td style="padding:8px 0;border-top:1px solid #e2e8f0;text-align:right;font-weight:700;">{{ $money($invoice->total) }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#0d9488;">This payment</td>
                    <td style="padding:4px 0;text-align:right;color:#0d9488;font-weight:600;">{{ $money($payment->amount) }}</td>
                </tr>
                <tr>
                    <td style="padding:4px 0;color:#64748b;">Total paid</td>
                    <td style="padding:4px 0;text-align:right;">{{ $money($invoice->amount_paid) }}</td>
                </tr>
                @if ($invoice->balance > 0.01)
                <tr>
                    <td style="padding:4px 0;color:#b91c1c;">Balance due</td>
                    <td style="padding:4px 0;text-align:right;color:#b91c1c;font-weight:600;">{{ $money($invoice->balance) }}</td>
                </tr>
                @endif
            </table>
            @else
            <p style="margin-top:18px;font-size:15px;font-weight:700;">Amount paid: {{ $money($payment->amount) }}</p>
            @endif

            <p style="margin:24px 0 0;font-size:13px;color:#94a3b8;">
                Questions? WhatsApp or call {{ $c['phone'] }} · {{ $c['email'] }}
            </p>
        </td></tr>
    </table>

    <p style="text-align:center;font-size:12px;color:#94a3b8;margin-top:16px;">
        &copy; {{ date('Y') }} {{ $app }}. This is a computer-generated receipt.
    </p>
</div>
</body>
</html>
