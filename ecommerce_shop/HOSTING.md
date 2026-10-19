# Ecommerce hosting setup

The storefront, customer accounts, product administration, and order API now use
the same MySQL database when `ECOMMERCE_DB_NAME` is set. The app connects to the
database directly and does not try to create a database on the hosting server.

Configure these PHP/server environment variables in the hosting control panel:

| Variable | Hosting value |
| --- | --- |
| `ECOMMERCE_DB_HOST` | `sql203.infinityfree.com` |
| `ECOMMERCE_DB_PORT` | `3306` |
| `ECOMMERCE_DB_NAME` | `if0_43216368_ecommerceusers` |
| `ECOMMERCE_DB_USER` | `if0_43216368` |
| `ECOMMERCE_DB_PASSWORD` | Set the database password privately in hosting configuration; do not commit it |
| `ECOMMERCE_MAIL_FROM` | `emmanuellokumu93@gmail.com` |
| `ECOMMERCE_SMTP_HOST` | `smtp.gmail.com` |
| `ECOMMERCE_SMTP_PORT` | `587` |
| `ECOMMERCE_SMTP_SECURITY` | `tls` |
| `ECOMMERCE_SMTP_USERNAME` | `emmanuellokumu93@gmail.com` |
| `ECOMMERCE_SMTP_PASSWORD` | A Gmail App Password, configured privately; never use the normal account password |

The local XAMPP defaults remain available when these variables are unset. On the
host, the database account needs permission to create or alter the application's
tables. Back up the existing database before deploying.

Before exposing the admin panel, set `ECOMMERCE_ADMIN_USERNAME` and
`ECOMMERCE_ADMIN_PASSWORD_HASH` in hosting configuration to replace the
development defaults.

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
