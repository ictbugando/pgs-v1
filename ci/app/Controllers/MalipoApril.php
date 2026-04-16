<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;

class Malipo extends BaseController
{
    public function index(){
        /*
        //$url    = "http://localhost/api/postbatchpatrecord";
        //$url    = "https://bmc.lipaswitch.co.tz/api/postbatchpatrecord";
        //$requestKey = "testxyz";
        //$requestKey = "testxyz";
        $hashArr    = getApiKeyHashPair();
        $url        = "https://bmc.lipaswitch.co.tz/api/postpatbills";
        //$url        = "http://localhost/bmc/public/api/postpatbills";

        $jsonTestString = array(
                                "RequestCode" => "SEND_BILL_DATA",
                                "RequestTimestamp" => "2023-11-10T12:30:45Z",
                                "RequestKey" => $hashArr["key"],
                                "RequestHash" => $hashArr["hash"],                               
                                "Bill" => array(
                                    "BillNumber" => "BILL013",
                                    "PatientId" => "PAT001",
                                    "PatientName" => "JOEPH MUSYOKA",
                                    "PatientPhone" => "255763855774",
                                    "BillTotal" => "100.00",
                                    "AmountPaid" => "0.00",
                                    "BillCreatedDate" => "2023-11-10",
                                    "BillExpiryDate" => "2023-12-01",
                                    "BillTerms" => "Net 30",
                                    "BillItems" => array(
                                        array(
                                            "ItemId" => "ITEM001",
                                            "ItemQty" => "2",
                                            "ItemCost" => "50.00",
                                            "ItemTax" => "5.00",
                                            "ItemDiscount" => "0.00",
                                            "ItemDesc" => "Blah",
                                            "TotalCost" => "105.00"
                                            )
                                        )
                                    ),
                               );

        $jsonTestString = json_encode($jsonTestString);

        //die( $jsonTestString );
        //'{"RequestCode":"SEND_BILL_DATA","RequestTimestamp":"2023-11-10T12:30:45Z","RequestKey":"your_api_key",
            //"RequestHash":"generated_hash_value",
            //"BillItems":[{}]}]}';

        $sXML       = $this->Engine->doCurljson($url , $jsonTestString);
        var_dump( $sXML );

        die();
        /*
       $sumuaary = $this->Engine->getMalipoSummary($startDate = 0, $stopDate = 0);
       var_dump($sumuaary);
       die();*/
       //$url        = "https://bmc.lipaswitch.co.tz/malipo/nmbbillrequest";
       //$jsonTestString          = json_encode(array("reference" => "E0000023" , "token" => "iamtestoken"));
       /*
       $url        = "https://bmc.lipaswitch.co.tz/malipo/nmbprocesscallback";
       $jsonTestString          = json_encode(array("reference" => "SAS169E0000016" , "timestamp" => "1234" , "receipt" => "XCDSF6" , "customer_name" => "Joseph" ,  
                "account_number" => "887766" , "token" => "00tt55" , "amount" => "1000" , "api_key" => "XX4455" , "api_secret" => "3344556"));
*/
        //$sXML       = $this->Engine->doCurljson($url , $jsonTestString);

        //var_dump( $sXML );

        //DIE();
    /*
    $reference      = "123456";
    $dataValues     = $this->Engine->doValidateCrdbRefOrControlNum( $reference );

    var_dump(  $dataValues  );

    die();
    */

    //crdbverify // crdbpost

    //$url        = base_url('malipo/crdbverify');

    /*
    $url = "http://localhost/bmc/public/malipo/crdbverify";
    //$url        = "https://bmc.lipaswitch.co.tz/malipo/crdb1";
    
    $myJsonData     = '{"paymentReference": "123456e","token": "463917799b220d99f68b84419570267eef6a34b181c47","checksum": "de809c4f9f3f05f20e2b6c5fc3312423ac32b783", "institutionID": 40091695213994 }';
    //$myJsonData     = '{\"paymentReference\":\"BMH123456\",\"institutionID\":\"40091695213994\",\"token\":\"1V5akbSaoburVYgSLb4CkF5dLj8uHj\",\"checksum\":\"c01bdde936064ea312b875864b5f11c7ab1eb0df\"}';
    $sXML       = $this->Engine->doCurljson($url , $myJsonData);

    var_dump($sXML);
    
    die();
    
    */
    //get_instance()->Engine->calcPatientBalance($pat_id = 17);
    //die();
    //get_instance()->Engine->prosPatientUnpaidBills($pat_id = 1 ,$globBillId = 0 );
    //die();
    //$billDetails    = get_instance()->Engine->getBillDetailsForMalipoValidation($pat_ref);

    //die();
    //$dataArray = array("msisdn" => "", "receipt" => "FA435553423889037", "amount" => "23000", "reference" => "E0000119");
    //var_dump(doProcessGlobalTransaction($trans_sp = "4", $dataArray));
    //die();

    //$url    = "http://localhost/malipo/crdbpost";
    $url    = "http://localhost/bmc/public/malipo/crdbpost";
    
    //$url    = "https://bmc.lipaswitch.co.tz/malipo/crdbpost";

    //die("Test");

    $myJsonData     = '{"amount": "100","transactionRef": "FA435553423889058","paymentReference": "BMHE0000117",
                       "token": "463917799b220d99f68b84419570267eef6a34b181c48",
                       "checksum": "de809c4f9f3f05f20e2b6c5fc3312423ac32b783", "institutionID": 40091695213994 }';
    
