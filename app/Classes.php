<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Classes extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $fillable = [
        'cla_number'
    ];
}
