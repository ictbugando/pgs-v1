<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;

class Refund extends BaseController
{
	public function index(){
		$viewData		= array();
		$start			= 0;

		//$billSearchArr	= $this->Engine->searchRefundBills($custid = 1);
		$viewData["billSearchArr"]	= $this->Engine->searchRefundBills($custid = 1);
		
		return view('Headers/header')
            . view('Headers/page_header')
			. view('Refund/refund' , $viewData)
			. view('Headers/page_footer')
            . view('Headers/footer');
	}

	public function searchrefundbills($pWord = "1"){
		$viewData	= array();

		if( isset($_POST['pWord'])){
			$pWord		= $this->request->getPost('pWord');
		}
		

		$viewData["billSearchArr"]	= $this->Engine->searchRefundBills($pWord);

		return view('Refund/refund_bill_search' , $viewData);
	}

	public function getrefundform(){

	}

	public function savenewrefund(){
		$billArr	= $this->request->getPost('bills');
		//$pWord		= $this->request->getPost('pWord');
		//$pWord		= $this->request->getPost('pWord');

		foreach( $billArr AS $billId){
			$refundAmount	= $this->request->getPost('refundamt_'.$billId );

			echo $billId.' :: '.$refundAmount.'<br />';
		}

		//var_dump($billArr);
	}

}