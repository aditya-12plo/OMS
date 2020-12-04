<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{

    protected $table = 'company';
    protected $primaryKey = 'company_id';
	 public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = array('company_id','name','pic','phone','mobile','fax','email','address','address2','country','province','city','area','sub_area','postal_code','village','status');
    public $timestamps = true;

    /**
     * Get the user that owns the company.
     */
    public function users()
    {
        return $this->belongsTo('App\Models\User');
    }
	
	
    public function fulfillments()
    {
        return $this->hasMany('App\Models\CompanyFulfillment','company_id');
    }
	
}
