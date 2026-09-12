# E-Medicine Shop (PHP + MySQL, MVC)

A 4-role online pharmacy platform: **Customer, Admin, Vendor, Delivery**. Written in plain PHP with procedural `mysqli` and prepared statements. No frameworks, no Composer, no build step. Copy it into XAMPP and it runs.

## 1. Install (XAMPP)

1. Copy the `EMedicineShop` folder into `C:\xampp\htdocs\wbt\`, so it becomes `htdocs/wbt/EMedicineShop/`.
2. Start Apache and MySQL in the XAMPP control panel.
3. Open `http://localhost/wbt/EMedicineShop/database_setup.php` once — this creates the database, all tables, the stored procedures, and sample data.
4. Open `http://localhost/wbt/EMedicineShop/`.

The default Admin, Vendor, and Delivery accounts are created automatically the first time `database_setup.php` runs (see **Test accounts** below). Customers sign up on the register page.

If your MySQL uses a password, change `$password` in `config/database.php`.

## 2. Folder structure

```
EMedicineShop/
├── index.php            Front controller: the ONLY entry point (router)
├── database_setup.php   Creates DB, tables, stored procedures, sample data
├── schema.sql            Same schema, importable manually via phpMyAdmin
├── .htaccess             Rewrites every request to index.php
│
├── config/
│   ├── app.php           App name, upload limit, session/timezone bootstrap
│   └── database.php      DB connection + db_select() / db_run() / db_call()
│
├── helpers/
│   └── helpers.php       url(), asset(), csrf, flash, auth, view() loader
│
├── models/                M — every SQL query lives here
│   ├── User.php           Auth, profile, customer + role lookups
│   ├── Category.php       Category CRUD (create routed through a stored procedure)
│   ├── Medicine.php       Medicine CRUD, vendor-scoped queries, search
│   ├── Cart.php           Cart items scoped to the logged-in customer
│   └── Order.php          Orders, order items, delivery assignment, status
│
├── controllers/            C — request handling, validation, role gates
│   ├── AuthController.php       Login / register / logout
│   ├── ProfileController.php    View/edit profile, change password
│   ├── HomeController.php       Public catalog browsing
│   ├── AdminController.php      Categories, medicines, customers, orders, dashboard
│   ├── VendorController.php     Vendor's own medicine CRUD
│   ├── DeliveryController.php   Assigned orders, mark delivered
│   ├── CartController.php       Cart page
│   ├── OrderController.php      Checkout, payment, order success
│   └── ApiController.php        AJAX/JSON endpoints (search, cart, order status)
│
├── views/                  V — HTML templates only
│   ├── layout/main.php     Shared header/nav/footer
│   ├── auth/                Login, register
│   ├── home/                 Catalog + medicine card partial
│   ├── admin/                 Admin panel pages
│   ├── vendor/                 Vendor dashboard + medicine form
│   ├── delivery/                 Delivery dashboard
│   ├── cart/                      Cart, checkout
│   ├── orders/                     Payment, order success
│   └── errors/                      404 / setup error pages
│
└── assets/
    ├── css/styles.css       All styling
    └── js/app.js            Client-side validation + AJAX (cart, search, order status)
```

The MVC rule used throughout: **a view never runs a query, and a model never prints HTML.** The controller sits in the middle: it reads `$_POST`, validates, calls the model, then `require`s the view.

## 3. How the router works

Every request goes through `index.php`. `route_request()` matches the URL against a list of routes and calls the matching controller function. There is no `.php` in any URL — `.htaccess` rewrites everything to `index.php` first.

| URL | What happens |
|---|---|
| `/login` | Login page |
| `/register` | Signup page (Customer, Vendor, or Delivery only) |
| `/admin` | Admin dashboard (stats via stored procedure) |
| `/admin/categories`, `/admin/medicines` | Catalog CRUD |
| `/admin/orders` | Accept / reject orders, assign a delivery person |
| `/vendor/medicines` | Vendor's own medicine CRUD |
| `/delivery` | Delivery person's assigned orders |
| `/cart`, `/checkout`, `/payment` | Cart → checkout → payment flow |
| `/api/medicines/search` | Live search, called by `app.js` |
| `/api/cart/add`, `/api/cart/update`, `/api/cart/remove` | AJAX cart actions |
| `/api/orders/status` | AJAX accept/reject/deliver |

