<?php 
	
	namespace App\trabajofuncs\Trabajofuncs;

	use App\Models\JobPost;

	class Trabajofuncs {

		public static function closethejob($jobid) {
			$isdone = JobPost::where("jobid",$jobid)->update(["jobstatus"=>0]);
			return $isdone;
		}
	}
?>