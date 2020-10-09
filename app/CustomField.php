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
    public function applicationSection()
    {
        return $this->belongsTo('App\ApplicationSections','section_id');
    }
     public function isConditional()
    {
        return $this->hasMany('App\CheckboxConditional','custom_field_id');
    }
}
