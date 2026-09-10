# Deploying MedBandhu to Hostinger (Business shared hosting)

This is the pilot setup. Move to a VPS when the **second (paying) hospital** signs up — see
"Moving to a VPS" at the bottom.

The app already ships with settings that suit shared hosting:
`SESSION_DRIVER=database`, `CACHE_STORE=database`, `QUEUE_CONNECTION=database` (no Redis needed),
and `trustProxies` + forced HTTPS in production are wired in.

---

## 0. One-time: put the code in Git

Shared hosting has no reliable Node, so **assets are built locally and committed**.

```bash
cd C:/MyWebStack/www/hms
git init
git add .
git commit -m "Initial deploy"
# create a PRIVATE repo on github.com, then:
git remote add origin git@github.com:<you>/medbandhu.git
git branch -M main
git push -u origin main
```

Before every deploy, build assets locally and commit them (they are normally git-ignored):

```bash
npm run build
git add -f public/build
git commit -m "Build assets"
git push
```

---

## 1. Hostinger hPanel setup

1. **Domain** – add `medbandhu.com` (and it will also cover `www`). Note its folder:
   `~/domains/medbandhu.com/`.
2. **PHP** – *Advanced → PHP Configuration* → set **PHP 8.3**. Enable extensions:
   `bcmath ctype curl dom fileinfo gd intl mbstring openssl pdo_mysql tokenizer xml zip`.
3. **MySQL** – *Databases → MySQL Databases* → create a database + user (Hostinger names them
   like `u1234_medbandhu` / `u1234_mb`). Save the password.
4. **SSH** – *Advanced → SSH Access* → enable, note host/port/username.
5. **SSL** – *Security → SSL* → install the free certificate for `medbandhu.com`. Turn on
   **Force HTTPS**.

---

## 2. First deploy (over SSH)

```bash
ssh -p <port> <user>@<host>

cd ~/domains/medbandhu.com

# Laravel must serve from /public, not public_html
rm -rf public_html
git clone https://github.com/<you>/medbandhu.git app
ln -s ~/domains/medbandhu.com/app/public ~/domains/medbandhu.com/public_html

cd app
composer install --no-dev --optimize-autoloader

cp .env.example .env
php artisan key:generate
```

Edit `.env` (use `nano .env`):

```dotenv
APP_NAME="MedBandhu"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://medbandhu.com

APP_LOCALE=en

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=u1234_medbandhu
DB_USERNAME=u1234_mb
DB_PASSWORD=<the password>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database

SESSION_SECURE_COOKIE=true

DEMO_MODE=true
DEMO_EMAIL=demo@medbandhu.test

MEDBANDHU_PHONE="+91 7667043372"
MEDBANDHU_WHATSAPP="917667043372"
MEDBANDHU_EMAIL="hello@medbandhu.com"
MEDBANDHU_ADDRESS="Ranchi, Jharkhand, India"

# Email — REQUIRED for payment receipts + activation emails to actually send.
# Create the hello@medbandhu.com mailbox in hPanel, then paste its password below.
MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=hello@medbandhu.com
MAIL_PASSWORD=<paste the mailbox password>
MAIL_SCHEME=smtps
MAIL_FROM_ADDRESS="hello@medbandhu.com"
MAIL_FROM_NAME="MedBandhu"

# Manual payment flow — shown to hospitals on their billing page.
MEDBANDHU_PAY_UPI="7667043372@axisbank"
MEDBANDHU_PAY_AC_NAME="MedBandhu"
MEDBANDHU_PAY_BANK="Axis Bank, Ranchi"
MEDBANDHU_PAY_AC_NUMBER="39998363584"
MEDBANDHU_PAY_IFSC="UTIB0000837"

# Razorpay — leave blank until you have live keys (online payments stay disabled)
RAZORPAY_KEY_ID=
RAZORPAY_KEY_SECRET=
RAZORPAY_WEBHOOK_SECRET=
```

Then:

```bash
php artisan migrate --force
php artisan db:seed --class=Database\\Seeders\\DemoPlansSeeder --force   # loads the public plans (+ demo hospital)
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Create the **platform super admin** (has no hospital):

```bash
php artisan tinker
>>> $u = \App\Models\User::create(['name'=>'You','email'=>'you@medbandhu.com','password'=>'<strong-password>','is_active'=>true]);
>>> setPermissionsTeamId(config('hms.platform_team_id'));
>>> $u->assignRole('Super Admin');   // create the role first if needed: see hms:sync-roles
>>> exit
```

(If `Super Admin` doesn't exist yet, run the platform provisioner path once — creating the first
hospital from `/platform/hospitals` also syncs roles. Easiest: create a throwaway hospital, then
attach yourself as Super Admin, or run `php artisan hms:sync-roles`.)

---

## 3. Cron (the scheduler)

*Advanced → Cron Jobs* → add, every minute:

```
/usr/bin/php ~/domains/medbandhu.com/app/artisan schedule:run >> /dev/null 2>&1
```

This drives `hms:refresh-subscriptions` (01:00) and `hms:reset-demo` (03:00).

---

## 4. Every later deploy

```bash
# locally
npm run build && git add -f public/build && git commit -m "deploy" && git push

# on the server
cd ~/domains/medbandhu.com/app
php artisan down
git pull
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan up
```

---

## 5. After go-live

- **Google Search Console** + **Bing Webmaster Tools** → add `medbandhu.com`, submit
  `https://medbandhu.com/sitemap.xml`.
- Test the share card: paste `https://medbandhu.com` into
  [OpenGraph.xyz](https://www.opengraph.xyz/) and
  [Google Rich Results Test](https://search.google.com/test/rich-results).
- Turn on **daily backups** in hPanel (paid add-on) — this is patient data. Or add a cron that
  `mysqldump`s to `~/backups/` daily and download weekly.
- Onboard a hospital from `/platform/hospitals` → *Add hospital*. Pick an **Activation**:
  - *14-day free trial* — access starts now.
  - *Hold for payment* — raises the first invoice and locks the hospital to its billing page
    until you record payment. Use this for paying customers.
  - *Activate now* — for a hospital that has already paid you offline.
- **Manual payment flow** (until an online subscription gateway is live):
  1. Hospital sees the amount + your UPI / bank details on their *Subscription & billing* page and
     a "I've paid — notify us on WhatsApp" button.
  2. They pay by UPI/NEFT and WhatsApp you the reference.
  3. You open the invoice in `/platform/billing/invoices`, hit **Record payment** (method + reference).
  4. That renews the subscription, unlocks the hospital, and **emails them a receipt** (needs SMTP,
     above). Receipts are also viewable/printable from the invoice page.

---

## Moving to a VPS (at hospital #2)

1. Hostinger **KVM 2** (8 GB), Ubuntu 24.04, Mumbai region, + automated backups.
2. Install a panel — **CloudPanel** (free, 1-click on Hostinger) or **Laravel Forge** ($12/mo).
3. `mysqldump` the pilot DB on shared → import on the VPS.
4. Deploy the repo the same way (panel handles nginx + SSL + the `/public` root + the scheduler).
5. Repoint DNS (`medbandhu.com` A record → VPS IP), keep the old box up 24 h as fallback.
6. On the VPS you can then switch `CACHE_STORE`/`SESSION_DRIVER`/`QUEUE_CONNECTION` to `redis`
   and run `queue:work` under Supervisor.
