<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	/**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['role','description'];

    /**
     * this is a many to many relationship between user and their roles
     * @return collection
     */
    public function users()
    {
        return $this->belongsToMany('App\User')->withTimestamps();
    }
}
