<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight flex items-center gap-2">
            <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            {{ __('Courier Check') }}
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if(!$isConfigured)
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg text-yellow-800 dark:text-yellow-300 text-sm">
                    The BD Courier API key is not configured yet. Set <code class="px-1 py-0.5 bg-yellow-100 dark:bg-yellow-900/40 rounded">BDCOURIER_API_KEY</code> in your <code class="px-1 py-0.5 bg-yellow-100 dark:bg-yellow-900/40 rounded">.env</code> file to enable this tool.
                </div>
            @endif

            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Look up a customer's phone number to see their delivery success/cancellation history across couriers before confirming a Cash on Delivery order.
            </p>

            <form id="courier-check-form" class="flex flex-wrap gap-3 mb-6" onsubmit="return false;">
                <input
                    type="text"
                    id="courier-check-phone"
                    placeholder="e.g. 017xxxxxxxx"
                    class="flex-1 min-w-[220px] border border-gray-300 dark:border-gray-700 rounded-lg px-4 py-2 bg-white dark:bg-gray-900 text-gray-900 dark:text-gray-100"
                >
                <button
                    type="submit"
                    class="px-4 py-2 bg-violet-600 text-white rounded-lg hover:bg-violet-700 transition"
                >
                    Check
                </button>
            </form>

            <div id="courier-check-result"></div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.getElementById('courier-check-form').addEventListener('submit', function () {
            const phone = document.getElementById('courier-check-phone').value.trim();
            const container = document.getElementById('courier-check-result');
            if (!phone) {
                container.innerHTML = '<div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">Please enter a phone number.</div>';
                return;
            }
            CourierCheck.fetchAndRender(phone, container);
        });

        document.getElementById('courier-check-phone').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                document.getElementById('courier-check-form').requestSubmit();
            }
        });
    </script>
    @endpush
</x-admin-layout>
