# Login System (PHP + MySQL + Bootstrap 5)

Login, registration, and a role-aware home page (user / admin) backed by MySQL.

## Setup (XAMPP)
1. Copy this folder into `C:\xampp\htdocs\login-app`.
2. Start **Apache** and **MySQL** in the XAMPP control panel.
3. Open phpMyAdmin → **Import** → choose `database.sql` → Go.
4. Visit http://localhost/login-app/

## Demo accounts
| Username | Password   | Role  |
|----------|------------|-------|
| admin    | Admin@123  | admin |
| juan     | User@1234  | user  |

New registrations always get the `user` role.

## Structure
- `index.php` – login · `register.php` – sign up · `home.php` – dashboard (admin sees user table) · `logout.php`
- `config/db.php` – DB credentials · `includes/auth.php` – session, CSRF, helpers
- `assets/` – custom CSS/JS on top of Bootstrap (loaded via CDN, internet needed)

## Security notes
Prepared statements, `password_hash`/`password_verify`, session ID regeneration on login, CSRF tokens, output escaping.
