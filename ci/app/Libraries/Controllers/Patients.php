<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;

class Patients extends BaseController
{
	public function index(){
		$viewData	= array();
		$start		= 0;

		return view('Headers/header')
            . view('Headers/page_header')
			. view('Patients/patients' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');		
	}

	public function record($pat_id){
		$viewData	= array();
        $pat_id     = (int)$pat_id;

		$viewData["patRecArray"]  = get_instance()->Engine->fetchPatientRecord($pat_id);

		return view('Headers/header')
            . view('Headers/page_header')
			. view('Patients/record' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');		
	}        

	public function fetchpatform(){
		$viewData		= array();
		$viewData["mkoaArray"]  = fetchNewPatientForm($mkoa_id = 0 , $wilaya_id = 0)["mkoa"];
		$viewData["idTypeArr"]  = get_instance()->Engine->fetchIdTypes();

		return view('Patients/newPatientForm' , $viewData);
	}

	public function fetchLocations($mkoa_id = 0, $wilaya_id = 0){
		$mkoa_id 	= (int)$mkoa_id;
		$wilaya_id	= (int)$wilaya_id;

		$responseArray = fetchNewPatientForm($mkoa_id, $wilaya_id);

		die(json_encode($responseArray));
	}
    
    public function processnewpat(){
        $siku		= date("Y-m-d H:i:s");
        $sname		= $this->request->getPost('sname');
        $otnames	= $this->request->getPost('names');
        $patNum	    = $this->request->getPost('patNum');
        $phone	    = $this->request->getPost('phone');
        $patDob	    = $this->request->getPost('patDob');
        $kinName	= $this->request->getPost('kinName');
        $kinPhone	= $this->request->getPost('kinPhone');
        $idType	    = (int)$this->request->getPost('idType');
        $idNum	    = $this->request->getPost('idNum');
        $kata	    = $this->request->getPost('kataselect');

        $patDob = "1990-11-23";

		if ( !$this->Engine->validateName( $sname ) || !$this->Engine->validateName( $otnames )){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Name entered is invalid";
			die(json_encode($responseArray));
		}

		if( !$this->Engine->validatePhone($phone) ){
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Invalid Patient phone number entered";
			die(json_encode($responseArray));			
		}

        if(strlen($patNum) < 4){
			$responseArray["status"]	= "03";
			$responseArray["msg"]		= "Please enter a Valid Patient Number";
			die(json_encode($responseArray));	            
        }

        if( $this->Engine->isExistingPatientNumber($patNum) ){
			$responseArray["status"]	= "03b";
			$responseArray["msg"]		= "The Patient Number entered is already registered";
			die(json_encode($responseArray));	
        }

        if( !$this->Engine->validateDOB($patDob) ){
			$responseArray["status"]	= "04";
			$responseArray["msg"]		= "Please select a valid Birth Date";
			die(json_encode($responseArray));	
        }

		if ( !$this->Engine->validateName( $kinName ) ){
			$responseArray["status"]	= "05";
			$responseArray["msg"]		= "Kin Name Entered Is Invalid";
			die(json_encode($responseArray));
		}

		if( !$this->Engine->validatePhone($kinPhone) ){
			$responseArray["status"]	= "06";
			$responseArray["msg"]		= "Invalid Next of Kin phone number entered";
			die(json_encode($responseArray));			
		}

        if( $idType > 0){
            if( !$this->Engine->isExistingIdType($idType) ){
                $responseArray["status"]	= "07";
                $responseArray["msg"]		= "Please select Patient's ID Type"; 
                die(json_encode($responseArray));	
            }

            if( !$this->Engine->validateIDNum($idNum) ){
                $responseArray["status"]	= "08";
                $responseArray["msg"]		= "Please enter a Valid Patient ID";
                die(json_encode($responseArray));	
            }           
        }

        if( !$this->Engine->isExistingKata($kata) ){
			$responseArray["status"]	= "09";
			$responseArray["msg"]		= "Please select a valid Adress Location of the Patient";
			die(json_encode($responseArray));
        }

        $dataPatient	= array("pat_num" => $patNum, "sur_name" => $sname, "other_names" => $otnames, "dob" => $patDob, "phone" => $phone,
                                "next_of_kin_name" => $kinName, "next_of_kin_phone" => $kinPhone, "id_type" => $idType, "id_number" => $idNum, 
                                "loc_kata" => $kata, "last_updated" => $siku, "reg_date" => $siku);
                                
        $saveStatus	= $this->Engine->savePatientDetails($dataPatient);

        if ( $saveStatus === false) {
            $responseArray["status"]	= "09";
            $responseArray["msg"]		= "An error occured when saving record";
            die(json_encode($responseArray));
        }
        else{
            $responseArray["status"]	= "111";
            $responseArray["msg"]		= "Successfully registered new user";
            die(json_encode($responseArray));
        }                                

    }    

}