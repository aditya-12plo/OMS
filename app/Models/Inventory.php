<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{

    protected $table 		= 'inventory';
    protected $primaryKey 	= 'inventory_id';
	
    protected $fillable = array(
			'product_id','fulfillment_center_id','company_id','stock_on_hand','stock_available',
			'stock_hold','stock_booked'
		);
		
    public $timestamps = true;
  
	
    public function product()
    {
		return $this->belongsTo('App\Models\Product','product_id');
        
    }
	
    public function fulfillment()
    {
		return $this->belongsTo('App\Models\FulfillmentCenter','fulfillment_center_id');        
    }
}
