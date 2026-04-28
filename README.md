# AutoEase PHP Backend

## Local server

```bash
php -S localhost:8000
```

## ProFreeHost deploy

Upload the project contents to your domain's `htdocs` folder.

Required uploaded files/folders:

- `index.php`
- `.htaccess`
- `app_config.php`
- `config_db.php`
- `controller/`
- `headers/`
- `includes/`
- `routes/`
- `uploads/`
- `vendor/`

Create `app_config.php` from `app_config.example.php` and fill in your ProFreeHost MySQL credentials.

Test after upload:

```text
https://your-domain/health
https://your-domain/debug/db?debug=1
https://your-domain/cars
```

Remove or disable debug access after setup.
