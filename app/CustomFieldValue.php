<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $table = 'custom_field_values';

    protected $guarded = [];

    public function customField()
    {
        return $this->belongsTo('App\CustomField', 'custom_field_id');
    }
}
