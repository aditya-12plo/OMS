<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $primaryKey = 'user_id';
    protected $fillable = [
        'name', 'phone', 'email','company_id','user_role_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password','remember_token',
    ];
	
	
    public function usersRole()
    {
        return $this->belongsTo('App\Models\UserRole','user_role_id');
    }
	
    /**
     * Get the record associated with the company.
     */
	
    public function company()
    {
    	return $this->hasOne('App\Models\Company','company_id','company_id');
    }
	
    public function role()
    {
    	return $this->hasOne('App\Models\UserRole','user_role_id','user_role_id');
    }
	
	
}
