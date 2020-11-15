<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsDamage extends Model
{

    protected $table 		= 'products_damage';
    protected $primaryKey 	= 'products_demage_id';
	
    protected $fillable = array(
			'product_id','fulfillment_center_id','qty','reason','additional_reason','hold_by','hold_date','sale_by','hold_date','sale_date','status'
		);
		
    public $timestamps = true;
  
	
    public function product()
    {
		return $this->belongsTo('App\Models\Products','product_id');
        
    }
	
    public function fulfillment()
    {
		return $this->belongsTo('App\Models\FulfillmentCenter','fulfillment_center_id');        
    }
  
	
    public function locationDamage()
    {
        return $this->hasOne('App\Models\LocationsDamageDetail','products_demage_id');
    }
}
