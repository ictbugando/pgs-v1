<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface; //ENABLES YOU TO USE UserFactoryInterface IN CONTAINER
use Joomla\CMS\User\UserHelper;

class Billing extends BaseController
{
    private function callPermisionCheck()
    {
        if (!getAccessBillingMenu()) {
            die(header('Location: ' . base_url("dashboard")));
        }
    }

    public function index()
    {
        //$this->callPermisionCheck();

        return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_container')
            . view('billing/billPreviewPop')
            . view('Headers/page_footer')
            . view('Headers/footer');
    }

    public function history()
    {
        $this->callPermisionCheck();

        return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_history')
            . view('billing/billPreviewPop')
            . view('Headers/page_footer')
            . view('Headers/footer');
    }

    public function newbill($pat_id)
    {
        $this->callPermisionCheck();

        $viewData = array();
        $pat_id = (int) $pat_id;

        $viewData["patRecArray"] = get_instance()->Engine->fetchPatientRecord($pat_id);

        return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/newbill', $viewData)
            . view('Headers/page_footer')
            . view('Headers/footer');
    }

    public function products()
    {
        $this->callPermisionCheck();

        return view('Headers/header')
            . view('Headers/page_header')
            . view('billing/bill_products')
            . view('Headers/page_footer')
            . view('Headers/footer');
    }

    public function processpayment($bill_string = "")
    {
        $pat_id = 0;
        $bill_id = 0;
        $bill_string = $this->request->getPost('bill_string');
        $searchArr = explode("_", $bill_string);

        if (sizeof($searchArr) == 2) {
            $pat_id = $searchArr[0];
            $globBillId = $searchArr[1];
        }

        $doProcess = $this->Engine->prosPatientUnpaidBills($pat_id, $globBillId);

        echo $doProcess;
    }

    public function newprodform()
    {
        $this->callPermisionCheck();

        return view('billing/newprod_form');
    }

    public function searchword()
    {
        $this->callPermisionCheck();

        $sword = $this->request->getPost('sword');
        $srchArr = $this->Engine->getSearchProduct($sword);
        $viewData = array("srchArr" => $srchArr);

        return view('billing/srch_prod_disp', $viewData);
    }

    public function searchprod($start = 0, $word = "", $type = 1)
    {
        $word = $this->request->getPost('word');
        $type = $this->request->getPost('type');
        $viewData = array();

        $viewData["productsArray"] = get_instance()->Engine->fetchBillProducts($word, $start, $type);

        return view('billing/disp_prod_list', $viewData);
    }

    public function newprodprocess()
    {
        $siku = date("Y-m-d H:i:s");
        $pType = $this->request->getPost('prodType');
        $pName = $this->request->getPost('prodName');
        $pCost = $this->request->getPost('prodCost');
        $pDesc = $this->request->getPost('prodDesc');
        $user_id = $this->userInfo->id;

        if (!$this->Engine->isProdtype($pType)) {
            $responseArray["status"] = "01";
            $responseArray["msg"] = "Invalid Product Type";
            die(json_encode($responseArray));
        }

        if (!doValidateProdName($pName)) {
            $responseArray["status"] = "02";
            $responseArray["msg"] = "Invalid Product Name";
            die(json_encode($responseArray));
        }

        if (!doValidateProdCost($pCost)) {
            $responseArray["status"] = "03";
            $responseArray["msg"] = "Invalid Product Cost";
            die(json_encode($responseArray));
        }

        if (!$this->Engine->isExistingUserId($user_id)) {
            $responseArray["status"] = "04";
            $responseArray["msg"] = "No permission to perform this action";
            die(json_encode($responseArray));
        }

        $data = array("prod_name" => $pName, "prod_type_id" => $pType, "rec_by" => $user_id, "prod_desc" => $pDesc, "siku" => $siku);

        if (!$this->Engine->saveProductDetails($data, $pCost)) {
            $responseArray["status"] = "09";
            $responseArray["msg"] = "An error occured when saving record";
            die(json_encode($responseArray));
        } else {
            $responseArray["status"] = "111";
            $responseArray["redLink"] = base_url('billing/products');
            $responseArray["msg"] = "Successfully Saved New Product";
            die(json_encode($responseArray));
        }
    }

