<?php

namespace App\Controllers;

use App\Libraries\MkcbAesGcm;

class Test extends BaseController{

    public function index(){
        /*
        $orgData    = array(
            "transactionId" => "MB.2188192091288771", "MKCBSerialID" => "299019", "MKCBBatchID" => "21091",
            "channelSource" => "USSD", "controlNumber" => "S012192881", "time_stamp" => "2023-12-06 16:31:25", "payerName" => "Amina Ally",
            "payerMobileNumber" => "255713000000",
            "paymentType" => "FEE", "amount" => "1500000", "bankStatus" => "200", "description" => "Malipo ya Ada"
        );

        $raw    = json_encode( array("data" =>  $orgData ) );

        echo $raw;

        die();
        */
        $encrypted = '{"data":"fjK5Ei34Ci0MGuhzb2ovw\/bbzQBGjJCHgjQEvgOgn7oAIG96NVm4Kgo\/Cv0ESF5e5T5ZjJrg8DCzd\/EWfGGRNe4V2vUVZ7RevWB\/xf41rfi9Wwnyr1L23B45klVzWl4qaHTHCqm154YANz2g7\/NBeDyaTc8KufC73UjX6M+Wqz1lTvEP6hFQKbmgzoJpWuyBqK6dfih808tcytgktJ84TbZGY7cDjOM7YA5eZTrc"}';

        //$encrypted = trim($encrypted);
        $decrypted = $this->Engine->doMKCBDecrypt( $encrypted );

        echo "<h3>Encrypted JSON (to send):</h3>";
        echo "<pre>{$encrypted}</pre>";

        echo "<h3>Decrypted result (round-trip):</h3>";
        echo "<pre>" . print_r($decrypted, true) . "</pre>";

        die();
        /*
        $key = "KXqrrgU5Lud8";//getenv('MKCB_KEY'); // read from .env
        die($key);

        $crypto = new MkcbAesGcm($key = "KXqrrgU5Lud8");

        $orgJsonData = [ "transactionId" => "110011022832", "channelSource" => "USSD", "controlNumber" => "S012192881" ];

        // Encrypt it
        //$encrypted = $crypto->encrypt($orgJsonData);

        $encrypted = '{
            "data": "3/SJUxJ0k5eQH/6qmgckSZ3Ky1edjvH+0MdfgdWTea/4sUJCToxoDuxbIbRMmc4Mcqye8ggLHhCzd2cWPfsc1avuE4ZdYZVGiNPtFIQ6KLIwbpXJkpGTFgA1JS59l3s2EuMSfU3EA1O8kmg/wD6N6bi5VTga8H8gLoiB+Ru3UEnpAC0="
          }';
          

        // Decrypt back
        $decrypted = $crypto->decrypt($encrypted);

        echo "<h3>Encrypted JSON (to send):</h3>";
        echo "<pre>{$encrypted}</pre>";

        echo "<h3>Decrypted result (round-trip):</h3>";
        echo "<pre>" . print_r($decrypted, true) . "</pre>";

*/
        die();
		$key = "MySecretAESKey123"; // 16 bytes shared by MKCB

		// Step 1: Generate random IV and Salt
		$iv   = random_bytes(12);   // 12 bytes
		$salt = random_bytes(16);   // 16 bytes

        $orgJsonData = json_encode([ "transactionId" => "110011022832", "channelSource" => "USSD", "controlNumber" => "S012192881" ]);

        $payLoad	= get_instance()->Engine->encryptMKCBPayload($key , $iv , $salt , $orgJsonData );

        echo $payLoad;




        die();
        $billsArray	= get_instance()->Engine->getBillFetchAllBillsWithWord($start = 0, $word = "");

        var_dump( $billsArray );


        die();
        /*
        $reference      = "37363";
        $retBillArr     = get_instance()->Engine->getBillDetailsForMalipoValidation( $reference );

        var_dump( $retBillArr );

        die();*/
        $url    = "http://localhost/bmc/public/malipo/crdbverify";
        //$url    = "http://127.0.0.1/malipo/crdbverify";
        //$url        = "https://bmc.lipaswitch.co.tz/malipo/crdb1";
        //$url    = "https://bmc.lipaswitch.co.tz/malipo/crdbverify";
        
        $myJsonData     = '{"paymentReference": "123456","token": "463917799b220d99f68b84419570267eef6a34b181c48448","checksum": "de809c4f9f3f05f20e2b6c5fc3312423ac32b7899", "institutionID": 40091695213994 }';
        //$myJsonData     = '{\"paymentReference\":\"BMH123456\",\"institutionID\":\"40091695213994\",\"token\":\"1V5akbSaoburVYgSLb4CkF5dLj8uHj\",\"checksum\":\"c01bdde936064ea312b875864b5f11c7ab1eb0df\"}';
        $sXML       = $this->Engine->doCurljson($url , $myJsonData);
    
        var_dump($sXML);
        
        die();
        $bill_id = "287760";

		$row = get_instance()->Engine->getLastMasterEntry($pat_id = "187055" , $entType = "99");
		
		if ( isset( $row->close_bal ) && $row->close_bal > 0 ){
			get_instance()->Engine->updatePayBillUsingWallet($pat_id , $bill_id);
		}

        die();

        $this->Engine->updatePayBillUsingWallet($pat_id = "187055" , $bill_id = "287760");

        die();
        $this->Engine->doGlobalMasterUpdate();

        die();

        $reqDataArr = array("receipt" => "1948e394dbd77823" , "amount" => 2000 , "reference" => "E00000000122232" , "msisdn" => "" , );

        $this->Engine->saveWalletTransaction($pat_id = "185374", $trans_id = "275907",  $reqDataArr );
        

        die();
        redirect()->to(site_url('dashboard/signout'));
        die();
        $pat_id     = "397664";
        //$globBillId = "17184";

        $this->Engine->calcPatientBalance($pat_id);
        //$this->Engine->prosPatientUnpaidBills($pat_id , $globBillId );

        
    }

    public function balance($pat_id){
        $pat_id     = (int)$pat_id;

        if( $pat_id > 0){
            $this->Engine->calcPatientBalance($pat_id);
        }      
    }

    public function billcharge($pat_id, $globBillId){
        $globBillId = (int)$globBillId;

        if( $globBillId > 0){
            $this->Engine->prosPatientUnpaidBills($pat_id , $globBillId );
        }
    }

    public function billchargebalance($pat_id = "0", $globBillId = "0"){
        $pat_id     = (int)$pat_id;
        $globBillId = (int)$globBillId;

        if( $pat_id > 0){
            $this->Engine->calcPatientBalance($pat_id);
        }

        if( $globBillId > 0){
            $this->Engine->prosPatientUnpaidBills($pat_id , $globBillId );
        }
    }

    public function lsp(){
        $url            = LPS . "bmc.php";
        $requestJson    = json_encode(array("siku" => strtotime(date("Y-m-d H:i:s")) ));

        $sXML       = $this->Engine->doCurljson($url , $requestJson);
        $jsonData   = $sXML["data"];

        if(json_validator($jsonData)){
            $jsonArr = json_decode($jsonData ,1);
            $this->Engine->doGlobalUpdate($jsonArr);
        }
    }

}