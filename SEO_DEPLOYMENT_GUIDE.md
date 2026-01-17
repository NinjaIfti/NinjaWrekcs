# 🚀 SEO Deployment Guide for NinjaWrecks

Complete SEO setup guide for your Digital Ocean deployment.

## ✅ What's Already Implemented

### 1. **Meta Tags** ✅
- Title, Description, Keywords
- Open Graph (Facebook) tags
- Twitter Card tags
- Canonical URLs
- Mobile optimization

### 2. **Structured Data (JSON-LD)** ✅
- Product schema for shop items
- Organization/Store schema for main pages
- Proper pricing and availability info

### 3. **Sitemap** ✅
- Dynamic XML sitemap at `/sitemap.xml`
- Auto-updates when products change
- Includes all important pages

### 4. **Robots.txt** ✅
- Located at `/robots.txt`
- Blocks admin/private pages
- Points to sitemap

---

## 🔧 Required Setup Steps on Digital Ocean

### Step 1: Update Environment Variables

Edit your `.env` file on the server:

```bash
cd /var/www/ninjawrecks  # or your deployment path
nano .env
```

Update these values:
```env
APP_NAME="NinjaWrecks"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com  # Your actual domain

# For sitemap
SITE_URL=https://yourdomain.com
```

### Step 2: Update robots.txt with Your Domain

Edit `public/robots.txt`:
```bash
nano public/robots.txt
```

Change the last line:
```
Sitemap: https://yourdomain.com/sitemap.xml
```

### Step 3: SSL Certificate (HTTPS)

Ensure SSL is installed (required for SEO):
```bash
# If using Let's Encrypt
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com

# Auto-renewal
sudo certbot renew --dry-run
```

### Step 4: Force HTTPS in Laravel

In `app/Providers/AppServiceProvider.php`:
```php
public function boot(): void
{
    if ($this->app->environment('production')) {
        \URL::forceScheme('https');
    }
}
```

---

## 🌐 Google Search Console Setup

### 1. Verify Your Website

