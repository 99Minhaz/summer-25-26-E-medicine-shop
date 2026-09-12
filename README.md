# E-Medical Shop

PHP MVC application for an E-Medical Shop with admin and customer roles.

## Setup

1. Start Apache and MySQL from XAMPP.
2. Copy this whole folder into `htdocs` (rename the folder if you like, e.g. `E-Medical-Shop`).
3. Open `http://localhost/<your-folder-name>/database_setup.php` once to create the database, tables, and sample data.
4. Open the app at:

```text
http://localhost/<your-folder-name>/
```

You can still import `schema.sql` manually if your teacher asks for the SQL file.

## Default Admin

```text
Email: admin@example.com
Password: admin12345
```

## Structure

- `controllers/` handles requests and role gates.
- `models/` contains beginner-style `mysqli` functions with prepared statements.
- `views/` contains PHP templates.
- `config/` contains app and database configuration.
- `uploads/` stores profile and medicine images (created automatically on first upload).
- `assets/` contains CSS and JavaScript.

## AJAX Endpoints

- `GET /api/medicines/search?q=&vendor=&genre=`
- `POST /api/cart/add`
- `POST /api/cart/update`
- `POST /api/cart/remove`
- `POST /api/orders/status`
