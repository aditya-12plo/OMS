<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{

    protected $table = 'address';
    protected $primaryKey = 'id';
	public $incrementing = true;
    protected $fillable = array('country_name','province','city','area','sub_area','village','postal_code');
    public $timestamps = true;
}
