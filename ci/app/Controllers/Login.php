<?php

namespace App\Controllers;
use CodeIgniter\HTTP\IncomingRequest;

class Login extends BaseController{
    public function index(){
		$viewData	= array();

		$viewData["sesionLanguage"]	= $this->sesionLanguage;

		return view('Headers/header')
            . view('login' , $viewData)
            . view('Headers/footer');
    }
	
	public function process(){
		$validation 		=  \Config\Services::validation();
		$requestResponse	= array();

		$sesionLanguage = get_instance()->sesionLanguage;
		
        $rules = [
            "loginEmail" => [
                "label" => "Email ", 
                "rules" => "required"
            ],
            "loginPassword" => [
                "label" => "Password ", 
                "rules" => "required"
            ]
        ];
		
        if ($this->validate($rules)) {
			$loginEmail	 	= $this->request->getPost('loginEmail');
			$loginPassword	= $this->request->getPost('loginPassword');

			if( !$this->Engine->validateEmail($loginEmail) ){
				$requestResponse["status"]	= "001";
				$requestResponse["msg"]		=  $sesionLanguage[0]->loginErrorMsg001;
				die(json_encode($requestResponse));						
			}

			if( strlen($loginPassword) < 4 ){
				$requestResponse["status"]	= "002";
				$requestResponse["msg"]		= $sesionLanguage[0]->loginErrorMsg002;
				die(json_encode($requestResponse));					
			}

			//Validate email and password fields present

			$loginEmail		= $this->Engine->fetchUserName($loginEmail);

			if($loginEmail == null){
				$requestResponse["status"]	= "003";
				$requestResponse["msg"]		= $sesionLanguage[0]->loginErrorMsg003;
				die(json_encode($requestResponse));					
			}
			
			$result_login = $this->Engine->doLoginProcess($loginEmail , $loginPassword);
			
			if($result_login == FALSE){
				$requestResponse["status"]	= "004";
				$requestResponse["msg"]		= $sesionLanguage[0]->loginErrorMsg004;
				die(json_encode($requestResponse));
			}
			else{
				$user_id	= $this->Engine->fetchUserId($loginEmail);
				$dataLogArr	= array("user_id" => $user_id, "item_id" => $user_id, "input_data" => "",
									"output_data" => "" , "au_type" => "1" );

				if($this->Engine->saveAuditActivityLogs($dataLogArr)){
					$requestResponse["status"] = "111";
					$requestResponse["msg"] = "Successfull";
					$requestResponse["redLink"] = base_url('transactions/history');
					die(json_encode($requestResponse));		
				};
			}
			
        } else {
            $requestResponse["status"]	= "000";
			$requestResponse["msg"]		= $sesionLanguage[0]->loginErrorMsg000;
			die(json_encode($requestResponse));
        }
		
	}
}
