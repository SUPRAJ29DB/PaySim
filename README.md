# PaySim

PaySim is a **local payment and wallet simulator** for learning and demonstration. It is not connected to NPCI, UPI rails, banks, or real payment settlement. All balances and transactions are simulated.

## Requirements

- PHP 8.1 or newer
- MySQL/MariaDB with `pdo_mysql`, or SQLite with `pdo_sqlite`
- Apache (for the supplied `.htaccess`) or another PHP-capable web server

## Setup (XAMPP)

1. Copy this folder to `C:\\xampp\\htdocs\\PaySim`.
2. Start Apache and MySQL in XAMPP.
3. In phpMyAdmin, create a database named `paysim` and import `sql/paysim.sql`. Import `sql/seed.sql` only if you want the project's sample records; inspect it before use.
4. Configure `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, and `DB_DRIVER=mysql` as PHP/server environment variables. `.env.example` is a reference; the app reads variables through `getenv()` and does not automatically parse `.env`. The app will fail clearly if MySQL is unavailable; it will not silently switch to SQLite or create databases automatically.
5. Visit `http://localhost/PaySim/`.

## Administrator account setup

No default administrator account or master password is seeded. Create an administrator only after configuring the database. Generate a password hash with PHP:

```bash
php -r "echo password_hash('REPLACE_WITH_A_LONG_UNIQUE_PASSWORD', PASSWORD_DEFAULT), PHP_EOL;"
```

Insert an administrator user row into `users` with that hash, a unique username/email/phone/UPI ID, and `role` set to `admin` or `superadmin`, `status` set to `active`. Do not commit the plaintext password or hash into source control. The admin login accepts only an active database user with an administrator role.

## Tests

Run the project test runner from the project root:

```bash
php tests/run-all-tests.php
```

The required PDO driver must be enabled for the configured database.

## Security note

This codebase is a learning simulator, not production payment software. Before exposing it to a network, review authentication, authorization, CSRF protection, session handling, payment consistency, error handling, and secrets management. Never use real bank credentials, real payment data, or real funds.
