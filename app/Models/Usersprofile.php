<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;

class Usersprofile extends Model implements AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table      = "userprofile";
    
    protected $fillable   = [
        'userid','name','location','locationlatitude','locationlongitude','address','addresslatitude','addresslongitude','pricewage','perwhat','status','created_at','updated_at'
    ];

    // 'location','locationlatitude','locationlongitude'
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    
}
