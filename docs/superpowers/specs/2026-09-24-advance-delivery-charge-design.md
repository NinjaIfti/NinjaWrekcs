# Advance delivery charge for risky orders

**Status:** design, awaiting approval
**Date:** 2026-09-24

## The problem

Cash on delivery is 457 of NinjaWrecks' 491 orders. Every refused parcel costs
the delivery charge with nothing recovered. The store already pays for the BD
Courier API, which reports any phone number's delivery history across couriers,
but today it is only a manual lookup an admin can run — it does not affect
checkout.

The owner wants customers to pay **the delivery charge only** up front when an
order looks risky, with the rest still paid on delivery.

## What this is not

**This is not a payment gateway.** Automated bKash or Nagad payment requires an
approved merchant account, which requires business documents and a
per-transaction fee. The owner has neither and wants neither, so no gateway is
proposed. Payment stays what it is today: the customer sends money from their
own bKash/Nagad to the store's number and enters the transaction ID, and an
admin verifies it.

Third-party tools that claim to automate verification for *personal* accounts
were considered and rejected: they are paid software working by unofficial
means (reading confirmation SMS or screen automation), they can break without
notice, and they may conflict with bKash's terms.

So the work here is **deciding who must pay in advance, and making verification
a real workflow instead of two unchecked text fields**.

## The rule

Advance payment of the delivery charge is required when **any** of these holds:

1. The order is going **outside Dhaka** — regardless of the customer's rating.
2. The customer's courier **success ratio is below 80%**, and they have at least
   one past parcel.
3. The **courier API could not be reached** (timeout, error, or not configured).

Otherwise the order proceeds as it does today.

### Deliberate decisions

**A customer with no courier history pays nothing in advance.** The API reports
an unknown number as 0% success, so a naive `< 80` test would force advance
payment on every first-time customer — precisely the people least tolerant of
friction. The rule therefore tests `total_parcel > 0 && success_ratio < 80`.

**An API failure requires advance payment** (owner's decision, 2026-09-24).
The consequence is worth stating plainly: during any outage of bdcourier.com,
*every* customer is asked to pay in advance, including first-time and
well-rated ones, and inside-Dhaka orders that would normally sail through. The
existing service already retries twice with a 12-second timeout, so this only
bites on a sustained outage. If lost checkouts during an outage become a
problem, the mitigation is to flip this one rule to fail-open; it is a single
branch in `AdvancePaymentPolicy`.

## How it works

### Deciding

A new `App\Services\AdvancePaymentPolicy` answers one question:

```php
public function decide(string $phone, string $deliveryLocation, float $deliveryCharge): AdvancePaymentDecision
```

`AdvancePaymentDecision` is a small readonly object carrying `required` (bool),
`amount` (the delivery charge), `reason` (an enum-ish string: `outside_dhaka`,
`low_success_ratio`, `courier_check_unavailable`), and the observed
`successRatio` / `totalParcels` where known. Returning a decision object rather
than a bool keeps the "why" available for the order record and the admin
screen, which is what makes a disputed order arguable after the fact.

The policy calls the existing `CourierCheckService`. Its result is **cached per
phone number for 6 hours** so that a customer revisiting checkout, or an admin
opening the order, does not spend API quota again. A failed lookup is **not**
cached — otherwise one blip would force advance payment on that customer for
six hours.

### Checkout

The decision is made **server-side at submit**, in `CheckoutController::store`,
inside the existing transaction. That is the authoritative check; anything the
browser was told earlier is a convenience only.

Before that, an AJAX endpoint lets the checkout page tell the customer what to
expect once they have entered their phone and chosen a delivery location, so
the payment instructions do not appear as a surprise at the final step. The
endpoint returns only `{required, amount, reason}` — never the customer's
courier history, which is commercially sensitive and none of their business.

When advance payment is required, checkout requires `transaction_number` and
`sending_number` (today these are required only when the customer chooses the
`bkash` method). The store's receiving number comes from config, so it can be
changed without a deploy.

### Order record

Five columns on `orders`, all nullable so existing rows are untouched:

| Column | Purpose |
|---|---|
| `advance_required` | boolean — was advance payment demanded |
| `advance_amount` | the delivery charge demanded at that moment |
| `advance_reason` | which rule fired |
| `advance_verified_at` | when an admin confirmed the money arrived |
| `courier_success_ratio` | the ratio observed at checkout, or null |

`courier_success_ratio` is a **snapshot, not a live value**. The customer's
rating changes over time; what matters for a disputed order is what was true
when the order was placed.

### Admin

The order list gets a filter and a badge for "advance payment pending
verification". The order page shows the transaction ID, the sending number, the
amount expected, the reason advance payment was demanded, and the ratio at the
time — with a **Verify** button that stamps `advance_verified_at`, and a
**Reject** button that leaves it unverified and notes it.

Verification is deliberately **not** wired into order status. Confirming an
order already deducts stock ([order stock follows status]), and entangling the
two would make a mis-click on Verify move stock. The admin sees the advance
payment state beside the status and decides.

## What is explicitly out of scope

- Any automated payment confirmation.
- Refunding the advance if an order is cancelled — handled by hand, as now.
- Charging anything beyond the delivery charge up front.
- Changing COD for good customers inside Dhaka, which stays exactly as it is.

## Testing

- `AdvancePaymentPolicy`: outside Dhaka forces advance regardless of a 100%
  ratio; a 79% ratio with history forces it; an 81% ratio does not; **zero
  parcels does not**; an API failure does; a failure is not cached while a
  success is.
- Checkout: an order needing advance payment is rejected without a transaction
  number; one not needing it is accepted without one; the decision is taken
  server-side even if the browser was told otherwise (post directly with a
  tampered payload).
- The AJAX endpoint never returns ratio or parcel counts.
- Admin: Verify stamps the timestamp and does not touch order status or stock.

Per the project's testing notes: flush the cache in `setUp`, memoise the admin
user, and build orders with `Order::create` — there is no `OrderFactory`.

## Rollout

The courier API key is already configured in production. One migration adds the
five columns. The 80% threshold, the 6-hour cache window and the receiving
bKash/Nagad number live in config so they can be tuned without a deploy.

Worth watching after release: how many orders fall into each `advance_reason`.
If `courier_check_unavailable` is a meaningful share, that rule needs revisiting.
