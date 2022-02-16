<?php 
	
	namespace App\Http\Controllers;

	use Illuminate\Http\Request;
	use App\Models\signupmodel;

	use Illuminate\Support\Facades\Crypt;

	use Laravel\Lumen\Routing\Controller as BaseController;

	class Apicontrol extends BaseController {

		public function signup(Request $request) {
			$exist = signupmodel::where("username",'=',$request->input("username"))->orWhere("emailaddress","=",$request->input("emailaddress"))->get();

			if (count($exist) > 0) {
				return response()->json([
					"message" => "Username or email already exists"
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
					"message" 	=> "Your account has been saved"
				]);
			}

		}

		public function signin(Request $request) {
			$exist = signupmodel::where("username",'=',$request->input("username"))->get()->toArray();

			if (count($exist) == 1) { // found 1
				if ($request->input("password") == Crypt::decrypt($exist[0]['password'])) {
					return response()->json([
						"message" => "true",
						"toa"     => "{$exist[0]['typeofaccount']}"
					]);
				}
			}

			return response()->json([
				"message" => "false"
			]);
		}
	}

?>