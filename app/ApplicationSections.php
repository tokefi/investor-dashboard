<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ApplicationSections extends Model
{
	protected $table = 'application_sections'; 

    protected $guarded = [];

    protected $fillable = ['name','label','description','site_url','rank'];
}
