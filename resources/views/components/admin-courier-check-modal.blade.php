<!-- Shared "Courier Check" modal - available on every admin page via the admin layout.
     Call CourierCheck.openModal('017xxxxxxxx') from anywhere to look up a phone number. -->
<div id="courier-check-modal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Courier Check
                    <span id="courier-check-modal-phone" class="text-gray-500 dark:text-gray-400 text-sm font-normal"></span>
                </h3>
                <button type="button" onclick="CourierCheck.closeModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <div id="courier-check-modal-body"></div>
        </div>
    </div>
</div>

<script>
    window.CourierCheck = (function () {
        const lookupUrl = @json(route('admin.courier-check.lookup'));
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        function escapeHtml(str) {
            const div = document.createElement('div');
            div.textContent = str ?? '';
            return div.innerHTML;
        }

        function ratioTextClasses(ratio) {
            if (ratio >= 80) return 'text-green-600 dark:text-green-400';
            if (ratio >= 50) return 'text-yellow-600 dark:text-yellow-400';
            return 'text-red-600 dark:text-red-400';
        }

        function ratioBoxClasses(ratio) {
            if (ratio >= 80) return `${ratioTextClasses(ratio)} border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20`;
            if (ratio >= 50) return `${ratioTextClasses(ratio)} border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20`;
            return `${ratioTextClasses(ratio)} border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20`;
        }

        function courierCard(courier) {
            const ratio = Number(courier.success_ratio ?? 0);
            return `
                <div class="border ${ratioBoxClasses(ratio)} rounded-lg p-3 flex items-center gap-3">
                    ${courier.logo ? `<img src="${escapeHtml(courier.logo)}" alt="${escapeHtml(courier.name)}" class="w-8 h-8 object-contain rounded bg-white flex-shrink-0">` : ''}
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-gray-900 dark:text-white text-sm">${escapeHtml(courier.name)}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            ${courier.success_parcel ?? 0} success / ${courier.cancelled_parcel ?? 0} cancelled / ${courier.total_parcel ?? 0} total
                        </p>
                    </div>
                    <div class="text-lg font-bold ${ratioTextClasses(ratio)}">${ratio}%</div>
                </div>
            `;
        }

        function renderLoading(container) {
            container.innerHTML = `
                <div class="flex items-center justify-center py-10 text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin w-6 h-6 mr-2" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Checking courier history...
                </div>
            `;
        }

        function renderResult(container, result) {
            if (!result || !result.success) {
                container.innerHTML = `
                    <div class="p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg text-red-700 dark:text-red-300 text-sm">
                        ${escapeHtml(result?.error || 'Could not fetch courier history.')}
                    </div>
                `;
                return;
            }

            const data = result.data || {};
            const reports = result.reports || [];
            const summary = data.summary;

            const courierCards = Object.entries(data)
                .filter(([key]) => key !== 'summary')
                .map(([, courier]) => courierCard(courier))
                .join('');

            const summaryHtml = summary ? `
                <div class="mb-4 p-4 bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-lg">
                    <p class="text-xs uppercase tracking-wide text-violet-600 dark:text-violet-400 font-semibold mb-1">Overall</p>
                    <div class="flex items-baseline gap-3">
                        <span class="text-2xl font-bold ${ratioTextClasses(Number(summary.success_ratio ?? 0))}">${summary.success_ratio ?? 0}%</span>
                        <span class="text-sm text-gray-600 dark:text-gray-400">${summary.success_parcel ?? 0} success / ${summary.cancelled_parcel ?? 0} cancelled / ${summary.total_parcel ?? 0} total parcels</span>
                    </div>
                </div>
            ` : '';

            const reportsHtml = reports.length ? `
                <div class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-300 dark:border-red-800 rounded-lg">
                    <p class="text-red-800 dark:text-red-300 font-semibold text-sm mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        ${reports.length} Fraud Report${reports.length > 1 ? 's' : ''}
                    </p>
                    <ul class="space-y-2">
                        ${reports.map(r => `
                            <li class="text-sm text-red-700 dark:text-red-300 border-t border-red-200 dark:border-red-800 pt-2 first:border-t-0 first:pt-0">
                                <span class="font-medium">${escapeHtml(r.name)}</span> via ${escapeHtml(r.courierName)} &mdash; ${escapeHtml(r.details)}
                                <span class="text-xs text-red-500 dark:text-red-400 block">${escapeHtml(r.created_at)}</span>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            ` : '';

            container.innerHTML = `
                ${summaryHtml}
                <div class="grid sm:grid-cols-2 gap-3">${courierCards || '<p class="text-sm text-gray-500 dark:text-gray-400 col-span-2">No courier history found for this number.</p>'}</div>
                ${reportsHtml}
            `;
        }

        function fetchAndRender(phone, container) {
            renderLoading(container);

            fetch(lookupUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ phone }),
            })
                .then(response => response.json().then(data => ({ ok: response.ok, data })))
                .then(({ data }) => renderResult(container, data))
                .catch(() => renderResult(container, { success: false, error: 'Network error while checking courier history.' }));
        }

        function openModal(phone) {
            if (!phone) return;
            document.getElementById('courier-check-modal-phone').textContent = phone;
            document.getElementById('courier-check-modal').classList.remove('hidden');
            fetchAndRender(phone, document.getElementById('courier-check-modal-body'));
        }

        function closeModal() {
            document.getElementById('courier-check-modal').classList.add('hidden');
        }

        return { openModal, closeModal, fetchAndRender };
    })();

    document.getElementById('courier-check-modal')?.addEventListener('click', function (e) {
        if (e.target === this) CourierCheck.closeModal();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') CourierCheck.closeModal();
    });
</script>