    $sXML       = $this->Engine->doCurljson($url , $myJsonData);

    var_dump($sXML);

    die();
    //$balrecArray	= get_instance()->Engine->getPatientBalRecord($pat_id = 2);

    //var_dump($balrecArray);

       // die();
       /*
    $url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros1";
    //$url    	= "http://localhost/bmc/public/malipo/aipros1";
    
    $myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
    $myXML		.=  '<COMMAND>
                        <TYPE>C2B</TYPE>
                        <CUSTOMERMSISDN>786670758</CUSTOMERMSISDN>
                        <MERCHANTMSISDN>780800125</MERCHANTMSISDN>
                        <AMOUNT>1100</AMOUNT>
                        <PIN></PIN>
                        <REFERENCE>ABC6677</REFERENCE>
                        <REFERENCE1>1234567156582</REFERENCE1>
                        <REFERENCE2>11151111944</REFERENCE2>
                    </COMMAND>';
            
    $sXML       = $this->Engine->docurl($url , $myXML);
		
	//var_dump($sXML);
	$xmlresponse	= new \SimpleXMLElement($sXML["data"]);
    //var_dump($xmlresponse);

    echo $xmlresponse->STATUS."<br />";

    $status = $xmlresponse->STATUS;

    //die();

    //$status = "200";

    if( $status  == "200"){
        $url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros2";
        //$url    	= "http://localhost/bmc/public/malipo/aipros2";
		
        $myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
        $myXML		.=  '<COMMAND>
                <TYPE>C2B</TYPE>
                <CUSTOMERMSISDN>786670758</CUSTOMERMSISDN>
                <MERCHANTMSISDN>780800125</MERCHANTMSISDN>
                <CUSTOMERNAME></CUSTOMERNAME>
                <AMOUNT>50000</AMOUNT>
                <PIN></PIN>
                <REFERENCE>ABC6677</REFERENCE>
                <USERNAME></USERNAME>
                <PASSWORD></PASSWORD>
                <REFERENCE1>1234567156582</REFERENCE1>
                <REFERENCE2>11151111944</REFERENCE2>                   
                </COMMAND>';
        
        $sXML       = $this->Engine->docurl($url , $myXML);

        var_dump($sXML);

    }
    
    die();

    $url    	= "https://bmc.lipaswitch.co.tz/malipo/aipros5";

    $myXML		= '<?xml version="1.0" encoding="UTF-8" standalone="yes" ?>';
    $myXML		.=  '<COMMAND>
                <TYPE>BILLFETCH</TYPE>
                <CUSTOMERMSISDN>685466727</CUSTOMERMSISDN>
                <USERNAME></USERNAME>
                <PASSWORD></PASSWORD>
                <CUSTOMERREF>123456</CUSTOMERREF>
                </COMMAND>';
    
    $sXML       = $this->Engine->docurl($url , $myXML);

    var_dump($sXML);
    */

