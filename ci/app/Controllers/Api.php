<?php

namespace App\Controllers;

class Api extends BaseController
{

    public function index()
    {
        echo '{"RequestCode":"SEND_BILL_DATA","RequestTimestamp":"2024-08-04T22:54:43","RequestKey":"qab0Zubvub","RequestHash":"0a311b53036f405517cecc92a2b7e61514e786b8b2525499351d06d09f705928","Bill":{"BillNumber":"1039086","PatientId":"399124","PatientName":"Neema Gilbeth Sedondi","PatientPhone":"255754623570","BillTotal":"20000.00","AmountPaid":"0.00","BillCreatedDate":"2024-08-04","BillExpiryDate":"2024-08-24","BillTerms":"eHMS Payment","BillItems":[{"ItemId":"1663","ItemDesc":"Bilirubin Total","ItemQty":"1","ItemDiscount":"0.00","ItemTax":"0.00","ItemCost":"10000.00"},{"ItemId":"8968","ItemDesc":"Direct Bilirubin","ItemQty":"1","ItemDiscount":"0.00","ItemTax":"0.00","ItemCost":"10000.00"}]}}';
    }

    public function dosynchrepeat()
    {
        //Synch failed transactions
        get_instance()->Engine->cronstart($id = 2);
        get_instance()->Engine->getBillsNotSynched();
        get_instance()->Engine->cronend($id = 2);
    }

    public function domastersynch()
    {
        get_instance()->Engine->cronstart($id = 3);
        $this->Engine->doGlobalMasterUpdate();
        get_instance()->Engine->cronend($id = 3);
    }

    public function testpush()
    {

        $patientData = get_instance()->Engine->fetchPatientRecord($billPatId = "8851");
        var_dump($patientData);
        die();
        //cleanTxStrings($string);
        $itemCode = "650";
        $desc = "Gentamicin Injection: 40mg\/mL in 2mL";
        $cost = "1000.00";

        $itemId = get_instance()->Engine->fetchLocalItemId($itemCode, $desc, $cost);

        echo 'My Item + ' . $itemId;
        //$reqDataArr	= array("receipt" => "xxcc", "amount" => "500", "reference" => "E0000151" , "msisdn" => "0763855774" );//["receipt"];
        //var_dump($reqDataArr);
        //doProcessGlobalTransaction($trans_sp = "5" , $reqDataArr);
        //$this->Engine->doSaveTemporaryNewTotal($billNum = "E0000150" , $newTotal = "100");
        //$data = $this->Engine->doGlobalPushPayments();
    }

    public function postbillpayment()
    {
        get_instance()->Engine->cronstart($id = 1);
        get_instance()->Engine->doSynchBillPaymentsEHMS();
        get_instance()->Engine->cronend($id = 1);
    }

