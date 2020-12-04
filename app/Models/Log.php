<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $primaryKey = 'log_id';
    protected $fillable = array('instance','channel','level','url','message','context','extra',
        'ip','user_agent'
    );
    public $timestamps = true; 
	 

}
