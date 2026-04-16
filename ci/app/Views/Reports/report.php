<?php
    $monthArr       = get_instance()->Engine->getmonth();
    $myWallets		= get_instance()->Engine->getWallets();
    $myTxStatus		= get_instance()->Engine->getTxStatus();

    $malipoSummArr          = get_instance()->Engine->getMalipoSummary($startDate = 0, $stopDate = 0, $wallet = 0);

    $globWalletMasterArr    = get_instance()->Engine->getGlobalMasterStatement($start = 0, $type = 0, $pat_id = 0 , $limit = 500 );

    $curr_year      = $monthArr['curr_year'];
    $curr_month     = $monthArr['curr_month'];
    $months         = $monthArr['months'];
    $year           = 2022;
?>
<div>
    <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
            <div style="margin-top:10px;"><h4><b>REPORTS | <span>&nbsp;</span></b></h4></div>
            <button class="nav-link active" id="nav-txns-tab" data-bs-toggle="tab" data-bs-target="#nav-txns" type="button" role="tab" aria-controls="nav-txns" aria-selected="true">Transactions</button>
            <button class="nav-link" id="nav-wallet-tab" data-bs-toggle="tab" data-bs-target="#nav-wallet" type="button" role="tab" aria-controls="nav-wallet" aria-selected="true">Wallet Statement</button>
            <!--
            <button class="nav-link" id="nav-bills-tab" data-bs-toggle="tab" data-bs-target="#nav-bills" type="button" role="tab" aria-controls="nav-bills" aria-selected="false">Billing</button>
            <button class="nav-link" id="nav-refund-tab" data-bs-toggle="tab" data-bs-target="#nav-refund" type="button" role="tab" aria-controls="nav-refund" aria-selected="false">Refund</button>
            <div class="txn-page-reload" ><span><i class="fa-solid fa-arrows-rotate"></i></span></div>
            -->
        </div>
    </nav>

    <div class="tab-content" id="nav-tabContent">
        <div class="tab-pane fade show active" id="nav-txns" role="tabpanel" aria-labelledby="nav-txns-tab">
            <?php require_once("txn_report_wrap.php"); ?>
        </div>

        <div class="tab-pane fade" id="nav-wallet" role="tabpanel" aria-labelledby="nav-wallet-tab">
            <?php require_once("wallet_report_wrap.php"); ?>
        </div>
        
        <div class="tab-pane fade" id="nav-bills" role="tabpanel" aria-labelledby="nav-bills-tab">
            <?php require_once("bill_report_wrap.php"); ?>
        </div>

        <div class="tab-pane fade" id="nav-refund" role="tabpanel" aria-labelledby="nav-refund-tab">
            <?php require_once("refund_report_wrap.php"); ?>
        </div>
        
    </div>

</div>