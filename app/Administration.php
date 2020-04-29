<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Administration extends Model
{
    protected $fillable = [
        'adm_firstname'
        , 'adm_lastname'
        , 'adm_middlename'
        , 'adm_phone_number'
        , 'adm_email'
        , 'adm_user_id'
        , 'adm_type_id'
        , 'adm_school_id'

    ];
    const CREATED_AT = 'adm_created';
    const UPDATED_AT = 'adm_updated';
    public function users() {
        return $this->hasOne('App\User', 'adm_user_id', 'id');
    }
}
