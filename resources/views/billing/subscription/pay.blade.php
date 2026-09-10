<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800">Pay {{ $invoice->number }}</h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-md mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg shadow-sm p-6 text-sm space-y-2">
                <div class="flex justify-between"><span class="text-gray-500">Invoice</span><span>{{ $invoice->number }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Description</span><span>{{ $invoice->description }}</span></div>
                <div class="flex justify-between font-semibold border-t border-gray-100 pt-2"><span>Amount due</span><span>₹{{ number_format($invoice->balance, 2) }}</span></div>

                <button id="payBtn" class="mt-4 w-full rounded-md bg-teal-600 px-4 py-2 text-sm font-medium text-white hover:bg-teal-700">
                    Pay ₹{{ number_format($invoice->balance, 2) }} with Razorpay
                </button>
                <a href="{{ route('billing.subscription.show') }}" class="block text-center text-sm text-gray-500 hover:underline mt-2">Cancel</a>
            </div>
        </div>
    </div>

    <form id="rzpForm" method="POST" action="{{ route('billing.subscription.callback', $invoice) }}">
        @csrf
        <input type="hidden" name="razorpay_order_id">
        <input type="hidden" name="razorpay_payment_id">
        <input type="hidden" name="razorpay_signature">
    </form>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        document.getElementById('payBtn').addEventListener('click', function () {
            const rzp = new Razorpay({
                key: @json($keyId),
                order_id: @json($order['id']),
                amount: @json($order['amount']),
                currency: 'INR',
                name: @json($hospital->name),
                description: @json($invoice->description),
                handler: function (res) {
                    const f = document.getElementById('rzpForm');
                    f.razorpay_order_id.value = res.razorpay_order_id;
                    f.razorpay_payment_id.value = res.razorpay_payment_id;
                    f.razorpay_signature.value = res.razorpay_signature;
                    f.submit();
                },
                theme: { color: '#0d9488' },
            });
            rzp.open();
        });
    </script>
</x-app-layout>
