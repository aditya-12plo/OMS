<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentCenter extends Model
{

    protected $table = 'fulfillment_center';
    protected $primaryKey = 'fulfillment_center_id';
	 public $incrementing = true;
    protected $fillable = array('company_id','code','name','pic','phone','mobile','fax','email','address','address2','country','province','city','area','sub_area','postal_code','village','status','remarks');
    public $timestamps = true;

    /**
     * Get the fulfillment that owns the company.
     */
    public function company()
    {
    	return $this->hasOne('App\Models\Company','company_id','company_id');
    }
}
