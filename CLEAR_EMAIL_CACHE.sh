#!/bin/bash

# Script to clear all caches and verify mail views for email notifications

echo "🔧 Clearing all Laravel caches..."
cd /var/www/NinjaWrekcs

php artisan view:clear
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan optimize:clear

echo ""
echo "📦 Verifying mail views are published..."
if [ -d "resources/views/vendor/mail/html" ]; then
    echo "✅ Mail views directory exists"
    ls -la resources/views/vendor/mail/html/ | head -10
else
    echo "❌ Mail views directory NOT found! Publishing now..."
    php artisan vendor:publish --tag=laravel-mail --force
fi

echo ""
echo "🔍 Checking if mail component files exist..."
if [ -f "resources/views/vendor/mail/html/message.blade.php" ]; then
    echo "✅ message.blade.php exists"
else
    echo "❌ message.blade.php NOT found!"
fi

if [ -f "resources/views/vendor/mail/html/button.blade.php" ]; then
    echo "✅ button.blade.php exists"
else
    echo "❌ button.blade.php NOT found!"
fi

if [ -f "resources/views/vendor/mail/html/layout.blade.php" ]; then
    echo "✅ layout.blade.php exists"
else
    echo "❌ layout.blade.php NOT found!"
fi

echo ""
echo "✨ Done! Now test the email route:"
echo "   Visit: https://www.ninjawrecks.me/test-email-debug"
