<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface; //ENABLES YOU TO USE UserFactoryInterface IN CONTAINER
use Joomla\CMS\User\UserHelper;

class Billing extends BaseController
{
    public function index(){
		return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_container')
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

    public function history(){
		return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_history')
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

    public function newbill($pat_id){
        $viewData	= array();
        $pat_id     = (int)$pat_id;

        $viewData["patRecArray"]  = get_instance()->Engine->fetchPatientRecord($pat_id);

		return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/newbill', $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');        
    }

    public function products(){
		return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_products')
			. view('Headers/page_footer')
            . view('Headers/footer');        
    }

    public function newprodform(){
		return view('billing/newprod_form');
    }

    public function searchword(){
        $sword		= $this->request->getPost('sword');
        $srchArr    = $this->Engine->getSearchProduct($sword);
        $viewData   = array("srchArr" => $srchArr );

        return view('billing/srch_prod_disp' , $viewData);
    }

    public function newprodprocess(){
        $siku		= date("Y-m-d H:i:s");
        $pType		= $this->request->getPost('prodType');
        $pName	    = $this->request->getPost('prodName');
        $pCost	    = $this->request->getPost('prodCost');
        $pDesc	    = $this->request->getPost('prodDesc');
        $user_id	= $this->userInfo->id;

        if( !$this->Engine->isProdtype($pType) ){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Invalid Product Type";
			die(json_encode($responseArray));            
        }

        if( !doValidateProdName($pName) ){
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Invalid Product Name";
			die(json_encode($responseArray));  
        }

        if( !doValidateProdCost($pCost) ){
			$responseArray["status"]	= "03";
			$responseArray["msg"]		= "Invalid Product Cost";
			die(json_encode($responseArray)); 
        }

        if( !$this->Engine->isExistingUserId($user_id) ){
			$responseArray["status"]	= "04";
			$responseArray["msg"]		= "No permission to perform this action";
			die(json_encode($responseArray));             
        }
        
        $data  = array("prod_name" => $pName, "prod_type_id" => $pType, "rec_by" => $user_id, "prod_desc" => $pDesc, "siku" => $siku);

        if ( !$this->Engine->saveProductDetails($data , $pCost) ) {
            $responseArray["status"]	= "09";
            $responseArray["msg"]		= "An error occured when saving record";
            die(json_encode($responseArray));
        }
        else{
            $responseArray["status"]	= "111";
            $responseArray["redLink"]	= base_url('billing/products');
            $responseArray["msg"]		= "Successfully Saved New Product";
            die(json_encode($responseArray));
        } 
    }

    public function postnewbill(){
        //var_dump($_POST);

        $pat_id		    = (int)$this->request->getPost('pat_id');
        $pay_term       = $this->request->getPost('pay-terms');
        $billCreate	    = $this->request->getPost('billCreateDate');
        $billExp	    = $this->request->getPost('billExpiryDate');
        $billDetails    = $this->request->getPost('bill_details');
        $bill_comments  = "";

        if( !$this->Engine->isExistingPatID($pat_id) ){
			$responseArray["status"]	= "01";
			$responseArray["msg"]		= "Invalid Patient Record";
			die(json_encode($responseArray));               
        }

        if( !$this->Engine->isExistingPayTerms($pay_term) ){
			$responseArray["status"]	= "02";
			$responseArray["msg"]		= "Invalid Payment Terms";
			die(json_encode($responseArray));
        }

        //echo $billDetails;

        if( !json_validator($billDetails) ){
			$responseArray["status"]	= "03";
			$responseArray["msg"]		= "Please add bill Items";
			die(json_encode($responseArray));
        }

        $billArray  = getValidBillItems($billDetails);

        if( sizeof($billArray) < 2){
			$responseArray["status"]	= "04";
			$responseArray["msg"]		= "Please add bill Items";
			die(json_encode($responseArray));            
        }

        $dataMap    = array("pat_id" => $pat_id, "bill_terms" => $pay_term, "bill_creation" => $billCreate , "bill_comments" => $bill_comments);

        if( strlen($billExp) > 1){
            $dataMap["bill_expiry"]   = $billExp;
        }

        if( !$this->Engine->saveBillDetails($dataMap , $billArray) ){
            $responseArray["status"]	= "09";
            $responseArray["msg"]		= "An error occured when saving record";
            die(json_encode($responseArray));
        }
        else{
            $responseArray["status"]	= "111";
            $responseArray["redLink"]	= base_url('patients/record/'.$pat_id);
            $responseArray["msg"]		= "Successfully Saved Bill";
            die(json_encode($responseArray));
        }
    }

    public function test($pat_id){
        $balrecArray    = get_instance()->Engine->calcPatientBalance($pat_id);

        echo get_instance()->Engine->retPatIdFromPatref( $pat_ref = "123456" );

        //var_dump( $balrecArray );
    }
}