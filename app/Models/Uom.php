<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uom extends Model
{

    protected $table 		= 'uom';
    protected $primaryKey 	= 'uom_code';
	public $incrementing 	= false;
    protected $keyType 		= 'string';
	
    protected $fillable = array(
			'uom_description'
		);
		
    public $timestamps = true;
  
}