`index.php` loads `config/` → `helpers/` → `models/` → `controllers/`, checks a remembered login cookie, then dispatches to one function. `require_role('admin')` (etc.) blocks anyone in the wrong role before the controller body runs.

## 4. The four roles

Each role manages its own table end-to-end (Create, Read, Update, Delete, Search) from its own dashboard. No feature appears on two roles' dashboards.

| Role | Manages (CRUD) | Feature 1 | Feature 2 | Feature 3 |
|---|---|---|---|---|
| **Customer** | Own cart & orders | Browse and search the catalog by name, vendor, or category | Add/update/remove items in the cart | Checkout, choose a payment method, and track order status |
| **Admin** | Categories, the full medicine catalog, customers, orders | Full CRUD on categories and medicines | Accept/reject incoming orders and assign a delivery person | Dashboard stats served by a MySQL stored procedure |
| **Vendor** | Their own medicines | Add / edit / delete medicines they supply | View their own stock levels | Newly added medicines appear in the public catalog immediately |
| **Delivery** | Orders assigned to them | View the list of orders assigned by Admin | See the customer's address and phone for each assigned order | Mark an assigned order as delivered |

## 5. How the roles connect

- A **customer** places an order → it starts as `pending`.
- The **admin** opens Purchase Requests and clicks **Accept** → an "Assign to..." dropdown appears, and the admin picks a **delivery** person.
- The **delivery** person sees the order on their dashboard → clicks **Mark Delivered** once it's handed over.
- A **vendor** adds a medicine from their dashboard → it shows up instantly in the admin's medicine list and the public home page, no separate approval step.

## 6. Requirement checklist

| Requirement | Where to look |
|---|---|
| MVC | `models/`, `controllers/`, `views/`, all routed by `index.php` |
| DB (MySQL procedural) | `sp_dashboard_stats()`, `sp_category_create()` in `schema.sql` / `database_setup.php`, called via `db_call()` in `config/database.php` |
| Auth (session + cookie) | `controllers/AuthController.php` (login/register), `helpers/helpers.php` (session helpers), `remember_tokens` table for "remember me" |
| PHP validation | `validate_registration()`, `validate_medicine()`, `validate_category()` in each controller |
| JS validation | `validators` object in `assets/js/app.js`, runs on `submit` before the server is hit |
| AJAX / JSON | `controllers/ApiController.php`, called from `assets/js/app.js` (`fetch`) |
| UI (HTML/CSS) | `views/`, `assets/css/styles.css` |
| Basic web security | see section 7 |
| Feature completeness | CRUD + search, 3+ distinct features per role |

## 7. Security, and why each piece is there

| Risk | Defence | File |
|---|---|---|
| SQL injection | Prepared statements everywhere — user input is never glued into SQL strings | all of `models/` |
| Stolen passwords | `password_hash()` on register, `password_verify()` on login | `models/User.php` |
| XSS | `e()` (escapes every value) wraps anything printed into HTML | `helpers/helpers.php`, all views |
| CSRF | A hidden token (`csrf_field()` / `verify_csrf()`) on every POST form | `helpers/helpers.php`, all forms |
| Wrong role accessing a page | `require_role()` runs before the controller body, on every protected route | every controller |
| Public admin sign-up | The register form only accepts Customer, Vendor, or Delivery; the server re-checks this even if the form is edited | `controllers/AuthController.php` |
| One customer/vendor viewing another's data (IDOR) | Cart, order, and vendor-medicine queries are scoped with `WHERE user_id = ?` / `WHERE vendor_id = ?` | `models/Cart.php`, `models/Order.php`, `models/Medicine.php` |
| "Remember me" leaking a reusable password | Stored as a random selector + hashed token pair, not the password itself | `remember_tokens` table, `models/User.php` |

## 8. Settings you can change

All in `config/app.php`:

```php
$app_name = "E-Medical Shop";              // shown in the title bar and header
define("UPLOAD_MAX_BYTES", 2 * 1024 * 1024); // max medicine/profile image size
```

## 9. Test accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@example.com` | `admin12345` |
| Vendor | `vendor@example.com` | `admin12345` |
| Delivery | `delivery@example.com` | `admin12345` |
| Customer | sign up on the register page | — |

Nobody can sign up as Admin — the register page only offers Customer, Vendor, and Delivery, and the server checks that list again even if the form is tampered with. New admins would have to be added directly in the database.

---

*E-Medicine Shop — CSC 3215: Web Technologies, American International University-Bangladesh.*