    $data   = '{"paymentReference": "2001234423","token": "463917799b220d99f68b84419570267eef6a34b181c47",
            "checksum": "de809c4f9f3f05f20e2b6c5fc3312423ac32b783","institutionID": 8008}';
		
    }
    
    public function test(){
        //$this->Engine->prosPatientUnpaidBills($pat_id = 1);
        //$doProcess = $this->Engine->prosPatientUnpaidBills($pat_id = 1 ,$globBillId = 23);
        //echo $doProcess;
		$codes		= retcards();
		$codeArr	= explode("\r\n" , $codes);

        //var_dump($codeArr);'
        $prodIdsArr = array();
        $siku       = date("Y-m-d H:i:s");
		
		foreach($codeArr as $code){
            $splitArr	= explode(",,," , $code);
            //echo $splitArr[0].",,,".$splitArr[1].",,,".$splitArr[2].",,,".$splitArr[3].',,,<br />';
            
            $prodName   = $splitArr[0];
            $prodCost   = $splitArr[1];
            $prodType   = $splitArr[2];
            $prodDesc   = $splitArr[3];

            //Get product type
            if(!isset($prodIdsArr[$prodType])){
                //Insert product id
                //$prodIdsArr[$prodType] = 1;
                $prodTypeId = $this->Engine->getSearchProductType($prodType);
                
                if($prodTypeId == "0"){
                    $prodTypeId = $this->Engine->saveProductType($prodType);
                }

                $prodIdsArr[$prodType] = $prodTypeId;
            }

            $data  = array("prod_name" => $prodName, "prod_type_id" => $prodIdsArr[$prodType], 
                           "rec_by" => "0", "prod_desc" => $prodDesc, "siku" => $siku);

            //var_dump( $data );

            $this->Engine->saveProductDetails($data , $prodCost);
            
            //break;
		}
    }

	public function aipros1(){
    doAipros1Validate(); //Validate
	}

	public function aipros2(){
    doAipros2Process(); //Process
	}    
	
	public function aipros3(){
    doAipros3Query(); //Query
	}

	public function aipros4(){
    doAipros4Fetch(); //Bill fetch
	}	    
	
	public function aipros5(){
    doAipros5Lookup(); //Lookup
	}
    
	public function crdbverify(){
       ob_start();
       $jsonData     = trim(file_get_contents('php://input'));
       $permitAccess = doCheckPermMalipoApi( "4" );
       
       if ( $permitAccess ){
            $retBillArr = doCRDBValidation($jsonData);

            if( isset($retBillArr->bill_amount) ){
                $dataValues     = array("status" => "200" , "msg" => "Success" , "data" => $retBillArr); //Valid return Json
            }
            else{
                $dataValues     = array("status" => "204" , "msg" => "Invalid Reference or Bill"); //Invalid Reference
            }
       }
       else{
           $dataValues = dispPermDeniedCrdb();
       }
       
       $respValidate   = dispResponseCRDBValidate($dataValues , $flag = "1");
       
       echo $respValidate;
       
       $response	= ob_get_clean();
       
       saveLogs("4" , $response);
       
       die($response);

    }

    public function crdbpost(){
       ob_start();
       $jsonData     = trim(file_get_contents('php://input'));
       $permitAccess = doCheckPermMalipoApi( "4" );
       
       if ( $permitAccess ){
        $dataValues     = doProcessCRDBTransaction($jsonData);
       }
       else{
        $dataValues = dispPermDeniedCrdb();
       }
       
       $respValidate   = dispResponseCRDBValidate($dataValues , $flag = "2");
       
       echo $respValidate;

       $response	= ob_get_clean();
       
       saveLogs("4" , $response);
       
       die($response);
    }

    public function nmbreq1(){
        die(";");
        ob_start();
        $xmlresponse    = trim(file_get_contents('php://input'));
        $permitAccess   = doCheckPermMalipoApi( "5" );
        $dataValues     = array();
        
        if ( $permitAccess ){
            $xmlresponse    = new SimpleXMLElement($xmlresponse);
            $dataValues      = doNMBValidation($xmlresponse);
        }
        
        $respValidate   = dispResponseNMBInitiator($dataValues , $flag = "1");
    
        echo $respValidate;

        $response	= ob_get_clean();

        saveLogs("5" , $response);

        die($response);
    }

    public function nmbreq2(){
        die(";");
        ob_start();
        $xmlresponse    = trim(file_get_contents('php://input'));
        $permitAccess   = doCheckPermMalipoApi( "5" );
        $dataValues      = array();
        
        if ( $permitAccess ){
            $xmlresponse	= new SimpleXMLElement($xmlresponse);
            $dataValues     = doProcessNMBTransaction($xmlresponse);
        }

        $respValidate   = dispResponseNMBInitiator($dataValues , $flag = "2");
    
        echo $respValidate;        

        $response	= ob_get_clean();

        saveLogs("5" , $response);

        die($response);
    }

    public function nmbrecon(){
        die();
	$token      = nmbGetToken(); //Generate Access Token Service
        $url        = "https://api.mpayafrica.co.tz/v2/reconcilliation";

        $jsonString = array("token" => $token, "reconcile_date" => date("d-m-Y"));
        $jsonString = json_encode( $jsonString );

        $sXML       = $this->Engine->doCurljson($url , $jsonString);

        $jsonData   = $sXML["data"];

        if( strlen($jsonData) > 0){
            $jsonData   = json_decode($jsonData,1);
            $payArrs    = $jsonData["transactions"];

            foreach($payArrs AS $payArr){
                $reference  = $payArr["reference"];
                $reference  = ( substr($reference , 0 , 6) == 'SAS169') ? substr($reference, 6) : $reference;
				$reference	= preg_replace('/[^a-zA-Z0-9]/', '', $reference);
                $amount     = $payArr["amount"];
                $receipt    = $payArr["receipt"];
                $reqDataArr = array();
				
				$reqDataArr = array("msisdn" => "", "receipt" => $receipt, 
									"amount" => $amount, "reference" => $reference);

                if( !get_instance()->Engine->isDuplicateTxn($receipt) ){
					echo 'Hakuna '.$receipt."<br />" ;
                    doProcessGlobalTransaction($trans_sp ="5" , $reqDataArr);
                }
				
				var_dump( $reqDataArr );
				
				echo "<br />";
            }
        }
    }    

    public function nmbinvoicepush(){
        /*
        nmbTransReconcile();
        die();
        $reference	= "123456";

        nmbBillCancel($reference);

        die();
        */
        $reference      = "E0000017";
        $resObject      = get_instance()->Engine->getBillDetailsForMalipoValidation( $reference );
        var_dump( $resObject );

        if( isset($resObject->reference) ){
            $token      = nmbGetToken(); //Generate Access Token Service

            if( $token != "0"){
                $retArrayBillSubmit = nmbBillSubmit($token , $resObject);
                //var_dump( $retArrayBillSubmit  );

                if( sizeof($retArrayBillSubmit) > 0 ){
                        if( $retArrayBillSubmit["status"] == "1"){
                                // Invoice Successfully Uploaded. End Of Story
                                // Update of Invoice submission table
                        }
                        else{
                                if( str_contains( $retArrayBillSubmit["description"], "Duplicate") ){
                                        $token              = nmbGetToken(); //Generate Access Token Service

                                        if( $token != "0"){
                                                $retArrayBillUpdate = nmbBillUpdate($token , $resObject); //Update Invoice
                                                //var_dump( $retArrayBillUpdate  );

                                                if( sizeof($retArrayBillUpdate) > 0 ){
                                                        if( $retArrayBillUpdate["status"] == "1"){
                                                                // Invoice Successfully Uploaded. End Of Story
                                                                // Update of Invoice submission table                                                        
                                                        }
                                                        else{
                                                                //Update as failed to submit
                                                        }
                                                }
                                        }
                                }
                        }
                }

            }
        }
     }

     public function nmbbillrequest(){
        ob_start();
        $jsonData     = trim(file_get_contents('php://input'));
        $permitAccess = doCheckPermMalipoApi( "5" );
        $token          = "0";
        
        if ( $permitAccess ){
                $jsonArray      = json_decode($jsonData , 1);

                if( isset($jsonArray["reference"]) ){
                        $reference      = $jsonArray["reference"];
                        $reference      = str_replace("SAS169","", $reference); //CONVERT TO LOCAL SCHEME
                        $token          = $jsonArray["token"];
                        $resObject      = get_instance()->Engine->getBillDetailsForMalipoValidation( $reference );

                        $invoiceJsonData = nmbGenSubmitJson($token , $resObject);

                        echo $invoiceJsonData;
                }
                else{
                        echo json_encode( array("status" => "0" , "description" => "Invalid Reference" , "token" => $token) );
                }
        }
        else{
                echo json_encode( array("status" => "0" , "description" => "Permision Denied" , "token" => $token) );
        }
 
        $response	= ob_get_clean();
        
        saveLogs("5" , $response);
        
        die($response);
     }

     public function nmbprocesscallback(){
        ob_start();
        $jsonData     = trim(file_get_contents('php://input'));
        $permitAccess = doCheckPermMalipoApi( "5" );
        $token          = "0";
        
        if ( $permitAccess ){
                $dataValues     = doProcessNMBTransaction($jsonData);
                $status         = ($dataValues['status'] == '200') ? "1" : "0";

                echo json_encode( array("status" => $status , "description" => $dataValues['msg'] ) );

        }
        else{
            echo json_encode( array("status" => "0" , "description" => "Permision Denied" , "token" => $token) );
        }
 
        $response	= ob_get_clean();
        
        saveLogs("5" , $response);
        
        die($response);
     }


}
