<?php
namespace App\Models;

use App\Models\BaseModel;

class SurveyTemplate extends BaseModel
{
    protected $primaryKey = 'id';


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['owner_id', 'body'];

    protected $casts = [
        'body' => 'json',
    ];
}
