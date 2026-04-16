<?php

namespace App\Controllers;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\CMS\User\UserHelper;
use SimpleSoftwareIO\QrCode\Generator;

class Verify extends BaseController
{
    public function index(){

	}

    public function qrcode($trans_id = null , $receipt = null){
        require FCPATH . '../ci/app/vendor/autoload.php';

        $viewData   = array();
        $resArray	= get_instance()->Engine->verifyReceipt( $trans_id , $receipt );

        if( isset($resArray->trans_id) ){
            $searchKey	= $resArray->trans_id."/".$resArray->receipt;
            $verifUrl	= base_url("verify/qrcode/".$searchKey);

            $qrcode		= new Generator;
            $qrSimple	= $qrcode->size(120)->generate( $verifUrl );

            $viewData	= array("qrSimple" => $qrSimple , "itemData" => $resArray);
            
        }

        return view('Headers/header')
                .view('transVerifyMessage' , $viewData )
                .view('popPaymentDataItem' , $viewData)
                .view('Headers/footer');
	}
}