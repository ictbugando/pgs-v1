<?php
$CI_INSTANCE = [];  # It keeps a ref to global CI instance

function register_ci_instance(\App\Controllers\BaseController &$_ci)
{
    global $CI_INSTANCE;
    $CI_INSTANCE[0] = &$_ci;
}


function get_instance(): \App\Controllers\BaseController
{
    global $CI_INSTANCE;
    return $CI_INSTANCE[0];
}

function isXml(string $value): bool
{
    $prev = libxml_use_internal_errors(true);

    $doc = simplexml_load_string($value);
    $errors = libxml_get_errors();

    libxml_clear_errors();
    libxml_use_internal_errors($prev);

    return false !== $doc && empty($errors);
}

function getXmlOrg(){
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if(isXml($xmlresponse)){
		$xmlresponse	= new \SimpleXMLElement($xmlresponse);
	}

	return json_encode($xmlresponse);	
}

function date_convert($my_date){
	$date = new DateTime($my_date, new DateTimeZone('Africa/Nairobi'));
	$date->setTimezone(new DateTimeZone('Africa/Nairobi'));

	$date = date('Y-m-d-W-H-i-s');
	$date =  explode("-",$date);
	$to_year  = $date[0];
	$to_month = $date[1];
	$to_day   = $date[2];
	$to_week  = $date[3];

	$use_date   = $my_date;

	$msg_date   = $my_date;
	$msg_date   = date("Y-m-d-W-H-i-s",strtotime($msg_date) );
	$msg_date   = explode("-",$msg_date);
	$msg_year  = $msg_date[0];
	$msg_month = $msg_date[1];
	$msg_day   = $msg_date[2];
	$msg_week  = $msg_date[3];

	if($msg_year == $to_year){
		if($msg_month == $to_month){
			 if($msg_day == $to_day){
				   $msg_date = new DateTime($use_date, new DateTimeZone('Africa/Dar_es_Salaam'));
				   $msg_date = $msg_date->setTimezone(new DateTimeZone('Africa/Dar_es_Salaam'));				 
				  // $msg_date   = date("g:i A",strtotime($msg_date) );
				   $msg_date = $msg_date->format('g:i A');
			  }
			 else if($msg_week == $to_week){
			   $msg_date = new DateTime($use_date, new DateTimeZone('Africa/Dar_es_Salaam'));
			   $msg_date = $msg_date->setTimezone(new DateTimeZone('Africa/Dar_es_Salaam'));				 
			   //$msg_date     = date("D g:i A",strtotime($msg_date) );
			   $msg_date = $msg_date->format('D g:i A');
			 }
			 else{
				   $msg_date = new DateTime($use_date, new DateTimeZone('Africa/Dar_es_Salaam'));
				   $msg_date = $msg_date->setTimezone(new DateTimeZone('Africa/Dar_es_Salaam'));
				   $msg_date = $msg_date->format('M j ');
				//$msg_date   = date("M j ",strtotime($msg_date) );
			  }
		}
		else{
		   $msg_date = new DateTime($use_date, new DateTimeZone('Africa/Dar_es_Salaam'));
		   $msg_date = $msg_date->setTimezone(new DateTimeZone('Africa/Dar_es_Salaam'));	
		   $msg_date = $msg_date->format('M j ');			   
		   //$msg_date   = date("M j ",strtotime($msg_date) );
		}
	}
	else{
	   $msg_date = new DateTime($use_date, new DateTimeZone('Africa/Dar_es_Salaam'));
	   $msg_date = $msg_date->setTimezone(new DateTimeZone('Africa/Dar_es_Salaam'));
	   $msg_date = $msg_date->format('M j Y');
	   //$msg_date   = date("M j Y",strtotime($msg_date) );
	}	
	return $msg_date;
}

function getMweziId($date){
	return get_instance()->Engine->retMweziID($date);
}

