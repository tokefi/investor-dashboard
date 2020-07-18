<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $guarded = [];

    public function getPropertiesAttribute($value)
    {
        return json_decode($value);
    }

    public function getAttributesAttribute($value)
    {
        return json_decode($value);
    }
}
