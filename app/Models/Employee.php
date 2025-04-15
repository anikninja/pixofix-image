<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;
use App\Models\Order;

/**
 * Class Employee
 *
 * @property int $id
 * @property string $name
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 */


class Employee extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'user_id'
    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
        * Get the employee by user ID.
        *
        * @param int $userId
        * @return \App\Models\Employee|null
        */  
    public function getUserByEmployee($employeeId)
    {
        $employee = $this->find($employeeId);
        return $employee ? $employee->user : null;
    }
    
    /**
     * Summary of saveWithUser
     * @param \App\Models\User $user
     * @return void
     */ 
    public function saveWithUser(User $user)
    {
        $this->user()->associate($user);
        $this->save();
    }
    /**
     * Get the orders for the employee.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
