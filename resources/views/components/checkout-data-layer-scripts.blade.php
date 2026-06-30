@php
    $checkoutItemsJson = json_encode(
        \App\Support\DataLayerHelper::cartItemsPayload($cartItems),
        JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    );
@endphp

<script>
(function () {
    const checkoutItems = {!! $checkoutItemsJson !!};
    const baseSubtotal = {{ (float) $cartSubTotal }};
    const hasPreorder = {{ $hasBookableItems ? 'true' : 'false' }};
    const bookingAmount = {{ (float) $totalBookingAmount }};

    function sha256(message) {
        const msgBuffer = new TextEncoder().encode(message);
        return crypto.subtle.digest('SHA-256', msgBuffer).then(function (hashBuffer) {
            return Array.from(new Uint8Array(hashBuffer))
                .map(function (b) { return b.toString(16).padStart(2, '0'); })
                .join('');
        });
    }

    function normalizePhone(phone) {
        var digits = phone.replace(/\D/g, '');
        if (digits.indexOf('880') === 0) return digits;
        if (digits.indexOf('0') === 0) return '880' + digits.slice(1);
        return '880' + digits;
    }

    function splitName(name) {
        var parts = name.trim().toLowerCase().split(/\s+/).filter(Boolean);
        return {
            first: parts[0] || '',
            last: parts.length > 1 ? parts.slice(1).join(' ') : ''
        };
    }

    function getDeliveryCharge() {
        var selected = document.querySelector('input[name="delivery_location"]:checked');
        if (!selected) return 0;
        return parseFloat(selected.getAttribute('data-charge') || '0');
    }

    function getDeliveryLocation() {
        var selected = document.querySelector('input[name="delivery_location"]:checked');
        return selected ? selected.value : null;
    }

    function getPaymentMethod() {
        var selected = document.querySelector('input[name="payment_method"]:checked');
        return selected ? selected.value : null;
    }

    function getCheckoutSummary() {
        var deliveryCharge = getDeliveryCharge();
        var discountField = document.getElementById('checkout_discount_value');
        var discount = discountField ? (parseFloat(discountField.value || '0') || 0) : 0;
        var total = baseSubtotal + deliveryCharge - discount;

        return {
            currency: 'BDT',
            value: total,
            subtotal: baseSubtotal,
            shipping: deliveryCharge,
            discount: discount,
            has_preorder: hasPreorder,
            booking_amount: bookingAmount,
            delivery_location: getDeliveryLocation(),
            payment_method: getPaymentMethod(),
            item_count: checkoutItems.length,
            items: checkoutItems
        };
    }

    function pushDataLayerEvent(eventName, extra) {
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push(Object.assign({ event: eventName }, extra || {}));
    }

    function buildHashedUserData(name, phone, email, address, deliveryLocation) {
        var nameParts = splitName(name || '');
        var region = deliveryLocation === 'inside_dhaka'
            ? 'dhaka'
            : (deliveryLocation === 'outside_dhaka' ? 'bangladesh' : null);

        var jobs = [
            email ? sha256(email.trim().toLowerCase()) : Promise.resolve(null),
            phone ? sha256(normalizePhone(phone)) : Promise.resolve(null),
            nameParts.first ? sha256(nameParts.first) : Promise.resolve(null),
            nameParts.last ? sha256(nameParts.last) : Promise.resolve(null),
            address ? sha256(address.trim().toLowerCase()) : Promise.resolve(null),
            region ? sha256(region) : Promise.resolve(null),
            sha256('bd')
        ];

        return Promise.all(jobs).then(function (results) {
            return {
                email_address: results[0],
                phone_number: results[1],
                address: {
                    first_name: results[2],
                    last_name: results[3],
                    street: results[4],
                    region: results[5],
                    country: results[6]
                }
            };
        });
    }

    function pushShippingInfo() {
        var summary = getCheckoutSummary();
        pushDataLayerEvent('add_shipping_info', {
            ecommerce: {
                currency: summary.currency,
                value: summary.value,
                shipping: summary.shipping,
                items: summary.items
            },
            checkout: {
                delivery_location: summary.delivery_location,
                subtotal: summary.subtotal,
                shipping: summary.shipping,
                has_preorder: summary.has_preorder,
                booking_amount: summary.booking_amount
            }
        });
    }

    function pushPaymentInfo() {
        var summary = getCheckoutSummary();
        pushDataLayerEvent('add_payment_info', {
            ecommerce: {
                currency: summary.currency,
                value: summary.value,
                items: summary.items
            },
            checkout: {
                payment_method: summary.payment_method,
                delivery_location: summary.delivery_location,
                subtotal: summary.subtotal,
                shipping: summary.shipping,
                discount: summary.discount,
                has_preorder: summary.has_preorder,
                booking_amount: summary.booking_amount
            }
        });
    }

    document.querySelectorAll('input[name="delivery_location"]').forEach(function (input) {
        input.addEventListener('change', pushShippingInfo);
    });

    document.querySelectorAll('input[name="payment_method"]').forEach(function (input) {
        input.addEventListener('change', pushPaymentInfo);
    });

    var checkoutForm = document.querySelector('form[action="{{ route('checkout.store') }}"]');

    if (document.querySelector('input[name="delivery_location"]:checked')) {
        pushShippingInfo();
    }

    if (document.querySelector('input[name="payment_method"]:checked')) {
        pushPaymentInfo();
    }

    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function () {
            var name = (document.getElementById('name') || {}).value || '';
            var phone = (document.getElementById('phone') || {}).value || '';
            var emailEl = document.getElementById('email');
            var formEmail = checkoutForm.getAttribute('data-user-email') || '';
            var email = emailEl && emailEl.value ? emailEl.value : formEmail;
            var address = (document.getElementById('address') || {}).value || '';
            var summary = getCheckoutSummary();

            buildHashedUserData(name, phone, email, address, summary.delivery_location).then(function (userData) {
                pushDataLayerEvent('checkout_submit', {
                    ecommerce: {
                        currency: summary.currency,
                        value: summary.value,
                        shipping: summary.shipping,
                        coupon: (document.getElementById('coupon_code_input') || {}).value || undefined,
                        items: summary.items
                    },
                    checkout: {
                        subtotal: summary.subtotal,
                        discount: summary.discount,
                        delivery_location: summary.delivery_location,
                        payment_method: summary.payment_method,
                        has_preorder: summary.has_preorder,
                        booking_amount: summary.booking_amount,
                        item_count: summary.item_count
                    },
                    user_data: userData
                });
            });
        });
    }
})();
</script>