function fetchNewPatientForm($mkoa_id, $wilaya_id){
	$CI			= get_instance();
	$resArray	= array();

	$resArray["mkoa"]	= $CI->Engine->fetchMkoaList();
	$resArray["wilaya"]	= $CI->Engine->fetcMkoaWilayaList($mkoa_id);
	$resArray["kata"]	= $CI->Engine->fetcWilayaKataList($wilaya_id);

	return $resArray;
}

function genRequestAiValidate($dataValues){
	ob_start();
		require("xml/aivalidate.php");
	return ob_get_clean();
}

function getRefFromRequest($xmlresponse){
	if( isset($xmlresponse->REFERENCE) ){
		return (string)$xmlresponse->REFERENCE;
	}

	return 0;
}

function getAmountFromRequest($xmlresponse){
	if( isset($xmlresponse->AMOUNT) ){
		return (string)$xmlresponse->AMOUNT;
	}

	return 0;
}

function getReceiptFromRequest($xmlresponse){
	if( isset($xmlresponse->TXNID) ){
		return (string)$xmlresponse->TXNID;
	}

	return 0;
}


function getBillDetailsFromRequest($xmlresponse){
	if( isset($xmlresponse->CUSTOMERREF) && isset($xmlresponse->CUSTOMERMSISDN)){
		return array( "ref" => (string)$xmlresponse->CUSTOMERREF , "msisdn" => (string)$xmlresponse->CUSTOMERMSISDN) ;
	}

	return array();
}

function doValidatePermAiApi(){
	$ipAdress	= get_instance()->ipAddress;
	$ip1st4		= substr($ipAdress, 0, 4);
	$ipAdress	= str_replace(".","",$ipAdress);

	if( ($ipAdress == '4121720347') || ($ipAdress == '172273472') || ($ipAdress == '127001') || 
		   ($ipAdress == '1') || ($ipAdress == '1017021') || ($ipAdress == '154741323') || ($ipAdress == '::1') || ($ip1st4 == '13.2') 
		    || ($ip1st4 == '41.7') ){
		return TRUE;
	}
	
	return FALSE;
}

function saveLogs($wallet_id , $response){
	$CI			= get_instance();
	$ipAdress	= $CI->ipAddress;
	$xmlOrg 	= getXmlOrg();
	$dataLog	= array("wallet_id" => $wallet_id, "org_data" => $xmlOrg, "final_data" => $response, 
						"req_ip" => $ipAdress, "siku" => date('Y-m-d H:i:s') );

	$CI->Engine->doSaveGwayLogs($dataLog);
}

function getAiDataArr($xmlresponse){
	$newArr	= array();

	if(isset($xmlresponse->REFERENCE1)){
		$newArr["receipt"]		= (string)$xmlresponse->REFERENCE1;
	}

	if(isset($xmlresponse->AMOUNT)){
		$newArr["amount"]		= (int)$xmlresponse->AMOUNT;
	}
	
	if(isset($xmlresponse->CUSTOMERMSISDN)){
		$newArr["msisdn"]		= (string)$xmlresponse->CUSTOMERMSISDN;
	}
	
	if(isset($xmlresponse->REFERENCE)){
		$newArr["reference"]		= (string)$xmlresponse->REFERENCE;
	}

	return $newArr;
}

function isValidAiDataArr($newArr){
	if( isset($newArr["receipt"]) && isset($newArr["amount"]) && isset($newArr["msisdn"]) && isset($newArr["reference"]) ){
		return TRUE;
	}
	
	return FALSE;
}

function isValidAmount($amount){
	if( ($amount >= 1000) && ($amount < 5000000) ){
		return TRUE;
	}

	return FALSE;
}

function dispPermDeniedAi(){
	return array("status" => "400" , "msg" => "Permision Not Allowed");
}

function doProcessValidation($xmlresponse){
	$CI				= get_instance();
	$dataValues		= array("status" => "400" , "msg" => "Invalid Reference");
	$xmlresponse	= new SimpleXMLElement($xmlresponse);

	if( !isValidAmount( getAmountFromRequest($xmlresponse) ) ){
		$dataValues		= array("status" => "400" , "msg" => "Invalid Amount");
	}
	else if( $CI->Engine->doValidateReference( getRefFromRequest($xmlresponse) ) ){
		$dataValues     = array("status" => "200" , "msg" => "Success");
	}


	return $dataValues;
} 

