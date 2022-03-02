<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
	
class GetJobCategory extends Model implements AuthenticatableContract, AuthorizableContract {
	use Authenticatable, Authorizable, HasFactory;

	protected $table 	= "jobcategory";
	protected $fillable = ["thejob","created_at","updated_at"];  
} 

?>