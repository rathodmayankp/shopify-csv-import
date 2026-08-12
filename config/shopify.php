<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Shopify Store URL
    |--------------------------------------------------------------------------
    |
    | The *.myshopify.com domain of the store, without the protocol.
    | e.g. laravel-import-test.myshopify.com
    |
    */
    'store_url' => env('SHOPIFY_STORE_URL'),

    /*
    |--------------------------------------------------------------------------
    | Admin API Access Token
    |--------------------------------------------------------------------------
    |
    | The private app / custom app access token (starts with shpat_).
    |
    */
    'access_token' => env('SHOPIFY_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Admin API Version
    |--------------------------------------------------------------------------
    */
    'api_version' => env('SHOPIFY_API_VERSION', '2024-10'),

    /*
    |--------------------------------------------------------------------------
    | Default Collection ID
    |--------------------------------------------------------------------------
    |
    | Every imported product gets added to this collection.
    |
    */
    'collection_id' => env('SHOPIFY_COLLECTION_ID'),

    /*
    |--------------------------------------------------------------------------
    | Request timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => env('SHOPIFY_TIMEOUT', 20),

];
