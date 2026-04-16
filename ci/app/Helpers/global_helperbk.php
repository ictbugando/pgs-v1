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

function cleanTxStrings($string){
	$string = str_replace(' ', '-', $string); // Replaces all spaces with hyphens.
	$string = preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
 
	return preg_replace('/-+/', '-', $string); // Replaces multiple hyphens with single one.
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

function getStatusCodeCRDB($code){
	if($code == "200"){
		return "200"; //Success
	}
	else if($code == "402"){
		return "6"; //Invalid amount
	}
	else if($code == "403"){
		return "204"; //Invalid reference
	}

	return "201"; //Inalid request
}

function getStatusCodeNMB($code){
	if($code == "200"){
		return "0";
	}
	else if($code == "402"){
		return "6";
	}
	else if($code == "403"){
		return "3";
	}

	return "5";
}

function dispAirtelResponse($dataValues){
	ob_start();
		require("xml/aivalidate.php");
	return ob_get_clean();
}

function dispResponseCRDBValidate($dataValues , $flag){
	$retJsonArray	= array("status" => $dataValues["status"], "statusDesc" => $dataValues["msg"] , "data" => $dataValues["data"]);
	
	if( $flag == "1" && isset($dataValues["data"]) ){
		$dataArr	= $dataValues["data"];
		
		$reference	= 'BMH'.$dataArr->reference;
		$txType		= $dataArr->typ;
		$amountType	= "FIXED";
		
		if($txType != "cnum")
			$amountType = "FLEXIBLE";

		$retJsonArray["data"]	= array("payerName" => $dataArr->sur_name." ".$dataArr->other_names, "amount" => $dataArr->bill_amount,  
										"amountType" => $amountType, "currency" => "TZS", "paymentReference" => $reference,  
										"paymentType" => "1122", "paymentDesc" => "Medica Bill Payment");
	}
	
	return json_encode($retJsonArray);
}

function dispResponseNMBInitiator($dataValues , $flag){
	ob_start();
		require("xml/initiatorXmlNMB.php");
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

function doCheckPermMalipoApi( $wallet_id ){
	$ipAdress	= get_instance()->ipAddress;
	$ip1st4		= substr($ipAdress, 0, 4);
	$ipAdress	= str_replace(".","",$ipAdress);

	if( $wallet_id == "1" || $wallet_id == "2" || $wallet_id == "3" ){
		//FOR XML REQUESTS
		$xmlresponse	= trim(file_get_contents('php://input'));

		if( !(strlen($xmlresponse) > 1) ){
			return FALSE; // Invalid XML Data
		}
	}
	else{
		//FOR JSON REQUESTS | CRDB | NMB
		$jsonData     = trim(file_get_contents('php://input'));

		if( !(strlen($jsonData) > 1) || !(json_validator($jsonData)) ){
			//return FALSE; // Invalid JSON Data
		}
	}

	//Allow local ip for test
	if( ($ipAdress == '127001') || ($ipAdress == '1') || ($ipAdress == '154741323') || ($ipAdress == '::1') ){
		return TRUE;
	}	

	if( $wallet_id == "1"){
		//For M-Pesa Whitelisted IPs
	}
	else if( $wallet_id == "2"){
		//For Tigo Pesa Whitelisted IPs
	}
	else if( $wallet_id == "3"){
		//For Airtel Whitelisted IPs
		if(  ($ipAdress == '4121720347') || ($ipAdress == '172273472') || ($ipAdress == '127001') || ($ip1st4 == '13.2') || ($ip1st4 == '41.7') ){
			return TRUE;
		}
	}
	else if( $wallet_id == "4"){
		//For CRDB Whitelisted IPs
		return TRUE; // Allow all CRDB Access for Test only
	}
	else if( $wallet_id == "5"){
		//For NMB Whitelisted IPs.
		return TRUE; // Allow all NMB Access for Test only
	}
	
	return TRUE;
	//return FALSE;
}

function saveLogs($wallet_id , $response){
	$orgData 	= getXmlOrg();
	saveAllLogs($wallet_id , $orgData , $response);
}

function saveApiLogs($response){
	$wallet_id	= "1";
	$orgData	= trim(file_get_contents('php://input'));

	return saveAllLogs($wallet_id , $orgData , $response );
}

function saveAllLogs($wallet_id , $orgData , $response){
	$CI			= get_instance();
	$ipAdress	= $CI->ipAddress;
	$uri 		= current_url(true);

	$dataLog	= array("wallet_id" => $wallet_id, "org_data" => $orgData, "final_data" => $response, 
						"req_url" => $uri, "req_ip" => $ipAdress, "siku" => date('Y-m-d H:i:s') );

	return $CI->Engine->doSaveGwayLogs($dataLog);
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

function getCRDBDataArr($jsonData){
	$newArr	= array();

	$newArr["msisdn"] = "";

	if( json_validator($jsonData) ){
		$jsonArray		= json_decode($jsonData , 1);
		
		if( isset($jsonArray["transactionRef"]) ){
			$newArr["receipt"]	= $jsonArray["transactionRef"];
		}

		if( isset($jsonArray["amount"]) ){
			$newArr["amount"]	= $jsonArray["amount"];
		}

		if( isset($jsonArray["paymentReference"]) ){
			$reference = $jsonArray["paymentReference"];
			$reference = ltrim($reference, 'BMH');

			$newArr["reference"]	= $reference;
		}
	}
	
	return $newArr;
}

function getNMBDataArr($jsonData){
	$newArr	= array();

	$newArr["msisdn"] = "";

	if( json_validator($jsonData) ){
		$jsonArray		= json_decode($jsonData , 1);
		
		if( isset($jsonArray["receipt"]) ){
			$newArr["receipt"]	= $jsonArray["receipt"];
		}

		if( isset($jsonArray["amount"]) ){
			$newArr["amount"]	= $jsonArray["amount"];
		}

		if( isset($jsonArray["reference"]) ){
			$reference = $jsonArray["reference"];
			$reference	= ( substr($reference , 0 , 6) == 'SAS169') ? substr($reference, 6) : $reference;

			$newArr["reference"]	= $reference;
		}
	}
	
	return $newArr;
}

function isValidDataArr($newArr){
	if( isset($newArr["receipt"]) && isset($newArr["amount"]) && isset($newArr["msisdn"]) && isset($newArr["reference"]) ){
		return TRUE;
	}
	
	return FALSE;
}

function isValidCrdbDataArr($newArr){
	if( isset($newArr["paymentReference"]) && isset($newArr["token"]) && isset($newArr["checksum"]) && isset($newArr["institutionID"]) ){
		return TRUE;
	}
	
	return FALSE;
}

function isValidAmount($amount){
	if( ($amount >= 1) && ($amount < 10000000) ){
		return TRUE;
	}

	return FALSE;
}

function isValidAmountForControl($billNum , $amount , $billAmount , $receiptType){
	$CI	= get_instance();

	if( ($amount >= 1) && ($amount < 10000000) ){
		if( ($receiptType == "cnum") && ($amount != $billAmount)){
			return FALSE;
			//Temporary hack update bill total to amount paid
			//$CI->Engine->doSaveTemporaryNewTotal($billNum , $amount);

			//return TRUE;
			//End Temporary
		}
		else{
			return TRUE;
		}
	}

	return FALSE;
}

function dispPermDeniedAi(){
	return array("status" => "400" , "msg" => "Permision Not Allowed");
}

function dispPermDeniedCrdb(){
	return array("status" => "201" , "msg" => "Permision Not Allowed");
}

function doAirtelValidation($xmlresponse){
	$CI				= get_instance();
	$dataValues		= array("status" => "400" , "msg" => "Invalid Reference");

	if( !isValidAmount( getAmountFromRequest($xmlresponse) ) ){
		$dataValues		= array("status" => "400" , "msg" => "Invalid Amount");
	}
	else if( $CI->Engine->doValidateReference( getRefFromRequest($xmlresponse) ) ){
		$dataValues     = array("status" => "200" , "msg" => "Success");
	}

	return $dataValues;
}

function doCRDBValidation($jsonData){
	$CI				= get_instance();
	$reference		= 0;

	if( json_validator($jsonData) ){
		$jsonArray		= json_decode($jsonData , 1);
		
		if( isset($jsonArray["paymentReference"]) ){
			$reference	= $jsonArray["paymentReference"];
			$reference = ltrim($reference, 'BMH');
		}
	}

	$retBillArr     = get_instance()->Engine->getBillDetailsForMalipoValidation( $reference );

	return $retBillArr;
}

function doNMBValidation($xmlresponse){
	$CI				= get_instance();
	$reference		= 0;
	$token			= "";

	if( isset($xmlresponse->reference) ){
		$reference	= (string)$xmlresponse->reference;
	}

	if( isset($xmlresponse->token) ){
		$token		= (string)$xmlresponse->token;
	}

	$retBillArr     		= get_instance()->Engine->getBillDetailsForMalipoValidation( $reference );
	$retBillArr["token"]	= $token;

	return $retBillArr;
}

function doProcessAITransaction($xmlresponse){
	$CI				= get_instance();
	$reqDataArr		= getAiDataArr($xmlresponse);
	$trans_sp		= "3";

	return  doProcessGlobalTransaction($trans_sp , $reqDataArr);
}

function doProcessCRDBTransaction($jsonData){
	$CI				= get_instance();
	$reqDataArr		= getCRDBDataArr($jsonData);
	$trans_sp		= "4";

	return doProcessGlobalTransaction($trans_sp , $reqDataArr);
}

function doProcessNMBTransaction($jsonData){
	$CI				= get_instance();
	$reqDataArr		= getNMBDataArr($jsonData);
	$trans_sp		= "5";

	return doProcessGlobalTransaction($trans_sp , $reqDataArr);
}

function doProcessGlobalTransaction($trans_sp , $reqDataArr){
	
	$CI				= get_instance();
	$xmlOrg 		= getXmlOrg();
	$dataValues     = array("status" => "400" , "msg" => "Could not process Transaction");
	$pat_id			= 0;
	$receiptType	= "";

	$reqDataArr["amount"] = (int)$reqDataArr["amount"]; //Remove decimals
	
	if( isValidDataArr($reqDataArr) ){
		$pat_id			= get_instance()->Engine->retPatIdFromPatref( $reqDataArr["reference"] );
		$resObject		= get_instance()->Engine->getBillDetailsForMalipoValidation($reqDataArr["reference"]);
		$receiptType	= $resObject->typ;
		$xBillNum		= $resObject->bill_num;
		
		$reqDataArr["payType"]	= $receiptType == "wallet" ? "2" : "1";
		
		if( isset($resObject->bill_amount) ){
						
			if( isValidAmountForControl($xBillNum , $reqDataArr["amount"] , $resObject->bill_amount , $receiptType) ){

				$saveRespArr	= $CI->Engine->saveTransData($pat_id, $trans_sp , $xmlOrg , $reqDataArr);
				
				if( $saveRespArr["status"] == TRUE){
					$dataValues     = array("status" => "200" , "msg" => $saveRespArr["msg"] , "data" => $saveRespArr["data"]);					
				}
				else{
					$dataValues     = array("status" => "401" , "msg" => $saveRespArr["msg"]); // 400
				}
			}
			else{
				$dataValues     = array("status" => "402" , "msg" => "Invalid Amount"); // 400
			}
		}
		else{
			$dataValues     = array("status" => "403" , "msg" => "Invalid Reference"); // 400
		}

		$dataValues["txnId"] = $reqDataArr["receipt"];
		
	}
	else{
		$dataValues     = array("status" => "400" , "msg" => "Invalid Request"); // 400
	}
	
	if($dataValues["status"] == "200" && $dataValues["msg"] != "Duplicate"){
		get_instance()->Engine->calcPatientBalance($pat_id); //Update balance
		
		$globBillId = get_instance()->Engine->getGlobalBillId( $reqDataArr["reference"] );
		
		if($receiptType == "cnum"){
			get_instance()->Engine->prosPatientUnpaidBills($pat_id ,$globBillId ); //Process bill
		}		
	}
	
	return $dataValues;
}

function doAipros1Validate(){
	ob_start();
	$permitAccess   = doCheckPermMalipoApi( "3" );
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess ){
		$xmlresponse	= new SimpleXMLElement($xmlresponse);
		$dataValues 	= doAirtelValidation($xmlresponse);
	}
	else{
		$dataValues = dispPermDeniedAi();
	}

	$respValidate   = dispAirtelResponse($dataValues);

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros2Process(){
	ob_start();
	$permitAccess   = doCheckPermMalipoApi( "3" );
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess ){
		$xmlresponse	= new SimpleXMLElement($xmlresponse);
		$dataValues		= doProcessAITransaction($xmlresponse);
	}
	else{
		$dataValues = dispPermDeniedAi();
	}
	
	$respValidate   = dispAirtelResponse($dataValues);

	echo $respValidate;

	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);
}

function doAipros3Query(){
	ob_start();
	$CI				= get_instance();
	$permitAccess   = doCheckPermMalipoApi( "3" );
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess ){
		$xmlresponse	= new SimpleXMLElement($xmlresponse);
		$txnId      	= getReceiptFromRequest($xmlresponse);

		if( $CI->Engine->isValidTxn($txnId) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "ref" => $txnId);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Transaction does not exist" , "ref" => $txnId);
		}

		$respValidate   = dispAirtelResponse($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = dispAirtelResponse($dataValues);
	}

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros4Fetch(){
	ob_start();
	$CI				= get_instance();
	$permitAccess   = doCheckPermMalipoApi( "3" );
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess ){
		$xmlresponse		= new SimpleXMLElement($xmlresponse);
		$billFetchArray		= getBillDetailsFromRequest($xmlresponse);
		$fetchedBillArray	= $CI->Engine->getBillFetchDetails($billFetchArray);
		
		if( isset($fetchedBillArray->bill_num) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "billar" => $fetchedBillArray);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Bill does not exist");
		}

		$respValidate   = dispAirtelResponse($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = dispAirtelResponse($dataValues);
	}

	echo $respValidate;
	
	$response	= ob_get_clean();
	
	saveLogs("3" , $response);

	die($response);	
}

