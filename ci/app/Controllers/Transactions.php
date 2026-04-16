<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use SimpleSoftwareIO\QrCode\Generator;

class Transactions extends BaseController
{
    private function callPermisionCheck(){
        if( !getAccessTransactionMenu() ){
            die(header('Location: '.base_url("dashboard")));
        }
    }

    public function index(){
		$this->callPermisionCheck();

		return view('Headers/header')
            . view('Headers/page_header')
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

	public function history(){
		$this->callPermisionCheck();
		
		$viewData		= array();
		$innerViewData	= array();
		
		$getRefArray	= array("start" => "0" , "rstatus" => "99", "mwezi" => (int)date('m') , "mwaka" => date('Y'));

		$getPayArray	= array("start" => "0" , "wallet" => "99", "pstatus" => "99", "mwezi" => (int)date('m') , 
								"mwaka" => date('Y'));
		
		$innerViewData	= array("refData" => $this->Engine->getGeneratedReference($getRefArray) );
		$viewData['refDataView']	= view('dispUniqueReference' , $innerViewData);
		$viewData['refStart']		= $innerViewData["refData"]["start"];
		$viewData['refLoadMore']	= $innerViewData["refData"]["load"];
		
		$innerViewData	= array("payData" => $this->Engine->getPaymentData($getPayArray) );
		$viewData['payDataView']	= view('dispPaymentData' , $innerViewData);
		$viewData['payStart']		= $innerViewData["payData"]["start"];
		$viewData['payLoadMore']	= $innerViewData["payData"]["load"];
		
		$viewData['monthArr']		= $this->Engine->getmonth();
		$viewData['myWallets']		= $this->Engine->getWallets();
		$viewData['myTxStatus']		= $this->Engine->getTxStatus();


		$user_id	= $this->userInfo->id;
		$outData	= json_encode($innerViewData["payData"]);
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => "",
							"output_data" => $outData , "au_type" => "3" );
							
		$this->Engine->saveAuditActivityLogs($dataLogArr);
		
		return view('Headers/header')
            . view('Headers/page_header')
			. view('transaction' , $viewData)
			. view('transPreviewPop')
			. view('Headers/page_footer')
            . view('Headers/footer');
    }

	public function fetchuniqueref(){
		$this->callPermisionCheck();

		$viewData	= array();
		
		$mwezi		= $this->request->getPost('mwezi');
		$mwaka		= $this->request->getPost('mwaka');
		$start		= $this->request->getPost('start');
		$rstatus	= $this->request->getPost('rstatus');
		
		$getRefArray	= array("start" => $start , "rstatus" => $rstatus, "mwezi" => $mwezi , "mwaka" => $mwaka );
		
		$viewData["refData"]	= $this->Engine->getGeneratedReference( $getRefArray );
		
		$responseArray["start"]	= $viewData["refData"]["start"];
		$responseArray["load"]	= $viewData["refData"]["load"];
		$responseArray["html"]	=  view('dispUniqueReference' , $viewData);
		die(json_encode($responseArray));
	}
	
	public function fetchpayments(){
		$this->callPermisionCheck();
		
		$viewData		= array();
		$responseArray	= array();
		
		$wallet		= $this->request->getPost('wallet');
		$pstatus	= $this->request->getPost('pstatus');
		$mwezi		= $this->request->getPost('mwezi');
		$mwaka		= $this->request->getPost('mwaka');
		$start		= $this->request->getPost('start');
		
		$getPayArray	= array("start" => $start , "wallet" => $wallet, "pstatus" => $pstatus, 
								"mwezi" => $mwezi , "mwaka" => $mwaka);
		
		$viewData["payData"]	= $this->Engine->getPaymentData( $getPayArray );

		$user_id	= $this->userInfo->id;
		$outData	= json_encode($viewData["payData"]);
		$inpData	= json_encode($_POST);
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => $inpData,
							"output_data" => $outData , "au_type" => "5" );

		$this->Engine->saveAuditActivityLogs($dataLogArr);
		
		$responseArray["start"]	= $viewData["payData"]["start"];
		$responseArray["load"]	= $viewData["payData"]["load"];
		$responseArray["html"]	= view('dispPaymentData' , $viewData);
		die(json_encode($responseArray));		
		
	}
	
	public function fetchpopitems($item_id = "trans_148"){
		$this->callPermisionCheck();

		require FCPATH . '../ci/app/vendor/autoload.php';

		$qrcode		= new Generator;
		$qrSimple	= "";
		$viewData	= array("qrSimple" => $qrSimple);

		if( isset($_POST["item_id"]) ){
			$item_id	= $this->request->getPost('item_id');
		}
		
		$itemArr	= explode("_",$item_id);
		
		if( sizeof($itemArr) == 2 ){
			$req_type		= $itemArr["0"];
			$req_item_id	= $itemArr["1"];
			
			if($req_type == "trans"){
				$itemDataArray			= $this->Engine->getPaymentItemData( $req_item_id );

				if( isset($itemDataArray->trans_id) ){
					$searchKey	= $itemDataArray->trans_id."/".$itemDataArray->receipt;
					$verifUrl	= "https://bmc.lipaswitch.co.tz/verify/qrcode/".$searchKey;//base_url("verify/qrcode/".$searchKey);
					$qrSimple	= $qrcode->size(120)->generate( $verifUrl );
				}

				$viewData["itemData"]	= $itemDataArray;
				$viewData["qrSimple"]	= $qrSimple;

				$user_id	= $this->userInfo->id;
				$outData	= json_encode($viewData["itemData"]);
				$inpData	= json_encode($_POST);
				
				$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => $inpData,
									"output_data" => $outData , "au_type" => "4" );
		
				$this->Engine->saveAuditActivityLogs($dataLogArr);
				
				return view('popPaymentDataItem' , $viewData);
			}
			else{
				//Return Reference
				echo 'Reference';
			}
		}
		else{
			//Unexpected format
			echo 'Unexpected Input';
		}
	}
	
	public function searchdata(){
		$this->callPermisionCheck();

		$responseArray	= array();
		$keyWord		= $this->request->getPost('searchWord');
		$sType			= $this->request->getPost('search');
		$start			= $this->request->getPost('start');
		
		$getRefArray	= array("start" => $start , "keyWord" => $keyWord);
		
		$viewData["payData"]	= $this->Engine->srchPaymentData( $getRefArray );
		$response	= view('dispPaymentData' , $viewData);

		$user_id	= $this->userInfo->id;
		$outData	= json_encode($viewData["payData"]);
		$inpData	= json_encode($_POST);
		
		$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => $inpData,
							"output_data" => $outData , "au_type" => "4" );

		$this->Engine->saveAuditActivityLogs($dataLogArr);
		
		$responseArray["start"]	= $viewData["payData"]["start"];
		$responseArray["load"]	= $viewData["payData"]["load"];		
		$responseArray["html"]	= $response;
		die(json_encode($responseArray));
	}


}