<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRole extends Model
{

    protected $table = 'users_role';
    protected $primaryKey = 'user_role_id';
	 public $incrementing = false;
    protected $keyType = 'string';
	
    protected $fillable = array('user_role_description');
    public $timestamps = true;
  
    public function userRole()
    {
        return $this->belongsTo('App\Models\User');
    }
  
  
    /**
     * Get All the record associated with the user.
     */
	
    public function users()
    {
        return $this->hasMany('App\Models\User','user_role_id');
    }
}