function doAipros5Lookup(){
	ob_start();
	$CI				= get_instance();
	$permitAccess   = doCheckPermMalipoApi( "3" );
	$xmlresponse	= trim(file_get_contents('php://input'));
	
	if ( $permitAccess ){
		$xmlresponse		= new SimpleXMLElement($xmlresponse);
		$billFetchArray		= getBillDetailsFromRequest($xmlresponse);
		$fetchedBillArray	= $CI->Engine->getBillFetchDetails($billFetchArray);
		
		if( isset($fetchedBillArray->bill_num) ){
			$dataValues     = array("status" => "200" , "msg" => "Success" , "lookArr" => $fetchedBillArray);
		}
		else{
			$dataValues     = array("status" => "404" , "msg" => "Bill does not exist");
		}

		$respValidate   = dispAirtelResponse($dataValues);
	}
	else{
		$dataValues = dispPermDeniedAi();
		$respValidate   = dispAirtelResponse($dataValues);
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
	if( $prodCost > 50 && $prodCost < 10000000){
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
	$lastUpdate = $balrecArray->bal_amt;
	
	$myHash		= returnMyHash($amount , $timestamp);

	if( $myHash	 == $hash){
		return TRUE;
	}

	return FALSE;
}

function returnMyHash( $amount , $timestamp ){
	return hash('sha256', ($amount.$timestamp));
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
		$number = "E0000001";
	}
	else{
		$idd	= str_replace("E", "", $lastid);
		$id		= str_pad($idd + 1, 14, 0, STR_PAD_LEFT);
		$number	= 'E'.$id;
	}

	return $number;
}

function nmbGetToken(){
	$token      	= "0";
	$url            = NMBLIVEURL."/v2/auth";
	$myJsonData		= json_encode( array("username" => NMBUSERNAME, "password" => NMBPASSWORD) );
	$sXML			= get_instance()->Engine->doCurljson($url , $myJsonData);

	if( $sXML["error1"] == "" && $sXML["error2"] == "" ){
		$tokenArr = json_decode($sXML["data"] , 1);

		if( $tokenArr["status"] == "1"){
			$token = $tokenArr["token"];
		}
	}

	return $token;
}

function nmbBillSubmit($token , $resObject){
	$status             = "0";
	$retArray			= array();
	$invoiceJsonData	= nmbGenSubmitJson($token , $resObject);

	$url        = NMBLIVEURL."/v2/invoice_submission";
	$sXML       = get_instance()->Engine->doCurljson($url , $invoiceJsonData);

	//var_dump($sXML);

	if( $sXML["error1"] == "" && $sXML["error2"] == "" ){
		$retArray	= json_decode($sXML["data"] , 1);

		$status             = $retArray['status'];
		$description        = $retArray['description'];
	}

	return $retArray;
}

function nmbBillUpdate($token , $resObject){
	$status             = "0";
	$retArray			= array();
	$invoiceJsonData	= nmbGenSubmitJson($token , $resObject);

	$url		= NMBLIVEURL."/v2/invoice_update";
	$sXML       = get_instance()->Engine->doCurljson($url , $invoiceJsonData);

	//var_dump( $sXML );

	if( $sXML["error1"] == "" && $sXML["error2"] == "" ){
		$retArray	= json_decode($sXML["data"] , 1);

		$status             = $retArray['status'];
		$description        = $retArray['description'];
	}
	
	return $retArray;
}

function nmbBillCancel($reference){
	$url		= NMBLIVEURL."/v2/invoice_cancel";
	$token		= nmbGetToken(); //Generate Access Token Service
	$reference	= "SAS169".$reference;

	$invCancelArray         = array("reference" => $reference, "token" => $token);
	$invCancelJsonData      = json_encode( $invCancelArray );

	$sXML   = get_instance()->Engine->doCurljson($url , $invCancelJsonData);

	//var_dump( $sXML );
}

function nmbGenSubmitJson($token , $resObject){
	$reference      = "SAS169".$resObject->reference;
	$reference      = "SAS169".$resObject->bill_num;

	$patient_name   = $resObject->sur_name." ".$resObject->other_names;
	$patient_num    = $resObject->pat_num;
	$billed_amount  = $resObject->bill_amount;
	$callback_url   = "https://bmc.lipaswitch.co.tz/malipo/nmbprocesscallback";

	$invoiceArray   = array("status" => "1" , "description" => "Success" , "reference" => $reference, "customer_name" => $patient_name, "customer_id" => $patient_num, 
							"amount" => $billed_amount, "type" => "Medical Service", "code" => "10", "allow_partial" => "true", 
							"callback_url" => $callback_url, "token" => $token );
	
	$invoiceJsonData	= json_encode( $invoiceArray );

	return $invoiceJsonData;
}

function nmbTransReconcile(){
	$url		= NMBLIVEURL."/v2/reconcilliation";
	$token		= nmbGetToken(); //Generate Access Token Service
	$recDate	= "13-10-2023";

	$transReconArray         = array("reconcile_date" => $recDate, "token" => $token);
	$transReconJsonData      = json_encode( $transReconArray );

	$sXML   = get_instance()->Engine->doCurljson($url , $transReconJsonData);

	//var_dump( $sXML );
}

function validateApiPayResponseData($requestData){
	if( isset($requestData['ResponseHash']) &&  isset($requestData['ResponseKey']) && isset($requestData['BillNumber'])  && isset($requestData['StatusCode']) ){
		return TRUE;
	}

	return FALSE;
}

function validateApiBillData($requestData){
	if( isset($requestData['RequestTimestamp']) &&  isset($requestData['RequestKey']) && isset($requestData['RequestHash'])  && isset($requestData['Bill']) ){
		$reqBill    = $requestData['Bill'];
		if( isset($reqBill['BillNumber']) && isset($reqBill['PatientId']) && isset($reqBill['PatientName']) && 
			isset($reqBill['PatientPhone']) && isset($reqBill['BillTotal']) && isset($reqBill['AmountPaid']) &&
			isset($reqBill['BillItems']) ){
				foreach($reqBill['BillItems'] AS $itemArr){
					if( !isset($itemArr['ItemId']) || !isset($itemArr['ItemCost']) || !isset($itemArr['ItemTax']) ||
						!isset($itemArr['ItemDiscount']) || !isset($itemArr['ItemQty']) ){
							return FALSE; //Data sent is invalid
					}
				}

				return TRUE; //Data sent is valid
		}
	}

	return FALSE;
}

function getApiKeyHashPair(){
	$password	= "Bugando24#@!";
	$requestKey	= md5(rand());

	$reqHash 	= hash( 'sha256', md5( ($requestKey.$password) ) );

	$keyHassArr = array("key" => $requestKey, "hash" => $reqHash);

	return $keyHassArr;
}

function verifyApiKeyHashPair($key , $hash){
	$password	= "Bugando24#@!";
	$reqHash 	= hash( 'sha256', md5( ($key.$password) ) );
	
	if($reqHash == $hash){
		return TRUE;
	}

	return FALSE;
}

function genApiBillPostResponse($status, $msg , $newBillArr){
	$hashArr    = getApiKeyHashPair();
	$control	= "";
	$billNum	= "";

	if(isset($newBillArr->bill_num)){
		$control = $newBillArr->bill_num;
		$billNum = $newBillArr->control_num;
	}

	$responseArr = array(
		"ResponseCode" => "SEND_BILL_RESPONSE",
		"ResponseTimestamp" => date("Y-m-d H:i:s"),
		"ResponseKey" => $hashArr["key"],
		"ResponseHash" => $hashArr["hash"],
		"ControlNumber" => $control,
		"BillNumber" => $billNum,
		"StatusCode" => $status,
		"StatusMsg" => $msg,
	);

	return json_encode( $responseArr );
}

function getAccessTransactionMenu(){
	$allowedGroupsArray	= array("8","10","11","12","13","14","15");

	if( sizeof(array_intersect(get_instance()->groupsArr, $allowedGroupsArray)) > 0){
		return TRUE;
	}

	return FALSE;
}

function getAccessPatientMenu(){
	$allowedGroupsArray	= array("8","10","11","12","13","15");

	if( sizeof(array_intersect(get_instance()->groupsArr, $allowedGroupsArray)) > 0){
		return TRUE;
	}
	
	return FALSE;
}

function getAccessBillingMenu(){
	$allowedGroupsArray	= array("8","10","11","12","13","14","15");

	if( sizeof(array_intersect(get_instance()->groupsArr, $allowedGroupsArray)) > 0){
		return TRUE;
	}
	
	return FALSE;
}

function getAccessReportMenu(){
	$allowedGroupsArray	= array("8","11","12","13","14");

	if( sizeof(array_intersect(get_instance()->groupsArr, $allowedGroupsArray)) > 0){
		return TRUE;
	}
	
	return FALSE;
}

function getAccessAdminMenu(){
	$allowedGroupsArray	= array("8","10","11");

	if( sizeof(array_intersect(get_instance()->groupsArr, $allowedGroupsArray)) > 0){
		return TRUE;
	}
	
	return FALSE;
}

function checkBillingPermit(){
	return redirect()->route('Login');
}

function retcards(){
	//$file = base_url();
	//return file_get_contents('cardnums.txt');
	$file	= FCPATH . 'bmclist.txt';
	$stream = fopen( $file ,"r");
	$string = stream_get_contents($stream);
	
    while (($line = fgets($stream)) !== false) {
        // process the line read.
		$string[]	= $line;
    }

    fclose($stream);
	
	return $string;
}

function getFilesInDir(){
	$arrFiles	= array();
	$dirPath	= FCPATH . "../public/downloads/database";

	$files		= scandir($dirPath);

	foreach ($files as $file) {
		$filePath = $dirPath . '/' . $file;
		if (is_file($filePath)) {
			$arrFiles[] = $file;
		}
	}

	return $arrFiles;
}

function saveRespStop($responseJson, $jsonData){
	saveApiLogs( json_encode(array($responseJson, $jsonData)) );
	die($responseJson);
}

function getCurrIps(){
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}
