# 🚀 SEO Quick Reference Card

## 📋 Essential Steps (After Deploying to Digital Ocean)

### 1️⃣ Update .env on Server
```bash
APP_URL=https://yourdomain.com
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX  # Optional
```

### 2️⃣ Update robots.txt
```bash
# Edit public/robots.txt
# Change last line to:
Sitemap: https://yourdomain.com/sitemap.xml
```

### 3️⃣ Run Optimization
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 4️⃣ Test Your URLs
- ✅ https://yourdomain.com/sitemap.xml
- ✅ https://yourdomain.com/robots.txt
- ✅ https://yourdomain.com (homepage)
- ✅ https://yourdomain.com/shop

---

## 🌐 Google Search Console (Most Important!)

### Submit Your Site
1. Go to: https://search.google.com/search-console
2. Add property: `https://yourdomain.com`
3. Verify using HTML file or DNS
4. Submit sitemap: `https://yourdomain.com/sitemap.xml`

### Request Indexing
- Submit 5-10 important URLs manually
- Wait 24-48 hours for crawling
- Monitor "Coverage" section for errors

---

## 📊 Optional: Google Analytics

1. Create GA4 property: https://analytics.google.com
2. Get Measurement ID: `G-XXXXXXXXXX`
3. Add to .env: `GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX`
4. Restart server or run: `php artisan config:cache`

---

## ✅ SEO Checklist

Before Going Live:
- [ ] HTTPS enabled (SSL certificate)
- [ ] APP_URL updated in .env
- [ ] robots.txt updated with domain
- [ ] Sitemap accessible
- [ ] Google Search Console verified
- [ ] Sitemap submitted to GSC
- [ ] All product images have alt text
- [ ] Page load time < 3 seconds
- [ ] Mobile responsive verified
- [ ] All pages have unique titles

---

## 🔍 Test Your SEO

### Tools to Test:
1. **Sitemap**: https://yourdomain.com/sitemap.xml
2. **Rich Results**: https://search.google.com/test/rich-results
3. **Mobile-Friendly**: https://search.google.com/test/mobile-friendly
4. **PageSpeed**: https://pagespeed.web.dev
5. **Meta Tags**: View page source and check `<head>` section

---

## 📱 Social Media Preview

Test how your site looks when shared:
- **Facebook**: https://developers.facebook.com/tools/debug/
- **Twitter**: https://cards-dev.twitter.com/validator
- **LinkedIn**: https://www.linkedin.com/post-inspector/

---

## 🐛 Common Issues

### Sitemap 404 Error?
```bash
php artisan route:clear
php artisan cache:clear
```

### Pages not indexing?
- Wait 48-72 hours
- Submit URL manually in GSC
- Check robots.txt isn't blocking

### Slow site?
```bash
php artisan optimize
composer dump-autoload -o
```

---

## 📞 Important Links

- 🗺️ Your Sitemap: `/sitemap.xml`
- 🤖 Robots File: `/robots.txt`
- 📊 Search Console: https://search.google.com/search-console
- 📈 Analytics: https://analytics.google.com
- 📖 Full Guide: `SEO_DEPLOYMENT_GUIDE.md`

---

## 🎯 Target Keywords

Focus on these for Bangladesh market:
- Valorant collectibles Bangladesh
- Valorant figures Dhaka
- Buy Valorant merchandise Bangladesh
- Valorant gaming store BD
- Valorant agent figures Bangladesh
- Authentic Valorant merch Dhaka

---

**Need Help?** See `SEO_DEPLOYMENT_GUIDE.md` for detailed instructions.
