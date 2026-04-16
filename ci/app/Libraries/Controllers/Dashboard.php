<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface; //ENABLES YOU TO USE UserFactoryInterface IN CONTAINER
use Joomla\CMS\User\UserHelper;

class Dashboard extends BaseController
{
    public function index(){
		$viewData		= array();

		return view('Headers/header')
            . view('Headers/page_header')
			. view('dashboard' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

    public function reports(){
		$viewData	= array();

		return view('Headers/header')
            . view('Headers/page_header')
			//. view('dashboard' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

    public function settings($user_id = 0){
		$viewData		= array();
		$user_id 		= (int)$user_id;

		if($user_id == 0){
			$user_id	= $this->userInfo->id;
		}

		$container		= \Joomla\CMS\Factory::getContainer();
		$userFactory	= $container->get(UserFactoryInterface::class);

		$viewData['user']			= $userFactory->loadUserById($user_id);
		$viewData['userData']		= $this->Engine->fetchUserDetails($user_id);
		$viewData["userTypes"]		= $this->Engine->fetchUserTypes();
		$viewData["languages"]		= $this->Engine->fetchSiteLanguages();
		$viewData["sesionLanguage"]	= $this->sesionLanguage;
		$viewData["s_user_id"]		= $this->userInfo->id;
		$viewData["permObj"]		= $this->Engine->permitedChangeUserProfile($this , $user_id);
		
		return view('Headers/header')
            . view('Headers/page_header')
			. view('settings' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

	public function admin(){
		$viewData	= array();
		$start		= 0;

		$viewData["usersData"]	= $this->Engine->fetchAllUserList($start);

		return view('Headers/header')
            . view('Headers/page_header')
			. view('administrator' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}
	
	public function signout(){
		\Joomla\CMS\Factory::getApplication()->getSession()->destroy();

		return redirect()->route('Login');
	}

	public function fetchartdata(){
		$viewData		= array();
		$responseArray	= array();

		$viewData['monthArr']	= $this->Engine->getmonth();

		$mtitles	= array("Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec");
		$currtVal	= array("100000","200000","400000","200000","150000","99000","200000","300000","500000","300000","140000","20000");
		$prevVal	= array("140000","260000","470000","280000","155000","99600","300000","350000","700000","200000","105000","60000");

		$responseArray["annualChart"] = array("title" => $mtitles , "currtVal" => $currtVal , "prevVal" => $prevVal);
		$responseArray["html"] = view('dispChartData' , $viewData);

		die( json_encode($responseArray) );
	}	

	public function fetchpopuser(){
		$viewData		= array();
		$responseArray	= array();
		$user_string	= $this->request->getPost('user_id');

		if( isset($user_string) ){
			$stringArr	= explode("_",$user_string);
			$user_id	= $stringArr[1];

			$viewData["userData"]	= $this->Engine->fetchUserDetails($user_id);
			$viewData["userTypes"]	= $this->Engine->fetchUserTypes();

			return view('popUserDetails' , $viewData);

			//$responseArray["html"]	= $this->Engine->fetchUserDetails($user_id);
			//die(json_encode($responseArray));
		}
	}

	public function fetchnewuserform(){
		$viewData		= array();

		$viewData["userTypes"]		= $this->Engine->fetchUserTypes();
		$viewData["languages"]		= $this->Engine->fetchSiteLanguages();
		$viewData["sesionLanguage"]	= $this->sesionLanguage;
		$viewData["userData"]		= $this->Engine->fetchUserDetails( $this->userInfo->id );
		$viewData["s_user_id"]		= $this->userInfo->id;

		return view('popUserNew' , $viewData);
	}

	public function postnewuser(){
		$name		= $this->request->getPost('name');
		$username	= $this->request->getPost('username');
		$email		= $this->request->getPost('email');
		$usergroup	= (int)$this->request->getPost('usergroup');	
		$userlang	= $this->request->getPost('userlang');
		$phone		= $this->request->getPost('phone');
		$pass1		= $this->request->getPost('pass1');
		$pass2		= $this->request->getPost('pass2');

		if ( !$this->Engine->validateName( $name ) ){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Name entered is invalid";
			die(json_encode($responseArray));
		}

		if ( !$this->Engine->validateUsername( $username ) ){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Username entered is invalid";
			die(json_encode($responseArray));
		}		

		if ( !$this->Engine->validateEmail( $email ) ) {
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Email entered is invalid";
			die(json_encode($responseArray));
		}

		if( $userlang == 0 || $userlang == 99){
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Language not selected";
			die(json_encode($responseArray));
		}

		if ( $this->Engine->isExistingUsername($username) ){		
			$responseArray["status"]	= "03";
			$responseArray["msg"]		= "Username already Exists";
			die(json_encode($responseArray));
		}

		if ( $this->Engine->isExistingUsername($email) ){		
			$responseArray["status"]	= "04";
			$responseArray["msg"]		= "Email already in use";
			die(json_encode($responseArray));
		}

		if( !$this->Engine->validatePhone($phone) ){
			$responseArray["status"]	= "05";
			$responseArray["msg"]		= "Invalid phone number entered";
			die(json_encode($responseArray));			
		}

		if ( !$this->Engine->isPassSecure($pass1) ){	
			$responseArray["status"]	= "06";
			$responseArray["msg"]		= "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";
			die(json_encode($responseArray));
		}

		if ( $pass1 != $pass2){	
			$responseArray["status"]	= "07";
			$responseArray["msg"]		= "Password Entered do not match";
			die(json_encode($responseArray));
		}

		$pass1 			= password_hash($pass1, 1);
		$pass2 			= password_hash($pass2, 1);

		$siku			= date("Y-m-d H:i:s");
		$dataUsers		= array("name" => $name, "username" => $username, "email" => $email, "password" => $pass1, "registerDate" => $siku);
		$dataMap		= array("group_id" => $usergroup);
		$dataProfile	= array("phone" => $phone , "lang_id" => $userlang, "created_by" => $this->userInfo->id);
		
		$saveStatus	= $this->Engine->saveUserDetails($dataUsers , $dataMap , $dataProfile );

		if ( $saveStatus === false) {
			$responseArray["status"]	= "09";
			$responseArray["msg"]		= "An error occured when saving record";
			die(json_encode($responseArray));
		}
		else{
			$responseArray["status"]	= "11";
			$responseArray["msg"]		= "Successfully registered new user";
			die(json_encode($responseArray));
		}

	}

	public function updateuserprofile(){
		$responseArray	= array();
		$dataLog		= array();
		$dataArchive	= array();

		if( isset($_POST['userid']) ){
			$user_id		= (int)$this->request->getPost('userid');
			$usergroup		= $this->request->getPost('usergroup');
			$s_user_id		= $this->userInfo->id;
			$dataUpdate		= array();
			$profUpdate		= array();

			$userData	= $this->Engine->fetchUserDetails($user_id);

			//PREPARE ACTIVITY LOG
			$dataArchive["old_data"] 	= $userData;
			$dataArchive["new_data"]	= json_encode($_POST);
			$dataLog["s_user_id"]		= $this->userInfo->id;
			$dataLog["item_id"]			= $user_id;
			$dataLog["item_type"]		= "1";
			$dataLog["archive_string"]	= json_encode($dataArchive);
			$dataLog["log_siku"]		= date("Y-m-d H:i:s");
			

			//Check if user exists
			if( !$this->Engine->isExistingUserId($user_id) ){
				$responseArray["status"]	= "02";
				$responseArray["msg"]		= "The user does not exist or has been deleted.";
				die(json_encode($responseArray));
			}

			$permObj		= $this->Engine->permitedChangeUserProfile($this , $user_id);			

			//Check permision for edit
			if(  $permObj->profChange ){

				if( $permObj->groupChange){
					//Change user Group
					$dataLog["act_type"]	= "1";

					if( !$this->Engine->updateUserGroup($user_id , $usergroup , $dataLog ) ){
						$responseArray["status"]	= "03";
						$responseArray["msg"]		= "A problem occured when updating user group";
						die(json_encode($responseArray));						
					}
				}

				if( $permObj->profChange ){
					$name			= $this->request->getPost('name');
					$username		= $this->request->getPost('username');
					$email			= $this->request->getPost('email');
					$phone			= $this->request->getPost('phone');
					$userlang		= $this->request->getPost('userlang');

					//Change user profile
					if($name != $userData->name){
						if ( !$this->Engine->validateName( $name ) ){
							$responseArray["status"]	= "01";
							$responseArray["msg"]		= "Name entered is invalid";
							die(json_encode($responseArray));
						}

						$dataUpdate['name'] = $name;
					}

					if($email != $userData->email && $permObj->emailChange){
						if ( !$this->Engine->validateEmail( $email ) ) {
							$responseArray["status"]	= "02";
							$responseArray["msg"]		= "Email entered is invalid";
							die(json_encode($responseArray));
						}
				
						if ( $this->Engine->isExistingUsername($email) ){		
							$responseArray["status"]	= "04";
							$responseArray["msg"]		= "Email already in use";
							die(json_encode($responseArray));
						}

						$dataUpdate['email'] = $email;
					}						

					if($username != $userData->username && $permObj->emailChange){
						if ( !$this->Engine->validateUsername( $username ) ){
							$responseArray["status"]	= "01";
							$responseArray["msg"]		= "Username entered is invalid";
							die(json_encode($responseArray));
						}

						if ( $this->Engine->isExistingUsername($username) ){		
							$responseArray["status"]	= "03";
							$responseArray["msg"]		= "Username already Exists";
							die(json_encode($responseArray));
						}

						$dataUpdate['username'] = $username;
					}

					if($phone != $userData->phone && $permObj->emailChange){
						if( !$this->Engine->validatePhone($phone) ){
							$responseArray["status"]	= "05";
							$responseArray["msg"]		= "Invalid phone number entered";
							die(json_encode($responseArray));			
						}

						$profUpdate['phone'] = $phone;
					}

					if($userlang != $userData->lang_id){
						if( !$this->Engine->isExistLanguage($userlang) ){
							$responseArray["status"]	= "06";
							$responseArray["msg"]		= "Invalid language selected";
							die(json_encode($responseArray));			
						}

						$profUpdate['lang_id'] = $userlang;
					}

					$dataLog["act_type"]	= "1";

					if( !$this->Engine->updateUserProfile($dataUpdate , $profUpdate , $user_id) ){
						$responseArray["status"]	= "04";
						$responseArray["msg"]		= "A problem occured when updating user";
						die(json_encode($responseArray));						
					}
				}

				//Global if no error both group and profile return successfull
				$responseArray["status"]	= "11";
				$responseArray["msg"]		= "Successfully updated user";
				$responseArray["redLink"] 	= base_url('dashboard/settings/'.$user_id);
				die(json_encode($responseArray));
			}
			else{
				$responseArray["status"]	= "01";
				$responseArray["msg"]		= "Not permited to perform the request";
				die(json_encode($responseArray));
			}			
		}
		else{
			$responseArray["status"]	= "00";
			$responseArray["msg"]		= "Unexpected request sent";
			die(json_encode($responseArray));
		}

	}

	public function updateuserpass(){
		$responseArray	= array();

		if( isset($_POST['userid']) ){
			$user_id		= $this->request->getPost('userid');
			$passold		= $this->request->getPost('passold');
			$pass1			= $this->request->getPost('pass1');
			$pass2			= $this->request->getPost('pass2');
			$s_user_id		= $this->userInfo->id;
			$passhash 		= $this->userInfo->password;

			//Check if user exists
			if( !$this->Engine->isExistingUserId($user_id) ){
				$responseArray["status"]	= "07";
				$responseArray["msg"]		= "The user does not exist or has been deleted.";
				die(json_encode($responseArray));
			}

			$permObj		= $this->Engine->permitedChangeUserProfile($this , $user_id);

			//Check permision for edit
			if(  ( $permObj->passChange ) ){
				if ( !$this->Engine->passAuth($passhash , $user_id , $passold) ){
					$responseArray["status"]	= "02";
					$responseArray["msg"]		= "Old password entered is not correct.";
					die(json_encode($responseArray));						
				}

				if ( !$this->Engine->isPassSecure($pass1) ){
					$responseArray["status"]	= "03";
					$responseArray["msg"]		= "Password should be at least 8 characters in length and should include at least one upper case letter, one number, and one special character.";
					die(json_encode($responseArray));					
				}				

				if($pass1 != $pass2){
					$responseArray["status"]	= "04";
					$responseArray["msg"]		= "Password Entered do not match";
					die(json_encode($responseArray));					
				}

				if ( $this->Engine->updateUserPassword($user_id , $pass1 ) ){
					$responseArray["status"]	= "11";
					$responseArray["msg"]		= "Successfull.";
					$requestResponse["redLink"] = base_url('dashboard/settings/'.$user_id);
					die(json_encode($responseArray));
					
				}
				else{
					$responseArray["status"]	= "05";
					$responseArray["msg"]		= "A problem was encountered while trying to update your password.";
					die(json_encode($responseArray));
				}

			}
			else{
				$responseArray["status"]	= "01";
				$responseArray["msg"]		= "Not permited to perform the request";
				die(json_encode($responseArray));
			}			
		}
		else{
			$responseArray["status"]	= "00";
			$responseArray["msg"]		= "Unexpected request sent";
			die(json_encode($responseArray));
		}

	}
	
}
