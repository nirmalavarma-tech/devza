<?php
class k2sClass{
	
	public function login($username, $password){
		$status = 'failed';
		$message = '';
		$result = array();
		
		if(!empty($username) && !empty($password)){
			
			// call the login api to check the details
			$loginApi = "https://api.k2s.cc/v1/auth/token";
			$loginParams = array("username"=>$username,"password"=>$password,"grant_type"=>"password","client_id"=>"k2s_web_app","client_secret"=>"pjc8pyZv7vhscexepFNzmu4P","csrfToken"=>"a19713af636c6");
			
			$curl = curl_init($loginApi);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS,  json_encode($loginParams));
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
			$result = curl_exec($curl);
			curl_close($curl);
			
			$result = json_decode($result);
			
			if(empty($result->token_type)){
				$message = 'Invalid details';
			}
			else{
				$message = 'success';
				$status = 'success';
			}
		}
		else{
			$message = 'Incomplete details';
		}
		$data = array("message"=>$message, "status"=>$status, "response"=>$result);
		return $data;
	}
	
	function getProfileDetails($accessToken,$refreshToken){
		
		$status = 'failed';
		$message = '';
		$result = array();
		$statistics = array();
		
		if(!empty($accessToken) && !empty($refreshToken)){
			$profileApi = "https://api.k2s.cc/v1/users/me";
			
			// prepare the headers
			$headers = array(
				'Content-Type: application/json',
				'cookie: accessToken = '.$accessToken.';refreshToken='.$refreshToken.';'
			);
			
			// make the curl call to profile, using GET method
			$curl = curl_init($profileApi);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($curl);
			curl_close($curl);
			
			$result = json_decode($result);
			
			if(empty($result) || empty($result->id)){
				$message = 'Invalid token';
			}
			else{
				// get statistics
				$statistics = $this->getStatisticsDetails($accessToken,$refreshToken);
				$status = 'success';
			}
		}
		else{
			$message = 'Incomplete details';
		}
		
		$data = array("message"=>$message, "status"=>$status, "response"=>$result,"stats"=>$statistics);
		return $data;
	}
	
	function getStatisticsDetails($accessToken,$refreshToken){
		
		$status = 'failed';
		$message = '';
		$result = array();
		
		if(!empty($accessToken) && !empty($refreshToken)){
			$statsApi = "https://api.k2s.cc/v1/users/me/statistic";
			
			// prepare the headers
			$headers = array(
				'Content-Type: application/json',
				'cookie: accessToken = '.$accessToken.';refreshToken='.$refreshToken.';'
			);
			
			// make the curl call to statistics api, using GET method
			$curl = curl_init($statsApi);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			$result = curl_exec($curl);
			curl_close($curl);
			
			$result = json_decode($result);
			
			if(empty($result) || empty($result->id)){
				$message = 'Invalid token';
			}
			else{
				$status = 'success';
			}
		}
		else{
			$message = 'Incomplete details';
		}
		
		$data = array("message"=>$message, "status"=>$status, "response"=>$result);
		return $data;
	}
}


$username = "nirmala@gmail.com";
$password = "Nirmala@123";

$k2Obj = new k2sClass();
$result = $k2Obj->login($username,$password);

// if the user is authenticated, we will pull the user details from profile api
if($result['status'] == "success"){
	
	$accessToken = $result['response']->access_token;
	$refreshToken = $result['response']->refresh_token;
	$profileDetails = $k2Obj->getProfileDetails($accessToken,$refreshToken);
	
	if($profileDetails['status'] == 'success'){
		echo "Account type: ".$profileDetails['response']->accountType."\n";
		echo "Daily Traffic: ".$profileDetails['stats']['response']->dailyTraffic->total."\n";
		echo "Storage Space: ".$profileDetails['stats']['response']->storageSpace->total;
	}
}
else{
	echo $result['message'];
}
?>