function doProcessAITransaction($xmlresponse){	
	$CI				= get_instance();
	$xmlresponse	= new SimpleXMLElement($xmlresponse);
	$reqDataArr		= getAiDataArr($xmlresponse);
	$xmlOrg 		= getXmlOrg();
	$dataValues     = array("status" => "400" , "msg" => "Could not process Transaction");
	$trans_sp		= "3";
	$pat_id			= get_instance()->Engine->retPatIdFromPatref( $reqDataArr["reference"] );
          
	if( isValidAiDataArr($reqDataArr) ){
		
		if( $CI->Engine->doValidateReference($reqDataArr["reference"]) ){
			 
			if( isValidAmount($reqDataArr["amount"]) ){
				
				if( $CI->Engine->saveTransData($trans_sp , $xmlOrg , $reqDataArr) ){
					$dataValues     = array("status" => "200" , "msg" => "Success");
					get_instance()->Engine->calcPatientBalance($pat_id); //Update balance
				}
				else{
					$dataValues     = array("status" => "400" , "msg" => "Transaction Failed");
				}
			}
			else{
				$dataValues     = array("status" => "400" , "msg" => "Invalid Amount");
			}
		}
		else{
			$dataValues     = array("status" => "400" , "msg" => "Invalid Reference");
		}

		$dataValues["txnId"] = $reqDataArr["receipt"];
		
	}
	else{
		$dataValues     = array("status" => "400" , "msg" => "Invalid Request");
	}

	return $dataValues;
}

function doAipros1Validate(){
	ob_start();
	$permitAccess	= doValidatePermAiApi();
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess && (strlen($xmlresponse) > 1) ){
		$dataValues = doProcessValidation($xmlresponse);
	}
	else{
		$dataValues = dispPermDeniedAi();
	}

	$respValidate   = genRequestAiValidate($dataValues);

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros2Process(){
	//ob_start();
	$permitAccess	= doValidatePermAiApi();
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess && (strlen($xmlresponse) > 1) ){
		$dataValues = doProcessAITransaction($xmlresponse);
	}
	else{
		$dataValues = dispPermDeniedAi();
	}
	
	$respValidate   = genRequestAiValidate($dataValues);

	echo $respValidate;

	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);
}

