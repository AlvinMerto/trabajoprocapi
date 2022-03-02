<?php 

	namespace App\classes;

	class TrabajoUtil {
		public static function convertokm($from_lat, $from_long, $to_lat, $to_long) {
		   $lat1     = deg2rad($from_lat);
		   $long1    = deg2rad($from_long);

           $lat2     = deg2rad($to_lat);
           $long2    = deg2rad($to_long);

           //Haversine Formula
           $dlong    = $long2 - $long1;
           $dlati    = $lat2 - $lat1;
             
           $val      = pow(sin($dlati/2),2)+cos($lat1)*cos($lat2)*pow(sin($dlong/2),2);
             
           $res      = 2 * asin(sqrt($val));
             
           $radius   = 3958.756;
             
           $miles    = ($res*$radius);

           return $miles * 1.609344; // in kilometers
		}
	}

?>