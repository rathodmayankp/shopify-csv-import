<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'upload_id',
        'row_number',
        'handle',
        'title',
        'body_html',
        'vendor',
        'product_type',
        'tags',
        'published',
        'sku',
        'price',
        'compare_at_price',
        'inventory_qty',
        'weight',
        'weight_unit',
        'image_src',
        'image_alt',
        'status',
        'shopify_product_id',
        'error_message',
    ];

    protected $casts = [
        'published' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'inventory_qty' => 'integer',
    ];

    public function upload(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function errorLogs(): HasMany
    {
        return $this->hasMany(ErrorLog::class);
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'gray',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_SUCCESS => 'green',
            self::STATUS_FAILED => 'red',
            default => 'gray',
        };
    }
}
