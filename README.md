# AutoEase PHP Backend

This backend is ready to upload to ProFreeHost inside `htdocs`.

## ProFreeHost deploy checklist

1. Upload all project files to `htdocs`, including `index.php`, `.htaccess`, `controller`, `routes`, `headers`, `includes`, `uploads`, and `vendor`.
2. In ProFreeHost, select PHP 8.0 or newer.
3. Copy `.env.example` to `.env` on the server.
4. Fill `.env` with the MySQL values from ProFreeHost Control Panel > MySQL Databases:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
5. Set `API_URL`, `CORS_ALLOWED_ORIGINS`, and `FRONTEND_URL` to your deployed React/frontend URL.
6. Import your local AutoEase database into the ProFreeHost database using phpMyAdmin.
7. Test the backend root URL. It should return:

```json
{"success":true,"message":"AutoEase API is running"}
```

## Clean API paths

The root `index.php` supports clean routes such as:

- `/auth/login`
- `/auth/register`
- `/auth/profile`
- `/cars`
- `/cars/filter`
- `/cars/details`
- `/cart/add`
- `/orders/place`
- `/admin/orders`
- `/installments`
- `/payment-intents/checkout`

The old direct PHP file URLs still work too, for example `/routes/cars/cars.php`.

Server php -S localhost:8000
