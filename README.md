# shopify-csv-import

 

## Setup

```
composer install
cp .env.example .env
php artisan key:generate
```

Set your database in `.env` (Set DB_CONNECTION=mysql):

```
php artisan migrate
```

Add your Shopify credentials in `.env`:

```
SHOPIFY_STORE_URL=your-store.myshopify.com (without https://)
SHOPIFY_ACCESS_TOKEN=shpat_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
SHOPIFY_API_VERSION=2024-10
SHOPIFY_COLLECTION_ID=your_collection_id
```

## Run

Terminal 1: 
```
mkdir storage\framework
mkdir storage\framework\cache
mkdir storage\framework\sessions
mkdir storage\framework\views
mkdir storage\logs

php artisan optimize:clear
php artisan serve
```

Terminal 2: 
```
php artisan queue:work
```

Open: http://127.0.0.1:8000