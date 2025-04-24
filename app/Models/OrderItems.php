<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Order;

/**
 * Class OrderItems
 *
 * @property int $id
 * @property int $order_id
 * @property string $upload
 * @property string $image_title
 * @property string $image_description
 * @property string $image_name
 * @property string $image_size
 * @property string $image_type
 * @property string $image_path
 * @property string $image_url
 * @property string $image_extension
 * @property string $image_mime
 * @property string $image_alt
 * @property int $image_batch
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */

class OrderItems extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'order_id',
        'upload',
        'image_title',
        'image_description',
        'image_name',
        'image_size',
        'image_type',
        'image_path',
        'image_url',
        'image_extension',
        'image_mime',
        'image_alt',
        'image_batch',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
    /**
     * Get the order by order ID.
     */
    public function getOrderByOrderId($orderId)
    {
        $order = $this->find($orderId);
        return $order ? $order->order : null;
    }
}
