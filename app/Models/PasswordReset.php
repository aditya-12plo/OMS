<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{

    protected $table 		= 'password_resets';
    protected $primaryKey   = null;
    public $incrementing    = false;

    protected $fillable = array('company_id', 'email', 'token', 'created_at');

    public $timestamps = false;
  
}