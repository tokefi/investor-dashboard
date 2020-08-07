<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomField extends Model
{
    use SoftDeletes;

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
