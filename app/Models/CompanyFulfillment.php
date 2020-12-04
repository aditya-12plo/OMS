<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanyFulfillment extends Model
{

    protected $table = 'company_fulfillment';
    protected $primaryKey = 'company_fulfillment_id';
	 public $incrementing = true;
    protected $fillable = array('company_id','fulfillment_center_id');
    public $timestamps = true;

	
    public function fulfillment()
    {
        return $this->belongsTo('App\Models\FulfillmentCenter','fulfillment_center_id');
    }
	
    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id');
    }
	
}
