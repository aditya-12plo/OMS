<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FulfillmentCenterType extends Model
{

    protected $table = 'fulfillment_center_type';
    protected $primaryKey = 'fulfillment_center_type_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = array('fulfillment_center_type_description');
    public $timestamps = false;

	
}
