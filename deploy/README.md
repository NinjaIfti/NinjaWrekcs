# Production deployment notes

Server: DigitalOcean droplet, `159.89.193.93`, app at `/var/www/NinjaWrekcs`.
Canonical origin: **`https://www.ninjawrecks.me`**.

## Do not undo: the http → https redirect

`nginx.conf` here mirrors `/etc/nginx/sites-available/NinjaWrekcs`.

The `www` host previously listened on **both** port 80 and 443 in a single server
block, serving the app over plain http as well as https:

```nginx
server {
    listen 80;
    listen 443 ssl http2;      # served the app on BOTH schemes
    server_name www.ninjawrecks.me;
```

Because `SESSION_SECURE_COOKIE` was unset, Laravel set the session cookie's
`Secure` flag based on the scheme of each individual request:

| Request | Session cookie |
| --- | --- |
| `http://www/...`  | `ninjawrecks-session=…; httponly` (no Secure) |
| `https://www/...` | `ninjawrecks-session=…; secure; httponly` |

Cookies are not isolated by scheme — both write the same cookie slot. So every
time a visitor crossed between http and https, the cookie was either withheld
(a Secure cookie is never sent over http) or overwritten, silently moving them
onto a **new session**. The CSRF token embedded in the page they were already
looking at then belonged to a dead session, producing an intermittent
**419 "Page Expired"** on submit.

It hit Instagram's in-app browser hardest, because it opens shared links over
plain http and nothing forced them onto https. Login, register and add-to-cart
were all affected, not just checkout.

**Any change that serves the app over plain http again reintroduces this bug.**

## Required production `.env` settings

These are not defaults and are easy to lose on a rebuild:

```env
APP_URL=https://www.ninjawrecks.me   # canonical host; a non-www value 301s and
                                     # would turn POSTs into GETs, dropping form data
SESSION_SECURE_COOKIE=true           # pins the Secure flag so it cannot flip per request
SESSION_LIFETIME=1440                # 24h, so a cart left open doesn't expire
MAIL_PORT=2525                       # see below
```

## Mail: port 2525, not 587

DigitalOcean blocks outbound SMTP on this droplet — ports 25, 465 and 587 all
time out, to any host (Gmail included), and it is not a local firewall (`ufw`
is inactive, iptables policy is ACCEPT). Brevo's alternate port **2525** is
open and is what the app uses.

## Applying nginx changes

```bash
# copy this file to the server, then:
nginx -t                 # must pass before reloading
systemctl reload nginx
```

Certbot uses the **nginx** authenticator (`/etc/letsencrypt/renewal/*.conf`),
which injects its own temporary challenge block, so the redirects do not
interfere with renewal. The explicit `/.well-known/acme-challenge/` location on
port 80 is a safety net.

## After deploying app code

```bash
cd /var/www/NinjaWrekcs
git pull origin main
php artisan config:cache
php artisan route:cache
php artisan view:cache
chown -R www-data:www-data storage/framework bootstrap/cache
```