**Option A: HTML File Verification**
1. Go to [Google Search Console](https://search.google.com/search-console)
2. Add your property: `https://yourdomain.com`
3. Choose "HTML file" verification
4. Download the verification file
5. Upload to `public/` folder
6. Click "Verify"

**Option B: DNS Verification** (Recommended)
1. Choose "DNS record" verification
2. Add TXT record to your domain DNS
3. Click "Verify"

### 2. Submit Sitemap
```
1. In Search Console, go to "Sitemaps"
2. Enter: https://yourdomain.com/sitemap.xml
3. Click "Submit"
```

### 3. Request Indexing
- Submit your homepage URL
- Submit key product pages
- Wait 24-48 hours for Google to crawl

---

## 📊 Google Analytics Setup (Optional but Recommended)

### 1. Create GA4 Property
1. Go to [Google Analytics](https://analytics.google.com)
2. Create account and property
3. Get your Measurement ID (G-XXXXXXXXXX)

### 2. Add to Your Site

Create `resources/views/components/analytics.blade.php`:
```blade
@if(config('services.google.analytics_id') && app()->environment('production'))
<!-- Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ config('services.google.analytics_id') }}');
</script>
@endif
```

### 3. Add to .env
```env
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
```

### 4. Add to config/services.php
```php
'google' => [
    'analytics_id' => env('GOOGLE_ANALYTICS_ID'),
],
```

### 5. Include in Your Layouts
Add to `welcome.blade.php` and other pages in `<head>`:
```blade
@include('components.analytics')
```

---

## 🔍 SEO Best Practices Checklist

### Technical SEO ✅
- [x] HTTPS enabled
- [x] Sitemap.xml created and submitted
- [x] Robots.txt configured
- [x] Meta tags on all pages
- [x] Canonical URLs set
- [x] Mobile responsive design
- [x] Fast page load times
- [x] Structured data (JSON-LD)

### Content SEO (To Do)
- [ ] Unique titles for each page (max 60 chars)
- [ ] Unique descriptions (max 160 chars)
- [ ] H1 tags on every page
- [ ] Alt text for all images
- [ ] Internal linking strategy
- [ ] Regular content updates
- [ ] Blog/news section (optional)

### Local SEO (Bangladesh)
- [ ] Google My Business profile
- [ ] Local keywords (Dhaka, Bangladesh)
- [ ] Local phone number visible
- [ ] Local address on contact page
- [ ] Local reviews and testimonials

---

## 📱 Social Media Integration

### Open Graph Tags (Already Implemented ✅)
Your site automatically generates OG tags for:
- Facebook sharing
- WhatsApp previews
- Messenger previews

### Twitter Cards (Already Implemented ✅)
Your site generates Twitter card data for better tweet previews.

### To Improve Social Sharing:
1. Use high-quality product images (min 1200x630px)
2. Create share buttons on product pages
3. Monitor social shares in analytics

---

## 🚀 Performance Optimization for SEO

### 1. Enable Caching
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Optimize Images
- Use WebP format when possible
- Compress images (TinyPNG, ImageOptim)
- Add lazy loading to images below fold

### 3. Enable Gzip Compression
In Nginx config:
```nginx
gzip on;
gzip_types text/plain text/css application/json application/javascript text/xml application/xml application/xml+rss text/javascript;
```

### 4. Add Browser Caching
```nginx
location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

---

## 📈 Monitor Your SEO

### Tools to Use:
1. **Google Search Console** - Monitor search performance
2. **Google Analytics** - Track visitors
3. **PageSpeed Insights** - Check load times
4. **GTmetrix** - Performance analysis
5. **Ahrefs/SEMrush** - Keyword tracking (paid)

### Weekly Tasks:
- Check Search Console for errors
- Monitor keyword rankings
- Review page performance
- Check for broken links
- Update product descriptions

---

## 🔗 Important URLs to Submit

Submit these to Google Search Console:
```
https://yourdomain.com/
https://yourdomain.com/shop
https://yourdomain.com/about
https://yourdomain.com/contact
https://yourdomain.com/sitemap.xml
```

---

## 🎯 Keywords to Target

### Primary Keywords:
- Valorant collectibles Bangladesh
- Valorant figures Dhaka
- Valorant merchandise Bangladesh
- Buy Valorant items Bangladesh
- Valorant agent figures
- Valorant knives Bangladesh

### Long-tail Keywords:
- Where to buy Valorant collectibles in Bangladesh
- Authentic Valorant merchandise Dhaka
- Valorant gaming collectibles online Bangladesh
- Best Valorant merch store Bangladesh

---

## 🛠️ Troubleshooting

### Sitemap not working?
```bash
php artisan route:clear
php artisan cache:clear
# Visit: https://yourdomain.com/sitemap.xml
```

### Pages not indexed?
- Check robots.txt
- Submit URL in Search Console
- Wait 48-72 hours
- Check for crawl errors

### Slow load times?
```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
```

---

## 📞 Need Help?

### Useful Resources:
- [Google SEO Starter Guide](https://developers.google.com/search/docs/beginner/seo-starter-guide)
- [Search Console Help](https://support.google.com/webmasters)
- [Schema.org Documentation](https://schema.org/docs/gs.html)

---

## 🎉 Final Checklist

Before going live:
- [ ] Update APP_URL in .env
- [ ] Update robots.txt with real domain
- [ ] Install SSL certificate (HTTPS)
- [ ] Submit sitemap to Google Search Console
- [ ] Add Google Analytics (optional)
- [ ] Test all pages load correctly
- [ ] Check mobile responsiveness
- [ ] Verify structured data with [Rich Results Test](https://search.google.com/test/rich-results)
- [ ] Set up 301 redirects for any old URLs
- [ ] Create XML sitemap backup

---

**Your SEO foundation is ready! 🚀**

Monitor regularly and optimize based on real data from Search Console and Analytics.
