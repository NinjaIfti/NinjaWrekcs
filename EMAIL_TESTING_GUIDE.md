# Email Testing Guide - NinjaWrekcs

## 📧 Email Configuration

Your Brevo SMTP is configured and ready to use:
- **SMTP Host:** smtp-relay.brevo.com
- **Port:** 587
- **Encryption:** TLS
- **From Address:** orders@ninjawrecks.me
- **From Name:** NinjaWrekcs

---

## 🧪 How to Test Emails

### Method 1: Using Test Route

Visit these URLs in your browser (server must be running):

#### 1. Test Simple Email
```
http://localhost:8000/test-email?type=simple&to=your-email@example.com
```

#### 2. Test Order Confirmation Email
```
http://localhost:8000/test-email?type=order-confirmation&to=your-email@example.com
```

#### 3. Test Order Status Update Email
```
http://localhost:8000/test-email?type=order-status&to=your-email@example.com
```

**Note:** For order emails, you need at least one order in your database.

---

### Method 2: Real Order Flow

#### Test Order Confirmation:
1. Add products to cart
2. Go to checkout
3. Complete the order
4. ✉️ Automatic email sent!

#### Test Order Status Update:
1. Login to admin panel
2. Go to Orders
3. Click on any order
4. Change status (e.g., from "pending" to "confirmed")
5. ✉️ Automatic email sent!

---

## 📨 Email Templates

### 1. Order Confirmation Email
**Sent:** When customer places an order  
**Trigger:** Automatically after checkout  
**Template:** `resources/views/emails/order-confirmation.blade.php`

**Features:**
- ✅ Order summary with items
- ✅ Payment details
- ✅ Shipping information
- ✅ Coupon discount display
- ✅ Order tracking link
- ✅ Next steps guide
- ✅ Professional branding

---

### 2. Order Status Update Email
**Sent:** When admin changes order status  
**Trigger:** Admin updates status in dashboard  
**Template:** `resources/views/emails/order-status-updated.blade.php`

**Features:**
- ✅ Status-specific messaging
- ✅ Visual timeline tracker
- ✅ Order summary
- ✅ What's next information
- ✅ Contact details
- ✅ Professional design

**Statuses:**
- `pending` → Order placed
- `confirmed` → Payment verified
- `processing` → Preparing items
- `shipped` → On the way
- `delivered` → Completed
- `cancelled` → Cancelled

---

## 🎨 Email Examples

### Order Confirmation Email Preview
```
Subject: Order Confirmation - Order #123 - NinjaWrekcs

Order Confirmed! 🎮

Thank you for your order, John Doe!

Order Details:
- Order Number: #123
- Date: December 18, 2025
- Status: Pending

Order Items:
[Item 1] x 2 = ৳500
[Item 2] x 1 = ৳300

Total: ৳800

[View Order Status Button]
```

### Order Status Update Email Preview
```
Subject: Order Status Update - Order #123 - NinjaWrekcs

Order Status Update 📦

Hello John Doe,

Great news! Your order has been confirmed and 
we've received your payment.

Current Status: Confirmed
Previous Status: Pending

Order Timeline:
✅ Pending - Order Placed
📍 Confirmed - Payment Verified (You are here!)
⏺️ Processing - Preparing Items
⏺️ Shipped - On the Way
⏺️ Delivered - Completed

[View Order Details Button]
```

---

## 🔧 Troubleshooting

### Emails Not Sending?

1. **Check Queue:** Emails are queued, make sure queue worker is running
   ```bash
   php artisan queue:work
   ```

2. **Check Logs:** Look for errors in `storage/logs/laravel.log`

3. **Verify Email Address:** Make sure the order has a valid email

4. **Test Brevo Connection:**
   ```bash
   php artisan tinker
   Mail::raw('Test', function($m) { $m->to('your-email@example.com')->subject('Test'); });
   ```

5. **Check .env file:** Ensure all MAIL_ variables are set correctly

---

## 📋 Email Sending Summary

### Automatic Emails:
- ✅ **Order Placed** → Order Confirmation Email
- ✅ **Status Changed** → Order Status Update Email
- ✅ **User Registered** → Email Verification (already implemented)

### Manual Testing:
- ✅ `/test-email` route for testing
- ✅ Multiple test types available
- ✅ Custom recipient email

---

## 💡 Tips

1. **Check Spam Folder:** Sometimes emails land in spam
2. **Whitelist Sender:** Add orders@ninjawrecks.me to contacts
3. **Test with Real Email:** Use your actual email for testing
4. **Monitor Brevo Dashboard:** Check sending statistics
5. **Queue Processing:** For production, use queue worker for faster response

---

## 🚀 Production Checklist

Before going live:
- [ ] Test all email templates
- [ ] Verify from address works
- [ ] Check email rendering on different clients
- [ ] Set up queue worker
- [ ] Monitor sending limits (Brevo free tier)
- [ ] Add email logging for tracking
- [ ] Test with real customer email addresses

---

## 📊 Brevo Limits

**Free Plan:**
- 300 emails/day
- Unlimited contacts

**Note:** Monitor your usage in Brevo dashboard

---

## ✅ Implementation Status

- ✅ Brevo SMTP configured
- ✅ Order confirmation email created
- ✅ Order status update email created
- ✅ Emails sent automatically on order placement
- ✅ Emails sent automatically on status change
- ✅ Professional HTML templates
- ✅ Mobile responsive design
- ✅ Error handling implemented
- ✅ Test route available

---

**All emails are ready to go! 🎉**




