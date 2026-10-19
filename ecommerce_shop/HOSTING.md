# Ecommerce hosting setup

The storefront, customer accounts, product administration, and order API use
the same MySQL database. The app connects directly to the configured database
and does not try to create a database on the hosting server.

## InfinityFree database

Use the MySQL hostname from the InfinityFree account panel; it is not the
website domain. Copy `config/.env.example` to `config/.env`, then enter the
database password and any other private credentials in `config/.env`. This file
is git-ignored, and direct HTTP access to `config/` is blocked by `.htaccess`.
Environment variables supplied by the hosting platform take precedence.

| Setting | InfinityFree value |
| --- | --- |
| `ECOMMERCE_DB_HOST` | `sql203.infinityfree.com` |
| `ECOMMERCE_DB_PORT` | `3306` |
| `ECOMMERCE_DB_NAME` | `if0_43216368_ecommerceusers` |
| `ECOMMERCE_DB_USER` | `if0_43216368` |
| `ECOMMERCE_DB_PASSWORD` | Set only in the private `.env` or hosting environment |

Customer accounts and password recovery use `users`; catalog reads and product
administration use `products`; checkout and order history use `shop_orders`.
These features, including legacy product-fetch and product-upload endpoints,
all use the same configured database. Existing tables are not moved or renamed.
The hosting database must already exist, and its user needs permission to create
or alter application tables. Back up existing data before deploying.

The current app expects `users` to include `id`, `username`, `pwd`, `email`, and
`created_at`; `products` to include `id`, `product_name`, `image_path`,
`rating_stars`, `rating_count`, `price_cents`, `keywords`, and `is_sold_out`;
and `shop_orders` to match the order fields defined in
`admin/database.php`. The app can add `products.is_sold_out` if it is missing,
but `CREATE TABLE IF NOT EXISTS` does not migrate other existing columns.
The legacy `orders` and `order_items` tables in the hosting database are not
used by the current checkout/order API. Compare the actual table structures in
phpMyAdmin before deploying; the table-name listing alone cannot confirm schema
compatibility.

Without hosting values or a private `.env` file, connections default to the
local `ecommerceusers` database.

## Hosting environment variables

| Variable | Hosting value |
| --- | --- |
| `ECOMMERCE_MAIL_FROM` | `emmanuellokumu93@gmail.com` |
| `ECOMMERCE_SMTP_HOST` | `smtp.gmail.com` |
| `ECOMMERCE_SMTP_PORT` | `587` |
| `ECOMMERCE_SMTP_SECURITY` | `tls` |
| `ECOMMERCE_SMTP_USERNAME` | `emmanuellokumu93@gmail.com` |
| `ECOMMERCE_SMTP_PASSWORD` | A Gmail App Password, configured privately; never use the normal account password |

Before exposing the admin panel, set `ECOMMERCE_ADMIN_USERNAME` and
`ECOMMERCE_ADMIN_PASSWORD_HASH` in `config/.env` or hosting configuration.
Generate the password hash locally with PHP's `password_hash()`; the admin login
has no built-in password fallback.

Password recovery requires authenticated SMTP with TLS. The app permits Gmail
STARTTLS on port `587` or implicit TLS on port `465`; it never falls back to
unauthenticated `mail()` or unencrypted port `25`. Enable two-step verification
for the sender account, create an App Password in Google Account security
settings, and store it as `ECOMMERCE_SMTP_PASSWORD`. Never place the App Password
in source control. Verify that the host allows outbound connections to Gmail
SMTP on the selected TLS port; if it does not, use a supported authenticated
SMTP provider.

Install the locked mail dependency before deployment with
`composer install --no-dev --optimize-autoloader` from this directory, then
upload the resulting `vendor` directory along with the application files if the
host does not provide Composer.
