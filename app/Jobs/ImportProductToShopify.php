<?php

namespace App\Jobs;

use App\Exceptions\ShopifyApiException;
use App\Models\ErrorLog;
use App\Models\Product;
use App\Models\Upload;
use App\Services\ShopifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportProductToShopify implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 30, 60];

    public function __construct(public Product $product)
    {
    }

    public function handle(ShopifyService $shopify): void
    {
        $this->product->update(['status' => Product::STATUS_PROCESSING]);

        try {
            $shopifyProductId = $shopify->createProduct($this->product);

            $collectionId = config('shopify.collection_id');

            if ($collectionId) {
                $shopify->addProductToCollection($shopifyProductId, $collectionId);
            }

            $this->product->update([
                'status' => Product::STATUS_SUCCESS,
                'shopify_product_id' => $shopifyProductId,
                'error_message' => null,
            ]);

            $this->finalizeRow(success: true);

            Log::info('Product imported to Shopify', [
                'product_id' => $this->product->id,
                'shopify_product_id' => $shopifyProductId,
            ]);
        } catch (ShopifyApiException $e) {
            $this->handleFailure($e->getMessage(), $e->responseBody());
        } catch (\Throwable $e) {
            $this->handleFailure($e->getMessage());
        }
    }

    public function failed(\Throwable $exception): void
    {
        // Called once retries are fully exhausted.
        $this->handleFailure($exception->getMessage());
    }

    protected function handleFailure(string $message, array $context = []): void
    {
        $this->product->update([
            'status' => Product::STATUS_FAILED,
            'error_message' => $message,
        ]);

        ErrorLog::create([
            'upload_id' => $this->product->upload_id,
            'product_id' => $this->product->id,
            'row_number' => $this->product->row_number,
            'message' => $message,
            'context' => ! empty($context) ? json_encode($context) : null,
        ]);

        $this->finalizeRow(success: false);

        Log::error('Product failed to import to Shopify', [
            'product_id' => $this->product->id,
            'error' => $message,
        ]);
    }

    /**
     * Bump the parent upload's counters and, once every row has been
     * processed, mark the upload as finished.
     */
    protected function finalizeRow(bool $success): void
    {
        DB::transaction(function () use ($success) {
            /** @var Upload $upload */
            $upload = Upload::query()->lockForUpdate()->find($this->product->upload_id);

            if (! $upload) {
                return;
            }

            $upload->processed_rows++;
            $success ? $upload->successful_rows++ : $upload->failed_rows++;

            if ($upload->total_rows > 0 && $upload->processed_rows >= $upload->total_rows) {
                $upload->status = $upload->failed_rows > 0
                    ? Upload::STATUS_COMPLETED_WITH_ERRORS
                    : Upload::STATUS_COMPLETED;
                $upload->finished_at = now();
            }

            $upload->save();
        });
    }
}
