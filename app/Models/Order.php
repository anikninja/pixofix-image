<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Employee;
use App\Models\OrderItems;
use App\Models\Category;
/**
 * Class Order
 *
 * @property int $id
 * @property string $order_number
 * @property string $order_date
 * @property string $status
 * @property string|null $notes
 * @property int $employee_id
 * @property int $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */

class Order extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'order_number',
        'order_date',
        'status',
        'notes',
        'employee_id',
        'category_id',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
    /**
     * Get the order items for the order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItems::class);
    }
}
