<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    protected $fillable = [
        'sch_name'
        , 'sch_city'
        , 'sch_region'
    ];

    const CREATED_AT = 'sch_created';
    const UPDATED_AT = 'sch_updated';
}
