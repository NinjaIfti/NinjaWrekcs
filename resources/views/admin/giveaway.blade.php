<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Giveaway Panel
        </h2>
    </x-slot>

    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('warning'))
                <div class="mb-4 p-4 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded-lg">
                    {{ session('warning') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('admin.giveaway.manual-store') }}" method="POST" class="mb-6 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                @csrf
                <label for="manual_phone" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                    Manual phone entry
                </label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <input
                        id="manual_phone"
                        name="phone"
                        type="text"
                        placeholder="+8801XXXXXXXXX / 8801XXXXXXXXX / 01XXXXXXXXX"
                        class="w-full border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 bg-white dark:bg-gray-800 text-gray-900 dark:text-white"
                        required
                    >
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition whitespace-nowrap">
                        Add Manual Entry
                    </button>
                </div>
            </form>

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
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Action</th>
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
                                    <td class="px-4 py-3 text-right">
                                        <form action="{{ route('admin.giveaway.destroy', $entry) }}" method="POST" onsubmit="return confirm('Delete this giveaway entry?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 text-xs font-semibold bg-red-600 text-white rounded-md hover:bg-red-700 transition">
                                                Delete
                                            </button>
                                        </form>
                                    </td>
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
