# SokoFlow Shop POS

A mobile-friendly PHP and MySQL point-of-sale app for one small shop. It includes owner and cashier accounts, product and stock management, cash/M-Pesa/credit sales, receipts, a daily report, and a customer credit book.

## Run locally with XAMPP

1. Start Apache and MySQL in the XAMPP Control Panel.
2. Open phpMyAdmin, select **Import**, and import `schema.sql`. This creates the `sokoflow` database and its tables.
3. Copy `config/config.local.example.php` to `config/config.local.php` and enter your local MySQL details. This per-machine file overrides environment variables and is excluded from Git. The XAMPP example uses `localhost`, `sokoflow`, `root`, and a blank password only in the explicitly local environment.
4. Open `http://localhost/shop%20management%20system/`. Create the owner and cashier accounts on the first-run screen.

Requires PHP 8.1+ with PDO, PDO MySQL, and mbstring enabled. The app does not ship with default account passwords. The tracked `config/config.php` reads `SHOP_ENV` and `SHOP_DB_*` variables, merges the optional ignored `config/config.local.php`, and throws a clear error if required settings are missing. Production requires a non-empty database password and never falls back to root or local defaults. `.htaccess` denies web access to the config directory; for Nginx, add equivalent server rules or store the local config outside the document root.

Open `hosting.php` in the app for the database setup, shared-hosting requirements, configuration steps, troubleshooting, and security checklist.

## Database and login passwords

- Database connection details belong in `config/config.local.php` or the hosting environment. On shared hosting, enter the database host, name, username, and password provided by the host.
- Owner and cashier login passwords are different from the database password. They are stored as password hashes in the `users` table; the app does not include an in-app password reset or change screen.
- Changing the database password in config changes only how the app connects to MySQL. It does not change owner or cashier login credentials.

## Included workflows

- Owners can add, edit, and remove products, and view reports. Cashiers can record sales and customer credit payments.
- Each sale checks and decrements stock in a database transaction. Sale lines keep the item name and buying-price snapshot for historical receipts and profit estimates.
- Receipts can be printed from a regular browser or shared using the device's share feature.
- The report date picker shows sales, cash and M-Pesa collections, estimated profit, recent transactions, and items at or below their low-stock threshold.
- Credit balances support partial payments recorded as cash or M-Pesa.
