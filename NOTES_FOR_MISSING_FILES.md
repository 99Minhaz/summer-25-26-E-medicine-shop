# Missing files (not yet provided)

These two files are referenced by the code but were not part of the uploaded set.
The app will not run in a browser until they are added.

1. `views/layout/main.php` — wraps every page with the HTML shell (head, header/nav, footer).
   Referenced in `helpers/helpers.php` -> `view()`.

2. `views/layout/_error.php` — small partial that prints a field-level validation error.
   Referenced throughout the form views (e.g. `$name = 'name'; require BASE_PATH . '/views/layout/_error.php';`).
   Expected to read a `$name` variable and print `$errors[$name]` if set.

Everything else (controllers, models, views, config, helpers, public assets, schema)
is included as uploaded, organized into the standard MVC folder structure.
