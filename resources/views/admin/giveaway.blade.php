<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Giveaway Panel
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            <div class="mb-6 flex flex-wrap gap-3 items-center justify-between">
                <div class="text-sm text-gray-600 dark:text-gray-300">
                    Total entries: <span class="font-bold text-gray-900 dark:text-white">{{ $entries->count() }}</span>
                </div>
                <div class="flex gap-3">
                    <button id="randomizeButton" type="button" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                        Randomize
                    </button>
                    <button id="copyAllButton" type="button" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Copy All Phone Numbers
                    </button>
                </div>
            </div>

            @if($entries->isEmpty())
                <div class="text-center py-10 text-gray-500 dark:text-gray-400">
                    No giveaway entries yet.
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-700">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700" id="giveawayTable">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Phone</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Order Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entered At</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700" id="giveawayBody">
                            @foreach($entries as $index => $entry)
                                <tr>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white row-index">{{ $index + 1 }}</td>
                                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-white phone-cell">{{ $entry->phone }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $entry->invoice_number ?? ('INV-' . $entry->order_id) }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ optional($entry->order_date)->format('d M Y h:i A') ?? '-' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">{{ $entry->created_at->format('d M Y h:i A') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <script>
        const randomizeButton = document.getElementById('randomizeButton');
        const copyAllButton = document.getElementById('copyAllButton');
        const giveawayBody = document.getElementById('giveawayBody');

        function refreshSerialNumbers() {
            const rows = giveawayBody ? Array.from(giveawayBody.querySelectorAll('tr')) : [];
            rows.forEach((row, index) => {
                const cell = row.querySelector('.row-index');
                if (cell) {
                    cell.textContent = index + 1;
                }
            });
        }

        randomizeButton?.addEventListener('click', () => {
            if (!giveawayBody) return;
            const rows = Array.from(giveawayBody.querySelectorAll('tr'));
            for (let i = rows.length - 1; i > 0; i--) {
                const j = Math.floor(Math.random() * (i + 1));
                [rows[i], rows[j]] = [rows[j], rows[i]];
            }
            rows.forEach((row) => giveawayBody.appendChild(row));
            refreshSerialNumbers();
        });

        copyAllButton?.addEventListener('click', async () => {
            const phones = Array.from(document.querySelectorAll('.phone-cell'))
                .map((cell) => cell.textContent.trim())
                .filter(Boolean);

            if (phones.length === 0) {
                return;
            }

            const text = phones.join('\n');
            try {
                await navigator.clipboard.writeText(text);
                copyAllButton.textContent = 'Copied';
                setTimeout(() => {
                    copyAllButton.textContent = 'Copy All Phone Numbers';
                }, 1400);
            } catch (error) {
                alert('Copy failed. Please copy manually.');
            }
        });
    </script>
</x-admin-layout>
