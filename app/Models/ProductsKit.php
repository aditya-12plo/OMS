<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductsKit extends Model
{

    protected $table 		= 'products_bundle';
    protected $primaryKey 	= 'product_bundle_id';
	
    protected $fillable = array(
			'product_id','product_id_component','qty'
		);
		
    public $timestamps = true;
  
    public function product()
    {
        return $this->belongsTo('App\Models\Products','product_id_component','product_id');
    }
  
}
