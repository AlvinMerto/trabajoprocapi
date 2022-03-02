<?php 
	
	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\signupmodel;
	use App\Models\GetJobCategory;
	use App\Models\JobPost;
	use App\Models\Usersprofile;
	use App\Models\StatusOfOpenedJob;
	use App\Models\Notification;

	// classes 
	use App\classes\TrabajoUtil;
	// end

	use Illuminate\Support\Facades\Crypt;
	use Illuminate\Support\Facades\DB;

	use Laravel\Lumen\Routing\Controller as BaseController;

	class Apicontrol extends BaseController {

		public function signup(Request $request) {
			$exist_username = signupmodel::where("username",'=',$request->input("username"))->get();
			if (count($exist_username) > 0) {
				return response()->json([
					"message" => "duplicate username"
				]);
			}

			$exist_email = signupmodel::where("emailaddress",'=',$request->input("emailadd"))->get();
			if (count($exist_email) > 0) {
				return response()->json([
					"message" => "duplicate email"
				]);
			}

			$inputs = ["username" 		=> $request->input("username"),
					   "password" 		=> Crypt::encrypt($request->input("password")),
					   "emailaddress"	=> $request->input("emailadd"),
					   "typeofaccount"  => (int) $request->input("accounttype"),
					   "status" 		=> $request->input("status")];
			$add = signupmodel::create($inputs);

			if ($add->save()) {
				return response()->json([
					"message" 	=> "saved"
				]);
			}

			return response()->json([
				"message" => "false"
			]);
		}

		public function signin(Request $request) {
			$exist = signupmodel::where("username",'=',$request->input("username"))->get()->toArray();

			if (count($exist) == 1) { // found 1
				if ($request->input("password") == Crypt::decrypt($exist[0]['password'])) {
					return response()->json([
						"message" => "true",
						"toa"     => "{$exist[0]['typeofaccount']}",
						"userid"  => $exist[0]['userid']
					]);
				} else {
					return response()->json([
						"message" => "false"
					]);
				}
			}

			return response()->json([
				"message" => "false"
			]);
		}

		public function getcategory() {
			$cats = [];

			foreach(GetJobCategory::all() as $cs) {
				array_push($cats,$cs->thejob);
			}

			return response()->json($cats);
		}

		public function postjob(Request $response) {
			$employerid  = $response->input("employerid");
			$location    = $response->input("location");
			$jobcategory = $response->input("jobcategory");
			$description = $response->input("description");
			$contractp   = $response->input("contractprice");
			$priceper    = $response->input("priceper");
			$offerwithin = $response->input("offerwithin");
			$workonly    = $response->input("workonlyfor"); // if the job posted is for bidding[0] or for immediate hiring[1]

			// dates 
			$datefrom = $response->input("datefrom");
			$dateend  = null; 
			
		// 	if (null !== $response->input("dateend")) {
		//		$dateend = $response->input("dateend");
		// 	}

			$locationlat  = null;
			$locationlong = null;
			$readableloc  = null;

			// save to jobcategory
				$cat_exist = GetJobCategory::where("thejob","=",$jobcategory)->get()->toArray();

				if (count($cat_exist)==0) {
					// means nothing found, then save one
					$save_cat = GetJobCategory::create(["thejob"=>$jobcategory]);
					$save_cat->save();
				}
			// end 

			if ($location == "home") {
				$homelocation = Usersprofile::where("userid",'=',$employerid)->get()->toArray();

				if(count($homelocation) == 0) {
					return false;
				} else {
					$locationlat  = $homelocation[0]['addresslatitude'];
					$locationlong = $homelocation[0]['addresslongitude'];
					$readableloc  = $homelocation[0]['address'];
				}
			} elseif ($location == "currentLocation") {
				// make it default:: use the values passed from the app
				$locationlat  = $response->input("latitude");
				$locationlong = $response->input("longitude");
				$readableloc  = "test test test";
			}

			$values = [
				"employerid"    		=> $employerid,
				"title"					=> $jobcategory,
				"definition"			=> $description,
				"joblocationlatitude"	=> $locationlat,
				"joblocationlongitude"  => $locationlong,
				"jobReadableLocation"   => $readableloc,
				"price"					=> $contractp." ".$priceper,
				"range"					=> $offerwithin,
				"workonlyfor"			=> $workonly, 
				"from"					=> $datefrom,
				"to"					=> $dateend,
				"jobstatus"				=> 1
			]; 

			$thejob = JobPost::create($values);

			if ($thejob->save()) {
				return response()->json([
					"return"	=> true
				]);
			}

			return response()->json([
				"return" 	=> false
			]);
		}

		public function searchforajob(Request $reqs) {
			$getfromwithin  = $reqs->input("getfromwithin");
			$typeofjob 		= $reqs->input("jobtype"); // workonlyfor or the 
			$workcategory 	= $reqs->input("workcategory");
			$startdate      = $reqs->input("startdate");
			$enddate 		= $reqs->input("enddate");
			$userid         = $reqs->input("workerid"); // find in the userid field in usersprofile table
			$joblocation    = (int) $reqs->input("locationofjob");
			
			$latitude       = null;
			$longitude      = null;

			// $getfromwithin  = 15;
			// $typeofjob      = 0;
			// $workcategory   = "barber";
			// $startdate      = "02/27/2022";
			// $enddate 	    = "02/28/2022";
			// $userid 		= 7;
			// $joblocation    = 1;

// return response()->json(["thejobs"=>$joblocation]);

			if ($joblocation == 1) {
				// current location:: must pass in the longitude and latitude
				// $latitude  = 7.2139391;
				// $longitude = 125.596082;

				$latitude  = $reqs->input("latitude");
				$longitude = $reqs->input("longitude");
			} else if ($joblocation == 0) { // 
				// home location 
				$homelocation = Usersprofile::where("userid",'=',$userid)->get()->toArray();

				if (count($homelocation) == 0) {
					return false;
				} else {
					$latitude  = $homelocation[0]['addresslatitude'];
					$longitude = $homelocation[0]['addresslongitude'];
				}
			}

			$sql = "select * from ( SELECT *,floor(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS({$latitude})) * COS(RADIANS(a.joblocationlatitude)) * COS(RADIANS({$longitude} - a.joblocationlongitude)) + SIN(RADIANS(a.joblocationlatitude)) * SIN(RADIANS({$latitude})))))) AS distance_in_km FROM jobs AS a ) as tbl1 join userprofile as uprof on tbl1.employerid = uprof.userid where tbl1.distance_in_km <= {$getfromwithin} and tbl1.workonlyfor = '{$typeofjob}' and tbl1.title like'%{$workcategory}%' and tbl1.from BETWEEN '{$startdate}' and '{$enddate}' and tbl1.jobid not in (select jobid from statusofopenedjob) order By distance_in_km ASC";

	//	return response()->json(["thejobs"=>$sql]);

			$thejobs = DB::select($sql);

			if (count($thejobs) > 0) {
				return response()->json([
					"thejobs" => $thejobs
				]);
			}

			return response()->json([
				"thejobs" => []
			]);
		}

		public function testing() {
			// TrabajoUtil::convertokm();
			$userid = 7;
			$homelocation = Usersprofile::where("userid",'=',$userid)->get()->toArray();
			echo $homelocation[0]['addresslatitude'];
			echo "<br/>";
			echo $homelocation[0]['addresslongitude'];
		}

		public function applytojob(Request $response) {
			$jobid 	   = $response->input("jobid");
			$workerid  = $response->input("workerid");

			// $jobid 	   = 34;
			// $workerid  = 7;

			// save to statusofopenedjob
			$statofjob 	   = StatusOfOpenedJob::create([
										"jobid"  	=> (int) $jobid,
										"status"    => 0,
										"workerid"	=> (int) $workerid
									]);

			if ($statofjob->save()) {
				// return response()->json([
				// 	"response" => true,
				// ]);
				return response()->json([
					"response" => true
				]);
			}

			return response()->json([
				"response" => false
			]);

		}

	}

?>