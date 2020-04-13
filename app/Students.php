<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Students extends Model
{
    const UPDATED_AT = null;
    const CREATED_AT = null;
    protected $fillable = [
        'stu_ticket'
        , 'stu_firstname'
        , 'stu_secondname'
        , 'stu_middlename'
        , 'stu_class_number_id'
        , 'stu_class_letter'
        , 'stu_created'
        , 'stu_updated'
    ];
}
