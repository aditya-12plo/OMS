<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Products extends Model
{

    protected $table 		= 'products';
    protected $primaryKey 	= 'product_id';
	
    protected $fillable = array(
			'company_id','product_code','product_description','uom_code','price',
			'width','height','weight','net_weight','gross_weight','qty_per_carton','carton_per_pallet',
			'cube','barcode','time_to_live','type','currency'
		);
		
    public $timestamps = true;
  
    public function company()
    {
        return $this->belongsTo('App\Models\Company','company_id');
    }
	
    public function uom_description()
    {
		return $this->belongsTo('App\Models\Uom','uom_code');
        
    }
	
    public function inventory()
    {
		return $this->hasMany('App\Models\Inventory','product_id');        
    }
	
    public function bundle()
    {
		return $this->hasMany('App\Models\ProductsKit','product_id');        
    }
  
}
