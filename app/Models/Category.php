<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Order;

class Category extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'slug',
        'description',
    ];

    /**
     * Get the orders for the category.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
