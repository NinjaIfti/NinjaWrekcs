# Digital Ocean Server Setup Guide

Complete guide for setting up your Laravel application with Redis caching on Digital Ocean.

## Table of Contents
1. [Redis Installation](#redis-installation)
2. [PHP Redis Extension](#php-redis-extension)
3. [Laravel Configuration](#laravel-configuration)
4. [Testing](#testing)
5. [Security](#security)
6. [Monitoring](#monitoring)
7. [Troubleshooting](#troubleshooting)

---

## Redis Installation

### Step 1: Connect to Your Server
```bash
ssh root@your_droplet_ip
# or if you have a user account
ssh your_username@your_droplet_ip
```

### Step 2: Update System Packages
```bash
sudo apt-get update
sudo apt-get upgrade -y
```

### Step 3: Install Redis
```bash
sudo apt-get install redis-server -y
```

### Step 4: Configure Redis Security

**Generate a strong password:**
```bash
openssl rand -base64 32
# Save this password - you'll need it for Laravel .env file
```

**Edit Redis configuration using direct commands:**

Run these commands to automatically configure Redis:

```bash
# Set Redis password (replace with your password)
sudo sed -i 's/^# requirepass foobared/requirepass WncN4gU+FTI508gw60kHA7VhYwc0MuPRTo6dCSEOfQI=/' /etc/redis/redis.conf

# If the above doesn't work (line might be different), try this:
sudo sed -i 's/^#*requirepass.*/requirepass WncN4gU+FTI508gw60kHA7VhYwc0MuPRTo6dCSEOfQI=/' /etc/redis/redis.conf

# Set bind to localhost only
sudo sed -i 's/^bind 127.0.0.1 ::1/bind 127.0.0.1/' /etc/redis/redis.conf

# Ensure protected-mode is yes
sudo sed -i 's/^protected-mode no/protected-mode yes/' /etc/redis/redis.conf

# Change supervised to systemd
sudo sed -i 's/^supervised no/supervised systemd/' /etc/redis/redis.conf
```

**Verify the changes:**
```bash
# Check password is set
sudo grep "^requirepass" /etc/redis/redis.conf

# Check bind address
sudo grep "^bind" /etc/redis/redis.conf

# Check protected mode
sudo grep "^protected-mode" /etc/redis/redis.conf

# Check supervised (if no output, add it)
sudo grep "^supervised" /etc/redis/redis.conf
```

**If supervised line is missing, add it:**
```bash
# Add supervised systemd line (if it doesn't exist)
echo "supervised systemd" | sudo tee -a /etc/redis/redis.conf

# Or if you want to replace existing supervised line:
sudo sed -i 's/^supervised.*/supervised systemd/' /etc/redis/redis.conf
```

**Alternative: Manual editing (if commands don't work)**
```bash
sudo nano /etc/redis/redis.conf
```

Then manually find and change:
1. `# requirepass foobared` → `requirepass WncN4gU+FTI508gw60kHA7VhYwc0MuPRTo6dCSEOfQI=`
2. `bind 127.0.0.1 ::1` → `bind 127.0.0.1`
3. `supervised no` → `supervised systemd`

### Step 5: Start Redis Service
```bash
sudo systemctl start redis-server
sudo systemctl enable redis-server
```

**Verify Redis is running:**
```bash
sudo systemctl status redis-server
```
You should see `active (running)` in green.

### Step 6: Test Redis Connection
```bash
redis-cli -a YOUR_GENERATED_PASSWORD ping
```
Should return: `PONG`

---

## PHP Redis Extension

### Step 1: Check PHP Version
```bash
php -v
```
Note your PHP version (e.g., 8.2, 8.1, etc.)

### Step 2: Install PHP Redis Extension

**For PHP 8.3:**
```bash
sudo apt-get install php8.3-redis -y
```

**For PHP 8.2:**
```bash
sudo apt-get install php8.2-redis -y
```

**For PHP 8.1:**
```bash
sudo apt-get install php8.1-redis -y
```

**For other versions, adjust the version number accordingly.**

**Alternative: Using PECL (if apt package doesn't work)**
```bash
sudo apt-get install php-pear php8.2-dev -y
sudo pecl install redis
echo "extension=redis.so" | sudo tee /etc/php/8.2/mods-available/redis.ini
sudo phpenmod redis
```

### Step 3: Restart PHP-FPM
```bash
# For PHP 8.2
sudo systemctl restart php8.2-fpm

# Or if using generic service
sudo systemctl restart php-fpm

# If using Apache instead of Nginx
sudo systemctl restart apache2
```

### Step 4: Verify Extension is Loaded
```bash
php -m | grep redis
```
Should output: `redis`

---

## Laravel Configuration

### Step 1: Navigate to Your Laravel Project
```bash
cd /var/www/your-project-name
# or wherever your Laravel project is located
```

### Step 2: Edit .env File
```bash
nano .env
```

### Step 3: Update Cache Configuration

Find or add these lines in your `.env` file:

```env
CACHE_STORE=redis
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=YOUR_GENERATED_PASSWORD_HERE
REDIS_PORT=6379
```

**Important:** Replace `YOUR_GENERATED_PASSWORD_HERE` with the password you generated in Step 4 of Redis Installation.

**Save and exit:**
- Press `Ctrl + X`
- Press `Y` to confirm
- Press `Enter` to save

### Step 4: Clear and Cache Laravel Configuration
```bash
php artisan config:clear
php artisan cache:clear
php artisan config:cache
```

---

## Testing

### Test Redis with Laravel

```bash
php artisan tinker
```

Then run these commands in tinker:
```php
Cache::put('test', 'redis_works', 60);
Cache::get('test');
```

**Expected output:** `"redis_works"`

If you see this, Redis is working correctly! Type `exit` to leave tinker.

### Test Redis Directly
```bash
redis-cli -a YOUR_PASSWORD
```

Then in Redis CLI:
```redis
SET test "Hello Redis"
GET test
```

Should return: `"Hello Redis"`

Type `exit` to leave Redis CLI.

---

## Security

### Security Checklist

- ✅ **Redis password is set** - Strong password generated and configured
- ✅ **Redis only binds to localhost** - `bind 127.0.0.1` in config
- ✅ **Protected mode enabled** - `protected-mode yes` in config
- ✅ **Password stored in .env** - Not committed to git (`.env` is in `.gitignore`)
- ✅ **Firewall configured** - UFW blocks external access to port 6379

### Firewall Configuration (Optional but Recommended)

If you're using UFW firewall, ensure Redis port is NOT exposed:

```bash
# Check firewall status
sudo ufw status

# Redis should NOT be in the allowed list
# If you see port 6379, remove it:
sudo ufw delete allow 6379/tcp
```

Redis should only be accessible from localhost (127.0.0.1) for security.

---

## Monitoring

### Check Redis Status
```bash
sudo systemctl status redis-server
```

### View Redis Logs
```bash
sudo tail -f /var/log/redis/redis-server.log
```

### Check Redis Memory Usage
```bash
redis-cli -a YOUR_PASSWORD INFO memory
```

### Monitor Redis Commands (Real-time)
```bash
redis-cli -a YOUR_PASSWORD MONITOR
```
Press `Ctrl + C` to stop monitoring.

### Check Redis Statistics
```bash
redis-cli -a YOUR_PASSWORD INFO stats
```

---

## Troubleshooting

### Issue: Redis won't start

**Check status:**
```bash
sudo systemctl status redis-server
```

**Check logs:**
```bash
sudo journalctl -u redis-server -n 50
```

**Common fixes:**
```bash
# Restart Redis
sudo systemctl restart redis-server

# Check configuration syntax
sudo redis-server /etc/redis/redis.conf --test-memory 1
```

### Issue: PHP can't connect to Redis

**1. Verify Redis is running:**
```bash
sudo systemctl status redis-server
```

**2. Check PHP Redis extension:**
```bash
php -m | grep redis
```
If not found, reinstall:
```bash
sudo apt-get install php8.2-redis -y
sudo systemctl restart php8.2-fpm
```

**3. Verify password in .env matches Redis config:**
```bash
# Check .env
cat .env | grep REDIS_PASSWORD

# Check Redis config
sudo grep requirepass /etc/redis/redis.conf
```
Passwords must match exactly.

**4. Test connection manually:**
```bash
redis-cli -a YOUR_PASSWORD ping
```
Should return `PONG`.

**5. Clear Laravel config cache:**
```bash
php artisan config:clear
php artisan config:cache
```

### Issue: Authentication failed

- Double-check password in `/etc/redis/redis.conf` matches `.env` file
- Ensure no extra spaces or quotes in password
- Restart Redis after password changes:
  ```bash
  sudo systemctl restart redis-server
  ```

### Issue: Permission denied

```bash
sudo chown redis:redis /var/lib/redis
sudo chmod 750 /var/lib/redis
sudo systemctl restart redis-server
```

### Issue: Connection refused

**Check if Redis is listening on correct port:**
```bash
sudo netstat -tlnp | grep 6379
```

**Check Redis bind address:**
```bash
sudo grep "^bind" /etc/redis/redis.conf
```
Should show: `bind 127.0.0.1`

---

## Performance Tuning (Optional)

For high-traffic sites, optimize Redis in `/etc/redis/redis.conf`:

```bash
sudo nano /etc/redis/redis.conf
```

**Add or modify these settings:**

```conf
# Set max memory (adjust based on your server RAM)
# Example: 256MB for small servers, 1GB for larger
maxmemory 256mb
maxmemory-policy allkeys-lru

# Enable persistence (saves data to disk)
save 900 1
save 300 10
save 60 10000
```

**Restart Redis:**
```bash
sudo systemctl restart redis-server
```

---

## What's Cached in This Application

### Frontend (Public Pages)
- **Homepage**: Featured products and categories (30 minutes)
- **Shop Page**: Products list when no filters applied (30 minutes)
- **Product Details**: Individual product pages (1 hour)
- **Deals Page**: Deal products (30 minutes)
- **Categories**: Category list and counts (1 hour)
- **Price Range**: Min/max prices (1 hour)
- **Recent Purchases**: Recent purchase notifications (10 minutes)

### Admin Panel
- **Dashboard Stats**: Total products, orders, revenue (5 minutes)
- **Financial Data**: Revenue, expenses, profit calculations (5 minutes)

### Automatic Cache Invalidation

Caches are automatically cleared when:
- Products are created, updated, or deleted
- Categories are created, updated, or deleted
- Orders are created, updated, or status changed
- Pre-orders are converted to active orders

---

## Quick Commands Reference

```bash
# Start Redis
sudo systemctl start redis-server

# Stop Redis
sudo systemctl stop redis-server

# Restart Redis
sudo systemctl restart redis-server

# Check Redis status
sudo systemctl status redis-server

# Connect to Redis CLI
redis-cli -a YOUR_PASSWORD

# Clear all Laravel cache
php artisan cache:clear

# Clear Laravel config cache
php artisan config:clear

# Cache Laravel config
php artisan config:cache

# View Redis info
redis-cli -a YOUR_PASSWORD INFO
```

---

## Support

If you encounter issues not covered here:

1. Check Redis logs: `sudo tail -f /var/log/redis/redis-server.log`
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Verify all steps were completed correctly
4. Ensure passwords match in both Redis config and Laravel .env

---

**Last Updated:** 2024
**For:** Laravel Application with Redis Caching on Digital Ocean
