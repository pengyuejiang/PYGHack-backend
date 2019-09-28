<?php
namespace App\Models;

use App\Models\BaseModel;

class Coupon extends BaseModel
{
    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sponsor_id', 'is_consumed', 'consumed_by', 'consumed_meal_name', 'consumed_at'];

    protected $casts = [
        'is_consumed' => 'boolean',
    ];
}
