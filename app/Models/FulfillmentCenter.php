<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentCenter extends Model
{

    protected $table = 'fulfillment_center';
    protected $primaryKey = 'fulfillment_center_id';
	 public $incrementing = true;
    protected $fillable = array('code','name','pic','phone','mobile','fax','email','address','address2','country','province','city','area','sub_area','postal_code','village','status','remarks','longitude','latitude','fulfillment_center_type_id');
    public $timestamps = true;

    /**
     * Get the fulfillment that owns the company.
     */
	
    public function inventorys()
    {
		return $this->hasMany('App\Models\Inventory','fulfillment_center_id');
    }
	
    public function locations()
    {
		return $this->hasMany('App\Models\Locations','fulfillment_center_id');
    }
	
    public function fulfillmentType()
    {
		  return $this->belongsTo('App\Models\FulfillmentCenterType','fulfillment_center_type_id');        
    }
}