function doAipros3Query(){
	ob_start();
	$CI				= get_instance();
	$permitAccess	= doValidatePermAiApi();
	$xmlresponse	= trim(file_get_contents('php://input'));
	$xmlresponse	= new SimpleXMLElement($xmlresponse);
	
	if ( $permitAccess && (strlen($xmlresponse) > 1) ){
		$txnId      = getReceiptFromRequest($xmlresponse);

		if( $CI->Engine->isValidTxn($txnId) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "ref" => $txnId);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Transaction does not exist" , "ref" => $txnId);
		}

		$respValidate   = genRequestAiValidate($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = genRequestAiValidate($dataValues);
	}

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros4Fetch(){
	ob_start();
	$CI				= get_instance();
	$permitAccess	= doValidatePermAiApi();
	$xmlresponse	= trim(file_get_contents('php://input'));
	$xmlresponse	= new SimpleXMLElement($xmlresponse);
	
	if ( $permitAccess && (strlen($xmlresponse) > 1) ){
		$billFetchArray		= getBillDetailsFromRequest($xmlresponse);
		$fetchedBillArray	= $CI->Engine->getBillFetchDetails($billFetchArray);
		
		if( isset($fetchedBillArray->bill_num) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "billar" => $fetchedBillArray);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Bill does not exist");
		}

		$respValidate   = genRequestAiValidate($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = genRequestAiValidate($dataValues);
	}

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros5Lookup(){
	ob_start();
	$CI				= get_instance();
	$permitAccess	= doValidatePermAiApi();
	$xmlresponse	= trim(file_get_contents('php://input'));
	$xmlresponse	= new SimpleXMLElement($xmlresponse);
	
	if ( $permitAccess && (strlen($xmlresponse) > 1) ){
		$billFetchArray		= getBillDetailsFromRequest($xmlresponse);
		$fetchedBillArray	= $CI->Engine->getBillFetchDetails($billFetchArray);
		
		if( isset($fetchedBillArray->bill_num) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "lookArr" => $fetchedBillArray);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Bill does not exist");
		}

		$respValidate   = genRequestAiValidate($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = genRequestAiValidate($dataValues);
	}

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doValidateProdName($prodName){
	if( strlen($prodName) > 3 && strlen($prodName) < 50){
		return TRUE;
	}

	return FALSE;
}

function doValidateProdCost($prodCost) {
	if( $prodCost > 500 && $prodCost < 5000000){
		return TRUE;
	}

	return FALSE;
}

function getTaxDropDown($prod_id){
	ob_start();
		require("html/tax_drop_down.php");
	return ob_get_clean();
}

function getDiscountInput($prod_id){
	ob_start();
		require("html/discount_input.php");
	return ob_get_clean();
}

function getCostInput($cost_amt , $prod_id){
	ob_start();
		require("html/cost_input.php");
	return ob_get_clean();
}

function getCountInput($prod_id){
	ob_start();
		require("html/count_input.php");
	return ob_get_clean();
}

function getTotalInput($cost_amt , $prod_id){
	ob_start();
		require("html/total_input.php");
	return ob_get_clean();
}

function getDeleteHtmlt($prod_id){
	ob_start();
		require("html/delete_bill_item.php");
	return ob_get_clean();
}

function json_validator($data) {
	if (!empty($data)) {
		return is_string($data) && 
		  is_array(json_decode($data, true)) ? true : false;
	}
	return false;
}

function getValidBillItems($json_data){
	$json_arr		= json_decode($json_data , 1);
	$billDetArray	= array();
	$grandTotal		= 0;

	foreach($json_arr AS $arr){
		if(is_array($arr) ){
			if( isset($arr["id"]) && isset($arr["qty"]) && isset($arr["cost"]) && isset($arr["tax"]) && isset($arr["discount"]) && 
				isset($arr["total"]) ){

				$cost	= $arr["cost"];
				$qty	= $arr["qty"];
				$tax	= $arr["tax"];
				$disc	= $arr["discount"];

				$total	= ($cost*$qty);

				if( ($disc > 0) && ($disc < 101) ){
					$total = ($total - ($total*($disc/100)) );
				}	
			
				if( ($tax > 0) && ($tax < 101) ){
					$total = ($total + ($total*($tax/100)) );
				}

				$grandTotal		= $grandTotal+$total;

				$arr["total"]	= $total;

				$billDetArray[]	= $arr;
			}
		}
	}

	$billDetArray["grandTotal"]	= $grandTotal;

	return $billDetArray;
}

function checkBalValidity($balrecArray){
	$amount		= $balrecArray->bal_amt;
	$timestamp	= $balrecArray->bal_timestamp;
	$hash		= $balrecArray->bal_hash;

	$hashText	= $amount.$timestamp;
	$myHash		= hash('sha256', $hashText);

	if( $myHash	 == $hash){
		return TRUE;
	}

	return FALSE;
}

function returnMyHash( $amount , $timestamp ){
	$hashText	= $amount.$timestamp;
	$myHash		= hash('sha256', $hashText);

	return $myHash;
}

function getBillNumber(){
	$lastid		= get_instance()->Engine->getLastBillNumber();
	$newId		= "0";
	$isValid	= FALSE;

	while($isValid == FALSE){
		$newId		= genBillNumber($lastid);
		$isValid	= get_instance()->Engine->isValidNewBillNumber($newId);
	}

	return $newId;
}

function genBillNumber($lastid){
	if( $lastid	== "0"){
		$number = "E-0000001";
	}
	else{
		$idd	= str_replace("E-", "", $lastid);
		$id		= str_pad($idd + 1, 7, 0, STR_PAD_LEFT);
		$number	= 'E-'.$id;
	}

	return $number;
}