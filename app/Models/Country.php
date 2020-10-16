<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{

    protected $table = 'country';
    protected $primaryKey = 'id';
    protected $fillable = array('name','alpha_2','alpha_3','alpha_numeric');
    public $timestamps = true;

}
