<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Locations extends Model
{

    protected $table 		= 'locations';
    protected $primaryKey 	= 'location_id';
	
    protected $fillable = array(
			'fulfillment_center_id','location_code','location_description','max_qty','min_qty',
			'status'
		);
		
    public $timestamps = true;
  
		
    public function fulfillment()
    {
		return $this->belongsTo('App\Models\FulfillmentCenter','fulfillment_center_id');        
    }
	
    public function locationDetails()
    {
        return $this->hasMany('App\Models\LocationsDetail','location_id');
    }
	
    public function locationDamageDetails()
    {
        return $this->hasMany('App\Models\LocationsDamageDetail','location_id');
    }
	
}
