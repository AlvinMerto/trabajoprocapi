<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
	
class JobPost extends Model implements AuthenticatableContract, AuthorizableContract {
	use Authenticatable, Authorizable, HasFactory;

	protected $table 	= "jobs";
	protected $fillable = ["employerid","title","definition","joblocationlatitude","joblocationlongitude","jobReadableLocation","price","range","workonlyfor","from","to","jobstatus","created_at","updated_at"];  
} 

?>