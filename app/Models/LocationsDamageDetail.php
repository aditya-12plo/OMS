<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationsDamageDetail extends Model
{

    protected $table 		= 'locations_detail_demage';
    protected $primaryKey 	= 'location_detail_damage_id';
	
    protected $fillable = array(
			'location_id','products_demage_id','qty'
		);
		
    public $timestamps = true;
  
	
    public function locationDescription()
    {
        return $this->belongsTo('App\Models\Locations','location_id');
    }
}
