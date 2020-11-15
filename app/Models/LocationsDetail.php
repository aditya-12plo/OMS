<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationsDetail extends Model
{

    protected $table 		= 'locations_detail';
    protected $primaryKey 	= 'location_detail_id';
	
    protected $fillable = array(
			'location_id','inventory_id','max','min'
		);
		
    public $timestamps = true;
  
	
    public function location()
    {
        return $this->belongsTo('App\Models\Locations','location_id');
    }
	
    public function inventorys()
    {
        return $this->belongsTo('App\Models\Inventory','inventory_id');
    }
	
}
