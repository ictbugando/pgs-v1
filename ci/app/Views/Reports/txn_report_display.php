<?php
    $walletsSumArr  = $malipoSummArr["walletTotals"];
    $startDate      = date("H:i:s d-F-Y",$malipoSummArr["startDate"]);
    $stopDate       = date("H:i:s d-F-Y",$malipoSummArr["stopDate"]);
    $start          = strtoupper( $startDate );
    $stop           = strtoupper( $stopDate );
?>

<div class="row align-items-start">
    <div class="col-8"><h3>Transactions Summary</h3></div>
    <div class="col-4">
        <button type="button" id="2" class="btn btn-secondary exp-report" >
            <b><span><i class="fa-solid fa-print"></i></span> Print</b>
        </button>
        <button type="button" id="1" class="btn btn-primary exp-report" >
            <b><span><i class="fa-solid fa-file-arrow-down"></i></span> Export</b>
        </button>
    </div>
</div>

<h6><b>PERIOD:&nbsp;&nbsp;&nbsp;<?php echo $startDate." <--> ".$stopDate; ?></b></h6>

<?php if( sizeof($walletsSumArr) == 0): ?>
    <div class="alert alert-primary d-flex justify-content-center" role="alert">
        There are no Transactions Recorded for this Period
    </div>
<?php else: ?>
    <?php
        $reportDetArr   = $malipoSummArr["detArr"];
        $loadMore       = $malipoSummArr["payLoadMore"];
        $mWalletId      = 99;
        $count          = 1;

        if( sizeof($walletsSumArr) > 1){
            //You are fetching all
            $mWalletId = 0;
        }
        else if( sizeof($walletsSumArr) == 1){
            $mWalletId = array_key_first($walletsSumArr);
        }
        
        $morePayData		= "";
        $finishedPayData	= "";

        $loadMore == "1" ? $morePayData = "hideMe" : $finishedPayData = "hideMe";
    ?>

    <input type="hidden" name="reportStartD" id="reportStartD" value="<?= $malipoSummArr["startDate"]; ?>" />
    <input type="hidden" name="reportStopD" id="reportStopD" value="<?= $malipoSummArr["stopDate"]; ?>" />

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr><th colspan="3"></th><th colspan="2">Amount Collected</th><th>&nbsp;</th></tr>
            <tr><th>&nbsp;</th><th>Gateway</th><th>Transactions #</th><th>Control Number</th><th>Wallet</th><th>Total</th></tr>
        </thead>
        <tbody>
            <?php foreach($walletsSumArr AS $walletArr): ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo $walletArr["name"]; ?></td>
                    <td><?php echo number_format($walletArr["txnCount"]); ?></td>
                    <td>Tsh. <?php echo number_format($walletArr["walletTotal"]); ?></td>
                    <td>Tsh. <?php echo number_format($walletArr["controlTotal"]); ?></td>
                    <td>Tsh. <?php echo number_format($walletArr["total"]); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <th></th>
            <th>Total</th>
            <th><?php echo number_format($malipoSummArr["walletTxnTotals"]); ?></th>
            <th>Tsh. <?php echo number_format($malipoSummArr["walletDepositTotals"]); ?></th>
            <th>Tsh. <?php echo number_format($malipoSummArr["walletControlTotals"]); ?></th>
            <th>Tsh. <?php echo number_format($malipoSummArr["walletGrandTotals"]); ?></th>
        </tfoot>
    </table>

    <hr />
    <div class="row align-items-start">
        <div class="col-10"><h3>Transaction Details</h3></div>
        <div class="col-2"></div>
    </div>
    
    <?php
    $count = 1;
    ?>
    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <td>#</td><td>Gateway</td><td>Receipt</td><td>Control Number</td><td>Amount</td><td>Date</td><td>Phone</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach($reportDetArr as $payArr): ?>
                <tr>
                    <td><?php echo $count++ ?></td>
                    <td><?php echo $payArr->wallet_name; ?></td>
                    <td><?php echo $payArr->receipt; ?></td>
                    <td><?php echo $payArr->reference; ?></td>
                    <td>Tsh. <?php echo number_format($payArr->amount); ?></td>
                    <td><?php echo date_convert(date("Y-m-d H:i:s" ,$payArr->trans_date)); ?></td>
                    <td><?php echo $payArr->msisdn; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div id="payDataLoadMore" class="d-flex justify-content-center">
        <center>
        <div id="moreRefData" class="<?php echo $morePayData; ?>">
            <a href="#">LOAD MORE RECORDS</a>
        </div>
        <div id="finishedRefData" class="<?php echo $finishedPayData; ?>">
        <a href="#">NO MORE RECORDS</a>
        </div>
        </center>
	</div>

    <input type="hidden" name="expUrlBmc" value="<?php echo base_url("dashboard/exportreport/".$malipoSummArr["startDate"]."/".$malipoSummArr["stopDate"])."/".$mWalletId; ?>" />
<?php endif; ?>