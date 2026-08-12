<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\Product;
use App\Models\Upload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public function __construct(public Upload $upload)
    {
    }

    public function handle(): void
    {
        Log::info('Starting CSV processing', ['upload_id' => $this->upload->id]);

        $this->upload->update([
            'status' => Upload::STATUS_PROCESSING,
            'started_at' => now(),
        ]);

        if (! Storage::disk('local')->exists($this->upload->stored_path)) {
            $this->markAsFailed('The uploaded file could not be found on disk.');

            return;
        }

        $fullPath = Storage::disk('local')->path($this->upload->stored_path);
        $handle = fopen($fullPath, 'r');

        if ($handle === false) {
            $this->markAsFailed('The uploaded file could not be opened for reading.');

            return;
        }

        $header = fgetcsv($handle);

        if ($header === false) {
            fclose($handle);
            $this->markAsFailed('The CSV file appears to be empty.');

            return;
        }

        $header = array_map(fn ($col) => trim((string) $col), $header);

        if (! in_array('Title', $header, true)) {
            fclose($handle);
            $this->markAsFailed('The CSV is missing a required "Title" column.');

            return;
        }

        $rowNumber = 1; // header was row 1
        $validRows = 0;
        $invalidRows = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;

            // Skip completely blank lines that some spreadsheet tools leave behind.
            if (count($row) === 1 && trim((string) $row[0]) === '') {
                continue;
            }

            $data = $this->mapRowToColumns($header, $row);

            if (empty(trim((string) ($data['Title'] ?? '')))) {
                $invalidRows++;

                ErrorLog::create([
                    'upload_id' => $this->upload->id,
                    'row_number' => $rowNumber,
                    'message' => 'Row skipped - missing required "Title" value.',
                    'context' => json_encode($data),
                ]);

                continue;
            }

            $product = Product::create([
                'upload_id' => $this->upload->id,
                'row_number' => $rowNumber,
                'handle' => $data['Handle'] ?? null,
                'title' => $data['Title'],
                'body_html' => $data['Body HTML'] ?? null,
                'vendor' => $data['Vendor'] ?? null,
                'product_type' => $data['Product Type'] ?? null,
                'tags' => $data['Tags'] ?? null,
                'published' => $this->parseBool($data['Published'] ?? 'TRUE'),
                'sku' => $data['Variant SKU'] ?? null,
                'price' => $this->parseDecimal($data['Variant Price'] ?? null),
                'compare_at_price' => $this->parseDecimal($data['Variant Compare At Price'] ?? null),
                'inventory_qty' => (int) ($data['Variant Inventory Qty'] ?? 0),
                'weight' => $this->parseDecimal($data['Variant Weight'] ?? null),
                'weight_unit' => $data['Variant Weight Unit'] ?? null,
                'image_src' => $data['Image Src'] ?? null,
                'image_alt' => $data['Image Alt Text'] ?? null,
                'status' => Product::STATUS_PENDING,
            ]);

            $validRows++;

            ImportProductToShopify::dispatch($product);
        }

        fclose($handle);

        $this->upload->update([
            'total_rows' => $validRows,
            'failed_rows' => $invalidRows,
            'processed_rows' => $invalidRows,
        ]);

        Log::info('Finished parsing CSV', [
            'upload_id' => $this->upload->id,
            'valid_rows' => $validRows,
            'skipped_rows' => $invalidRows,
        ]);

        // Edge case: every row in the file was invalid, so no Shopify jobs were
        // ever dispatched. Nothing will finalize this upload, so do it now.
        if ($validRows === 0) {
            $this->upload->update([
                'status' => Upload::STATUS_FAILED,
                'failure_reason' => 'No valid product rows were found in the CSV.',
                'finished_at' => now(),
            ]);
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('CSV processing job failed', [
            'upload_id' => $this->upload->id,
            'error' => $exception->getMessage(),
        ]);

        $this->markAsFailed($exception->getMessage());
    }

    protected function markAsFailed(string $reason): void
    {
        $this->upload->update([
            'status' => Upload::STATUS_FAILED,
            'failure_reason' => $reason,
            'finished_at' => now(),
        ]);

        ErrorLog::create([
            'upload_id' => $this->upload->id,
            'message' => $reason,
        ]);
    }

    protected function mapRowToColumns(array $header, array $row): array
    {
        $data = [];

        foreach ($header as $index => $column) {
            $data[$column] = $row[$index] ?? null;
        }

        return $data;
    }

    protected function parseBool(mixed $value): bool
    {
        return in_array(strtoupper(trim((string) $value)), ['TRUE', '1', 'YES'], true);
    }

    protected function parseDecimal(mixed $value): ?float
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        return (float) $value;
    }
}
