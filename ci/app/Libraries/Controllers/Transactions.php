<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;

class Transactions extends BaseController
{
    public function index(){
		return view('Headers/header')
            . view('Headers/page_header')
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

	public function history(){
		//echo "Dashboard Page";
		//var_dump($this->userInfo);
		
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
		
		return view('Headers/header')
            . view('Headers/page_header')
			. view('transaction' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
    }

	public function fetchuniqueref(){
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
		
		$responseArray["start"]	= $viewData["payData"]["start"];//json_encode($getPayArray);
		$responseArray["load"]	= $viewData["payData"]["load"];
		$responseArray["html"]	= view('dispPaymentData' , $viewData);
		die(json_encode($responseArray));		
		
	}
	
	public function fetchpopitems(){
		$viewData	= array();
		$item_id	= $this->request->getPost('item_id');
		$itemArr	= explode("_",$item_id);
		
		if( sizeof($itemArr) == 2 ){
			$req_type		= $itemArr["0"];
			$req_item_id	= $itemArr["1"];
			
			if($req_type = "trans"){
				$viewData["itemData"]	= $this->Engine->getPaymentItemData( $req_item_id );
				
				//$= view('dispPaymentData' , $viewData);
				//var_dump( $itemDataArray );
				
				return view('popPaymentDataItem' , $viewData);
			}
			else{
				//Return Reference
			}
		}
		else{
			//Unexpected format
		}
		
		var_dump($itemArr);
	}
	
	public function searchdata(){
		$responseArray	= array();
		$keyWord		= $this->request->getPost('searchWord');
		$sType			= $this->request->getPost('search');
		$start			= $this->request->getPost('start');
		//$keyWord		= "8123403687162306";
		//$sType			= "1";
		
		$getRefArray	= array("start" => $start , "keyWord" => $keyWord);
		
		if($sType == "1"){
			$viewData["payData"]	= $this->Engine->srchPaymentData( $getRefArray );
			$response	= view('dispPaymentData' , $viewData);
			
			$responseArray["start"]	= $viewData["payData"]["start"];
			$responseArray["load"]	= $viewData["payData"]["load"];
		}
		else{
			$viewData["refData"]	= $this->Engine->srchReferenceData( $keyWord );
			$response	= view('dispUniqueReference' , $viewData);
			
			$responseArray["start"]	= $viewData["refData"]["start"];
			$responseArray["load"]	= $viewData["refData"]["load"];
		}
		
		
		$responseArray["html"]	= $response;
		die(json_encode($responseArray));
	}


}