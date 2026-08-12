<?php

namespace App\Services;

use App\Exceptions\ShopifyApiException;
use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShopifyService
{
    protected string $baseUrl;

    protected ?string $primaryLocationId = null;

    public function __construct()
    {
        $storeUrl = rtrim((string) config('shopify.store_url'), '/');
        $version = config('shopify.api_version');

        $this->baseUrl = "https://{$storeUrl}/admin/api/{$version}";
    }

    /**
     * Create a product in Shopify from a local Product record.
     *
     * @return string the Shopify product ID
     *
     * @throws ShopifyApiException
     */
    public function createProduct(Product $product): string
    {
        $payload = [
            'product' => [
                'title' => $product->title,
                'body_html' => $product->body_html,
                'vendor' => $product->vendor,
                'product_type' => $product->product_type,
                'tags' => $product->tags,
                'status' => $product->published ? 'active' : 'draft',
                'variants' => [
                    [
                        'sku' => $product->sku,
                        'price' => $product->price,
                        'compare_at_price' => $product->compare_at_price,
                        'weight' => $product->weight,
                        'weight_unit' => $product->weight_unit ?: 'kg',
                        'requires_shipping' => true,
                        'taxable' => true,
                        'inventory_management' => 'shopify',
                        'inventory_policy' => 'deny',
                    ],
                ],
            ],
        ];

        if ($product->image_src) {
            $payload['product']['images'] = [[
                'src' => $product->image_src,
                'alt' => $product->image_alt,
            ]];
        }

        $response = $this->client()->post("{$this->baseUrl}/products.json", $payload);

        if ($response->failed()) {
            throw new ShopifyApiException(
                "Shopify rejected the product create request (HTTP {$response->status()}).",
                $response->json() ?? [],
                $response->status()
            );
        }

        $shopifyProduct = $response->json('product');

        if (! $shopifyProduct || ! isset($shopifyProduct['id'])) {
            throw new ShopifyApiException('Shopify returned an unexpected response with no product ID.', $response->json() ?? []);
        }

        // Set the initial inventory quantity for the created variant, if provided.
        if ($product->inventory_qty > 0) {
            $this->setVariantInventory($shopifyProduct, $product->inventory_qty);
        }

        return (string) $shopifyProduct['id'];
    }

    /**
     * Add a product to a Shopify collection via the collects endpoint.
     *
     * @throws ShopifyApiException
     */
    public function addProductToCollection(string $shopifyProductId, string $collectionId): void
    {
        $response = $this->client()->post("{$this->baseUrl}/collects.json", [
            'collect' => [
                'product_id' => $shopifyProductId,
                'collection_id' => $collectionId,
            ],
        ]);

        if ($response->failed()) {
            throw new ShopifyApiException(
                "Failed to add product {$shopifyProductId} to collection {$collectionId} (HTTP {$response->status()}).",
                $response->json() ?? [],
                $response->status()
            );
        }
    }

    /**
     * Shopify (API 2021-01+) requires inventory to be set per-location via the
     * inventory_levels endpoint rather than directly on the variant payload.
     */
    protected function setVariantInventory(array $shopifyProduct, int $quantity): void
    {
        try {
            $inventoryItemId = $shopifyProduct['variants'][0]['inventory_item_id'] ?? null;
            $locationId = $this->primaryLocationId();

            if (! $inventoryItemId || ! $locationId) {
                return;
            }

            $this->client()->post("{$this->baseUrl}/inventory_levels/set.json", [
                'location_id' => $locationId,
                'inventory_item_id' => $inventoryItemId,
                'available' => $quantity,
            ]);
        } catch (\Throwable $e) {
            // Inventory sync failing shouldn't fail the whole product import -
            // the product already exists in Shopify at this point. Just log it.
            Log::warning('Shopify inventory sync failed', [
                'product_id' => $shopifyProduct['id'] ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function primaryLocationId(): ?string
    {
        if ($this->primaryLocationId !== null) {
            return $this->primaryLocationId;
        }

        $response = $this->client()->get("{$this->baseUrl}/locations.json");

        if ($response->failed()) {
            return null;
        }

        $locations = $response->json('locations') ?? [];

        return $this->primaryLocationId = isset($locations[0]['id']) ? (string) $locations[0]['id'] : null;
    }

    protected function client()
    {
        return Http::withHeaders([
            'X-Shopify-Access-Token' => config('shopify.access_token'),
            'Content-Type' => 'application/json',
        ])->timeout((int) config('shopify.timeout', 20));
    }
}
