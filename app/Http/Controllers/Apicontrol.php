<?php 
	
	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\signupmodel;
	use App\Models\GetJobCategory;
	use App\Models\JobPost;
	use App\Models\Usersprofile;
	use App\Models\StatusOfOpenedJob;
	use App\Models\Notification;
	use App\Models\Bidding;

	use App\trabajofuncs\Trabajofuncs;
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
				"price"					=> $contractp,
				"perwhatjb"             => $priceper,
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
			$typeofjob 		= $reqs->input("jobtype"); // workonlyfor field :: // 1 for bidding // 0 for immediate hiring
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

			$sql = "select * from ( SELECT *,floor(111.111 * DEGREES(ACOS(LEAST(1.0, COS(RADIANS({$latitude})) * COS(RADIANS(a.joblocationlatitude)) * COS(RADIANS({$longitude} - a.joblocationlongitude)) + SIN(RADIANS(a.joblocationlatitude)) * SIN(RADIANS({$latitude})))))) AS distance_in_km FROM jobs AS a ) as tbl1 join userprofile as uprof on tbl1.employerid = uprof.userid where tbl1.distance_in_km <= {$getfromwithin} and tbl1.workonlyfor = '{$typeofjob}' and tbl1.title like'%{$workcategory}%' and tbl1.from BETWEEN '{$startdate}' and '{$enddate}' and tbl1.jobid not in (select jobid from bidding where workerid = '{$userid}') order By distance_in_km ASC";
				//  and tbl1.jobid not in (select jobid from statusofopenedjob)

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
			// $userid = 7;
			// $homelocation = Usersprofile::where("userid",'=',$userid)->get()->toArray();
			// echo $homelocation[0]['addresslatitude'];
			// echo "<br/>";
			// echo $homelocation[0]['addresslongitude'];

			echo JobPost::where("jobid",33)->get(["employerid"])[0]->employerid;
		}

		public function applytojob(Request $response) {
			$jobid 	   = $response->input("jobid");
			$workerid  = $response->input("workerid");

			$employerid = JobPost::where("jobid",$jobid)->get(["employerid"])[0]->employerid;
			// $jobid 	   = 34;
			// $workerid  = 7;

			// save to statusofopenedjob
			$statofjob 	   = StatusOfOpenedJob::create([
										"jobid"  	=> (int) $jobid,
										"status"    => 0,
										"workerid"	=> (int) $workerid
									]);

			$bidtbl   = ["workerid"    => (int) $workerid,
						 "employerid"  => (int) $employerid,
						 "jobid"	   => (int) $jobid,
						 "bidprice"	   => null,
						 "perwhat"	   => null,
						 "typeofbid"   => null,
						 "status"	   => 0
						];

			$workername = Usersprofile::where("userid",$workerid)->get(["name"])[0]->name;
			$jobsdet    = JobPost::where("jobid",$jobid)->get(["title","perwhatjb"])->toArray();
			$jobtitle   = $jobsdet[0]->title;
			$perwhatjb  = $jobsdet[0]->perwhatjb;

			$notifmsg   = null;
			// isbidding is default to false
			if ($response->input("isbidding") == "true") { 
				// save to bidding table :: Bidding
				$bidtbl['perwhat']   = $perwhatjb;
				$bidtbl['bidprice']  = $theprice = $response->input("bidprice");
				$bidtbl['typeofbid'] = "custompricebid";
				// {$workername}
				$notifmsg 			 = "<b style='color:#0922a3'>{$jobtitle}</b> has been bid on by a <b style='color:#0922a3'>worker</b> for <b>{$theprice} {$perwhatjb}</b>.";
			} else {
				$workerdetails       = Usersprofile::where("userid",$workerid)->get(["pricewage","perwhat"]);
				$bidtbl['perwhat']   = $workerdetails[0]->perwhat;   // get perwhat from usersprofile table
				$bidtbl['bidprice']  = $workerdetails[0]->pricewage; // get price from usersprofile table
				$bidtbl['typeofbid'] = "fixpricebid";
				$notifmsg 			 = "<b>{$workername}</b> applied for the <b>{$jobtitle}</b> you posted.";
			}

			$savethisbid = Bidding::create($bidtbl);
			$savethisbid->save();

			$savetonotif = Notification::create([
							"table"			=> "jobs",
							"uniqueid"		=> $jobid,
							"notiffrom"		=> $workerid,	
							"notiffor"		=> $employerid,
							"thenotif"		=> $notifmsg,
							"isread"		=> 0
						]);

			if ($statofjob->save()) {
				/** save to notification table */

				/** */				
				return response()->json([
					"response" => true
				]);
			}

			return response()->json([
				"response" => false
			]);

		}

		public function readnotifications(Request $reqs) {
			$employerid = $reqs->input("empid");

			// $employerid = 3;
			$rets = Notification::where("notiffor",$employerid)->get()->toArray();

			$ret_s = array_map(function($a){
				$a['created_at'] = "<i>".date("l - M. d, Y", strtotime($a['created_at']))."</i>";
				return $a;
			}, $rets);

			return response()->json([
				"response"	=> $ret_s
			]);
		}

		public function getnotifdetails(Request $reqs) {
			$tbl 		 = $reqs->input("table");
			$uniqueid    = $reqs->input("uniqueid");
			$notifid     = $reqs->input("notifid");

			// $tbl  			= "bidding";
			// $uniqueid 		= "39";

			$details    	= Bidding::where("jobid",$uniqueid)->orderBy("bidprice")->get()->toArray();
			$jobdetails     = JobPost::where("jobid",$uniqueid)->get(["title","from"])->toArray();

			$jobdetails = array_map(function($a){
				$a['from'] = date("l - M. d, Y", strtotime($a['from']));
				return $a;
			}, $jobdetails);

			$datetoday  = [["datetoday" => date("D - M. d, Y")]];

			$lowest     	= null;
			$thelowbidders  = [];
			$otherbidders   = [];

			for($i=0;$i<=count($details)-1;$i++) {
				if ($lowest == null) {
					$lowest = $details[$i]['bidprice'];
					array_push($thelowbidders, $details[$i]);
				} else {
					if ($lowest == $details[$i]['bidprice']) {
						array_push($thelowbidders, $details[$i]);
					} else {
						array_push($otherbidders,$details[$i]);
					}
				}
			}

			return response()->json([
				"response" => [$thelowbidders,$otherbidders,$jobdetails,$datetoday]
			]);

		}

		public function hireworker(Request $reqs) {
			$bidid = $reqs->input("bidid");

			Bidding::where("id",$bidid)->update(["status"=>1]);

			$biddetails = Bidding::where("id",$bidid)->get("jobid")->toArray();
			$jobid      = $biddetails[0]['jobid'];

		//	JobPost::where("jobid",$jobid)->update(["jobstatus"=>0]);
			if (Trabajofuncs::closethejob($jobid)) {
				return response()->json([
					"response" => true
				]);
			}
		}

		public function statusofopenedjob(Request $reqs) {
			// from the jobs table
			// if 1 = currently hiring
			// if 0 = job is closed and somebody got hired
			// if 2 = job is closed and no one got hired
			// if 3 = job is completely done
			// :::::: if 3 = job is currently being worked out
			
			// query from the table the jobs with 0 status
			// join bidding table, userprofile table and statusofopenedjob table

			$employerid = $reqs->input("empid");
			// $employerid    = 3;

			$data = DB::table("jobs")
						->join("bidding","jobs.jobid","=","bidding.jobid")
						->join("userprofile","bidding.workerid","=","userprofile.userid")
						->join("statusofopenedjob","jobs.jobid","=","statusofopenedjob.jobid")
						->select("jobs.title","jobs.from","bidding.id as biddingid","bidding.workerid","bidding.bidprice","bidding.perwhat","bidding.typeofbid","bidding.status","userprofile.name as workername","statusofopenedjob.targetdate","statusofopenedjob.status as completionrate")
						->where("jobs.employerid","=",$employerid)
						->where("jobs.jobstatus","=",0)
						->get()->toArray();

			$d = array_map(function($a){
				$a->from = date("D - M. d, Y", strtotime($a->from));
				return $a;
			},$data);

			return response()->json([
				"response" => $d
			]);
		}

		public function statusofcurrentlyhiring(Request $reqs) {
			// from the jobs table
			// query the jobs with 1 status
			// refer to the notifications

			$data = DB::table("jobs")
						->join("userprofile","jobs.employerid","=","userprofile.userid")
						->select("jobs.*","userprofile.name")
						->where("jobs.jobstatus","=",1)
						->orderBy("jobid","desc")
						->get()->toArray();
			
			$d = array_map(function($a){
				$a->created_at  = date("D - M. d, Y", strtotime($a->created_at));
				$a->from 		= date("D - M. d, Y", strtotime($a->from));

				if ($a->workonlyfor == "1") { // bidding
					$a->workonlyfor = "BIDDING";
				} else if($a->workonlyfor == "0") { // for immediate hiring
					$a->workonlyfor = "IMMEDIATE HIRING";
				}

				return $a;
			}, $data);

			return response()->json([
				"response" => $d
			]);
		}

	}

?>