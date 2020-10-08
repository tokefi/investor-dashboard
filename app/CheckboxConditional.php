<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckboxConditional extends Model
{
    protected $table = 'checkbox_conditionals'; 

    protected $guarded = [];

    protected $fillable = ['custom_field_id','custom_checkbox_id','is_linked'];

    public function linked()
    {
    	return $this->belongsTo('App\customField');
    }
}
