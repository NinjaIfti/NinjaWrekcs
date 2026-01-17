#!/bin/bash

# SEO Deployment Script for NinjaWrecks
# Run this after deploying to Digital Ocean

echo "🚀 NinjaWrecks SEO Deployment Script"
echo "======================================"
echo ""

# Check if .env exists
if [ ! -f .env ]; then
    echo "❌ Error: .env file not found!"
    echo "Please create .env file first"
    exit 1
fi

# Clear caches
echo "📦 Clearing caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Generate app key if not set
if grep -q "APP_KEY=$" .env; then
    echo "🔑 Generating application key..."
    php artisan key:generate
fi

# Check sitemap
echo "🗺️  Testing sitemap..."
php artisan route:list | grep sitemap
if [ $? -eq 0 ]; then
    echo "✅ Sitemap route configured"
else
    echo "⚠️  Warning: Sitemap route not found"
fi

# Check robots.txt
if [ -f public/robots.txt ]; then
    echo "✅ robots.txt exists"
else
    echo "⚠️  Warning: robots.txt not found in public/"
fi

# Storage link
if [ ! -L public/storage ]; then
    echo "🔗 Creating storage link..."
    php artisan storage:link
fi

# Set permissions
echo "🔒 Setting permissions..."
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || echo "Note: Run as sudo for ownership changes"

echo ""
echo "✨ Deployment complete!"
echo ""
echo "📋 Next steps:"
echo "1. Update APP_URL in .env with your domain"
echo "2. Update Sitemap URL in public/robots.txt"
echo "3. Test sitemap: https://yourdomain.com/sitemap.xml"
echo "4. Submit sitemap to Google Search Console"
echo "5. Add GOOGLE_ANALYTICS_ID to .env (optional)"
echo ""
echo "📖 See SEO_DEPLOYMENT_GUIDE.md for full instructions"