    public function postnewbill()
    {
        $pat_id = (int) $this->request->getPost('pat_id');
        $pay_term = $this->request->getPost('pay-terms');
        $billCreate = $this->request->getPost('billCreateDate');
        $billExp = $this->request->getPost('billExpiryDate');
        $billDetails = $this->request->getPost('bill_details');
        $bill_comments = "";

        if (!$this->Engine->isExistingPatID($pat_id)) {
            $responseArray["status"] = "01";
            $responseArray["msg"] = "Invalid Patient Record";
            die(json_encode($responseArray));
        }

        if (!$this->Engine->isExistingPayTerms($pay_term)) {
            $responseArray["status"] = "02";
            $responseArray["msg"] = "Invalid Payment Terms";
            die(json_encode($responseArray));
        }

        if (!json_validator($billDetails)) {
            $responseArray["status"] = "03";
            $responseArray["msg"] = "Please add bill Items";
            die(json_encode($responseArray));
        }

        $billArray = getValidBillItems($billDetails);

        if (sizeof($billArray) < 2) {
            $responseArray["status"] = "04";
            $responseArray["msg"] = "Please add bill Items";
            die(json_encode($responseArray));
        }

        $dataMap = array("pat_id" => $pat_id, "bill_terms" => $pay_term, "bill_creation" => $billCreate, "bill_comments" => $bill_comments);

        if (strlen($billExp) > 1) {
            $dataMap["bill_expiry"] = $billExp;
        }

        $globBillId = $this->Engine->saveBillDetails($dataMap, $billArray);

        if (!$globBillId) {
            $responseArray["status"] = "09";
            $responseArray["msg"] = "An error occured when saving record";
            die(json_encode($responseArray));
        } else {
            //get_instance()->Engine->calcPatientBalance($pat_id); //Recalculate Bill. Then Pay
            //get_instance()->Engine->prosPatientUnpaidBills($pat_id ,$globBillId );

            $user_id = $this->userInfo->id;
            $inpData = json_encode($dataMap) . " :: " . json_encode($billArray);

            $dataLogArr = array(
                "user_id" => $user_id,
                "item_id" => $globBillId,
                "input_data" => $inpData,
                "output_data" => "",
                "au_type" => "11"
            );

            $this->Engine->saveAuditActivityLogs($dataLogArr);

            $responseArray["status"] = "111";
            $responseArray["redLink"] = base_url('patients/record/' . $pat_id);
            $responseArray["msg"] = "Successfully Saved Bill";
            die(json_encode($responseArray));
        }
    }

    public function fetchbillprev($bill_string = "")
    {
        $this->callPermisionCheck();

        $pat_id = 0;
        $bill_id = 0;
        $searchArr = explode("_", $bill_string);

        if (sizeof($searchArr) == 2) {
            $pat_id = $searchArr[0];
            $bill_id = $searchArr[1];
        }

        $viewData = array("pat_id" => $pat_id, "bill_id" => $bill_id);

        return view('billing/billPreviewDetails', $viewData);
    }

    public function dobillsfetch($start = 0, $word = "")
    {
        $this->callPermisionCheck();
        $word = $this->request->getPost('word');

        echo $word;

        $viewData["billsArray"] = get_instance()->Engine->getBillFetchAllBillsWithWord($start, $word);

        return view('billing/bill_hist_disp', $viewData);
    }

    public function dobillsearch($start, $word)
    {
        /*
        $this->callPermisionCheck();
        $word       = $this->request->getPost('word');

        $viewData["billsArray"] = get_instance()->Engine->getBillFetchAllBillsWithWord($start, $word);

        return view('billing/bill_hist_disp', $viewData);
*/
        /*
        $search                     = $this->request->getPost('search');

        $billsArray                 = $this->Engine->doBillSearch($search);
        $billsArray["billCount"]    = 1;
        $viewData["billsArray"]     = $billsArray;

        $user_id	= get_instance()->userInfo->id;
        $outData	= json_encode($viewData);
        $inpData	= json_encode($_POST);

        $dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => $inpData,
                            "output_data" => $outData , "au_type" => "14" );

        get_instance()->Engine->saveAuditActivityLogs($dataLogArr);

        return view('billing/bill_hist_disp', $viewData);
        */
    }
}