    public function postpatbills()
    {
        //Fetch single patient record {PGM -> EHMS}
        $newBillArr = array();
        $jsonData = trim(file_get_contents('php://input'));
        //$jsonData   = '{"RequestCode":"SEND_BILL_DATA","RequestTimestamp":"2025-01-28T16:09:37","RequestKey":"dluebkcbbc","RequestHash":"ba7ed5f0413180f1cfae4b3c7acea43e06dacd4fe8f15f703872bfc12cda5894","Bill":{"BillNumber":"1209466","PatientId":"387628","PatientName":"jenipher enos omolo","PatientPhone":"255766858570","BillTotal":"30000.00","AmountPaid":"0.00","BillCreatedDate":"2025-01-28","BillExpiryDate":"2025-02-17","BillTerms":"eHMS Payment","BillItems":[{"ItemId":"607","ItemDesc":"Ampicillin + Cloxacillin Injection: 500mg","ItemQty":"1","ItemDiscount":"0.00","ItemTax":"0.00","ItemCost":"9000.00"},{"ItemId":"1194","ItemDesc":"Paracetamol Infusion: 10mg\/ml","ItemQty":"1","ItemDiscount":"0.00","ItemTax":"0.00","ItemCost":"21000.00"}]}}';
        //$jsonData   = '{"RequestCode":"SEND_BILL_DATA","RequestTimestamp":"2025-02-07T11:58:37","RequestKey":"5z3ntz9URuQyk2zYtjuI6liQuTS8Xon65AvoYHmWQYdBjYtIzfqzer3mTM4wNbfA","RequestHash":"9a8ef4c19199a791e77db8dbb6ba61952846d1b43c795aee87b4741211791588","Bill":{"BillNumber":"1219683","PatientId":"428981","PatientName":"Zack Matheus Mashauri","PatientPhone":"255769999612","BillTotal":"20000.00","AmountPaid":"0.00","BillCreatedDate":"2025-02-07","BillExpiryDate":"2025-02-27","BillTerms":"eHMS Payment","BillItems":[{"ItemId":"4075","ItemDesc":"Consultation fee - Self Referral Patient & Without Appointment","ItemQty":"1","ItemDiscount":"0.00","ItemTax":"0.00","ItemCost":"20000.00"}]}}';

        if (!checkIfAllowed()) {
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Access Denied", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        }

        if (!json_validator($jsonData)) {
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Invalid Json", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        }

        $requestData = json_decode($jsonData, 1);

        if (!validateApiBillData($requestData)) {
            //Missing data in request
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Missing data in request", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        }

        $reqKey = $requestData["RequestKey"];
        $reqHash = $requestData["RequestHash"];

        $billNum = $requestData["Bill"]["BillNumber"];
        $billPatId = $requestData["Bill"]["PatientId"];
        $billPatName = $requestData["Bill"]["PatientName"];
        $billPhone = $requestData["Bill"]["PatientPhone"];
        $billCreate = $requestData["Bill"]["BillCreatedDate"];
        $billExpiry = $requestData["Bill"]["BillExpiryDate"];

        if (!verifyApiKeyHashPair($reqKey, $reqHash)) {
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Invalid Hash Key Pair", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        }

        $billTestArr = $this->Engine->getBillDetailsForMalipoValidation($billNum);

        if (isset($billTestArr->bill_num)) {
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Duplicate Entry", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        }

        //Get Pat ID
        $patientData = get_instance()->Engine->fetchPatientRecordNew($billPatId, $ext = "0");

        if (!isset($patientData->pat_id)) {
            $nameArr = explode(" ", $billPatName, 2);
            $pat_name1 = "";
            $pat_name2 = "";

            if (isset($nameArr[0])) {
                $pat_name1 = $nameArr[0];
            }

            if (isset($nameArr[1])) {
                $pat_name2 = $nameArr[1];
            }

            $dataPatient = array(
                "pat_num" => $billPatId,
                "sur_name" => $pat_name1,
                "other_names" => $pat_name2,
                "phone" => $billPhone
            );

            //create new pat record and return ID
            if (get_instance()->Engine->savePatientDetails($dataPatient)) {
                $patientData = get_instance()->Engine->fetchPatientRecordNew($billPatId, $ext = "0");
            } else {
                $responseJson = genApiBillPostResponse($status = "422", $msg = "Failed to get patient", $newBillArr);
                saveRespStop($responseJson, $jsonData);
            }
        }

        $dataMap = array("pat_id" => $patientData->pat_id, "control_num" => $billNum, "bill_creation" => $billCreate);

        //Save bill and responsd
        $billArray = array();
        $grandTotal = 0;
        $bill_id = 0;
        $billItemsArr = $requestData["Bill"]["BillItems"];
        $itemsCount = 0;

        foreach ($billItemsArr as $itemArr) {
            $tax = 0;
            $disc = 0;
            $cost = 0;
            $qty = 0;
            $desc = "";
            $itemCode = 0;

            if (isset($itemArr['ItemCost'])) {
                $cost = (int) $itemArr['ItemCost'];
            }

            if (isset($itemArr['ItemQty'])) {
                $qty = (int) $itemArr['ItemQty'];
            }

            if (isset($itemArr['ItemTax'])) {
                $tax = (int) $itemArr['ItemTax'];
            }

            if (isset($itemArr['ItemDiscount'])) {
                $disc = (int) $itemArr['ItemDiscount'];
            }

            if (isset($itemArr['ItemDesc'])) {
                $desc = $itemArr['ItemDesc'];
            }

            if (isset($itemArr['ItemId'])) {
                $itemCode = $itemArr['ItemId'];
            }

            if ($qty == 0 || $cost == 0) {
                $grandTotal = 0;
                $bill_id = 0;
                break;
            }

            $itemId = get_instance()->Engine->fetchLocalItemId($itemCode, $desc, $cost);

            $total = ($cost * $qty);

            if (($disc > 0) && ($disc < 101)) {
                $total = ($total - ($total * ($disc / 100)));
            }

            if (($tax > 0) && ($tax < 101)) {
                $total = ($total + ($total * ($tax / 100)));
            }

            $grandTotal = $grandTotal + $total;

            $arr["total"] = $total;

            $billArray[] = array(
                "itemId" => $itemId,
                "cost" => $cost,
                "qty" => $qty,
                "tax" => $tax,
                "discount" => $disc,
                "total" => $total
            );
        }

        if (sizeof($billArray) > 0) {
            //Save Only Bill With Bill Items

            if ($grandTotal > 0) {
                $billArray["grandTotal"] = $grandTotal;
                $bill_id = $this->Engine->saveBillDetails($dataMap, $billArray);
            }
        }

        $bill_id = (int) $bill_id;

        if ($bill_id > 0) {
            //$newBillArr     = $this->Engine->getBillDetailsForMalipoValidation($bill_id);
            $newBillArr = $this->Engine->getBillUsingId($bill_id);
            $responseJson = genApiBillPostResponse($status = "200", $msg = "Success", $newBillArr);
            saveRespStop($responseJson, $jsonData);
        } else {
            $responseJson = genApiBillPostResponse($status = "422", $msg = "Failed to save bill", $bill_id);
            saveRespStop($responseJson, $jsonData);
        }
    }

    public function getsinglepatbill()
    {
        $hashArr = getApiKeyHashPair();
        $reqCode = "FETCH_SINGLE_BILL";

        $requestData = array(
            "RequestCode" => $reqCode,
            "RequestTimestamp" => date("Y-m-d H:i:s"),
            "RequestKey" => $hashArr["key"],
            "RequestHash" => $hashArr["hash"],
            "BillNumber" => "29613"
        );

        $requestJson = json_encode($requestData);

        $hostIp = "10.15.0.21";
        $url = "http://" . $hostIp . ":30005/ehms/payment/GetSinglePatientBill";

        $sXML = $this->Engine->doCurljson($url, $requestJson);
        $jsonData = $sXML["data"];

        saveApiLogs($jsonData);

        if (!json_validator($jsonData)) {
            die("Invalid Json"); //Invalid Json
        }

        $responseArr = json_decode($jsonData, 1);

        if (!validateApiPayResponseData($responseArr)) {
            //die("Invalid response");//Missing data in request
        }

        $reqKey = $responseArr["ResponseKey"];
        $reqHash = $responseArr["ResponseHash"];

        if (!verifyApiKeyHashPair($reqKey, $reqHash)) {
            die("Invalid Hash"); //Invalid Hash
        }

        var_dump($responseArr);

    }

    public function getbatchpatbill()
    {
        $hashArr = getApiKeyHashPair();
        $reqCode = "FETCH_BILL_BATCH";

        $requestData = array(
            "RequestCode" => $reqCode,
            "RequestTimestamp" => date("Y-m-d H:i:s"),
            "RequestKey" => $hashArr["key"],
            "RequestHash" => $hashArr["hash"],
            "StartBill" => "29604"
        );

        $requestJson = json_encode($requestData);

        $hostIp = "10.15.0.21";
        $url = "http://" . $hostIp . ":30005/ehms/payment/GetBatchPatientBill";

        $sXML = $this->Engine->doCurljson($url, $requestJson);
        $jsonData = $sXML["data"];

        saveApiLogs($jsonData);

        if (!json_validator($jsonData)) {
            die("Invalid Json"); //Invalid Json
        }

        $responseArr = json_decode($jsonData, 1);

        if (!validateApiPayResponseData($responseArr)) {
            //die("Invalid response");//Missing data in request
        }

        $reqKey = $responseArr["ResponseKey"];
        $reqHash = $responseArr["ResponseHash"];

        if (!verifyApiKeyHashPair($reqKey, $reqHash)) {
            die("Invalid Hash"); //Invalid Hash
        }

        var_dump($responseArr);

    }

    public function getsynchpaymentrecords()
    {
        //Do synch all payments created in the day {PGM -> EHMS}
        $hashArr = getApiKeyHashPair();
        $reqCode = "DAILY_PAYMENT_SYNCH";

        $billArray = $this->Engine->getDaysBillSynch();

        $requestData = array(
            "RequestCode" => $reqCode,
            "RequestTimestamp" => date("Y-m-d H:i:s"),
            "RequestKey" => $hashArr["key"],
            "RequestHash" => $hashArr["hash"],
            "LastSyncDate" => date("Y-m-d H:i:s"),
            "Payments" => $billArray
        );

        $requestJson = json_encode($requestData);

        echo $requestJson . '<br />';

        $hostIp = "10.15.0.21";
        $url = "http://" . $hostIp . ":30005/ehms/payment/SynchDailyBillPayment";
        $sXML = $this->Engine->doCurljson($url, $requestJson);
        $jsonData = $sXML["data"];

        //var_dump($sXML);

        saveApiLogs($jsonData);

        if (!json_validator($jsonData)) {
            die("Invalid Json"); //Invalid Json
        }

        $responseArr = json_decode($jsonData, 1);

        if (!validateApiPayResponseData($responseArr)) {
            //die("Invalid response");//Missing data in request
        }

        $reqKey = $responseArr["ResponseKey"];
        $reqHash = $responseArr["ResponseHash"];

        if (!verifyApiKeyHashPair($reqKey, $reqHash)) {
            die("Invalid Hash"); //Invalid Hash
        }

        var_dump($responseArr);
        /*


                {
                    "RequestCode": "DAILY_PAYMENT_SYNCH",
                    "RequestTimestamp": "2023-11-16T00:00:00Z",
                    "RequestKey": "your_api_key",
                    "RequestHash": "generated_hash_value",
                    "LastSyncDate": "2023-11-15T00:00:00Z",
                    "Payments": [
                      {
                        "BillNumber": "BILL001",
                        "PatientName": "JOEPH MUSYOKA",
                        "PatientPhone": "255763855774",
                        "AmountPaid": "100.00",
                        "BillTotal": "100.00",
                        "PayStatus": "Fully Paid",
                        "PaymentReceipt": "receipt_bill001_20231115"
                      }
                      // ... (other payments)
                    ]
                  }*/

    }

}