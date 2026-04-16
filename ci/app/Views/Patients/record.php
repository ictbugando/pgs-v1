<?php if( !isset($patRecArray->pat_id) ): ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading">Not found!</h4>
        <p>The patient record can not be found on the system. Contact system administrator for assistance</p>
    </div>
<?php else: ?>

<?php
    $patBalance     = 0;
    $pat_id         = $patRecArray->pat_id;
    $patPayArray	= get_instance()->Engine->getPatinetPayments( $patRecArray->pat_id , $start = 0 );
    $patBillArray	= get_instance()->Engine->getPatienttBills( $patRecArray->pat_id );
    $walletBalArr   = get_instance()->Engine->getLastMasterEntry($patRecArray->pat_id , "99");

    $globWalletMasterArr    = get_instance()->Engine->getGlobalMasterStatement($start = 0, $type = 0, $pat_id , $limit = 50 );

    if ( isset( $walletBalArr->close_bal ) ){
        $patBalance	= $walletBalArr->close_bal;
    }

    $payCount       = 1;
    $billCount      = 1;

    $patWalletLog	= get_instance()->Engine->getWalletLogs($patRecArray->pat_id);
?>
<div class="row align-items-start">
    <div class="col"><h3>Patients Details</h3></div>
    <div class="col">&nbsp;</div>
    <div class="col">
        <!--
        <button type="button" class="btn btn-success newPatientPop" ><i class="fa-solid fa-pen-to-square"></i> EDIT</button>
        -->
        <!--
        <a href="<?php echo base_url('billing/newbill/'.$patRecArray->pat_id); ?>" class="btn btn-primary" >
            <i class="fa-solid fa-plus"></i> NEW BILL
        </a>
        -->
    </div>
</div>
<hr>

<table class="table table-striped table-bordered">
    <tr><th colspan="6">PERSONAL DETAILS</th></tr>
    <tr>
        <th>File Number</th><td><?php echo $patRecArray->pat_num; ?></td>
        <th>Name</th><td><?php echo strtoupper($patRecArray->other_names." ".$patRecArray->sur_name); ?></td>
        <th>Phone</th><td><?php echo $patRecArray->phone; ?></td>
    </tr>
    <tr>
        <th>Walet Blance</th><td><b>Tsh. <?php echo number_format( $patBalance ); ?></b></td>
        <th>Birth Date</th><td><?php echo date("Y-m-d", strtotime($patRecArray->dob)); ?></td>
        <th>Home Adress</th><td><?php echo $patRecArray->mkoa_name."-".$patRecArray->wilaya_name."-".$patRecArray->kata_name; ?></td>
    </tr>
    <tr>
        <th>Next of Kin</th>
        <th>Kin Name</th><td><?php echo strtoupper($patRecArray->next_of_kin_name); ?></td>
        <th>Kin Phone</th><td><?php echo $patRecArray->next_of_kin_phone; ?></td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td colspan="2">
            <center><a href="<?= base_url("patients/exportpatstatement/".$pat_id); ?>" target="_blank" >EXPORT WALLET STATEMENT</a><center>
        </td>
        <td colspan="4">&nbsp;</td>
    </tr>
</table>

<div class="container">
    <div class="row">
        <div class="col-6 ">
            <?php require_once("dispWalletStatement.php"); ?>
        </div>

        <div class="col-6 border ">
            <?php //var_dump($patPayArray); ?>
            <table class="table table-striped table-bordered" >
                <thead class="table-dark">
                    <tr><th colspan="6"><h4 >Payment History</h4></th></tr>
                    <tr><th>#</th><th>Receipt</th><th>Amount</th><th>Bank</th><th>Date</th><th>&nbsp;</th></tr>
                </thead>
                <tbody>
                    <?php foreach($patPayArray AS $payArr): ?>
                        <tr>
                            <th><?php echo $payCount++; ?>.</th>
                            <td><?php echo $payArr->receipt; ?></td>
                            <td>Tsh. <?php echo number_format($payArr->amount); ?></td>
                            <td><?php echo $payArr->wallet_alias; ?></td>
                            <td><?php echo date_convert(date("Y-m-d H:i:s",$payArr->trans_date)); ?></td>
                            <td>
                                <a href="#" class="txn-pop" id="trans_<?php echo $payArr->trans_id; ?>">
                                    <span><i class="fas fa-arrow-up-right-from-square"></i></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex align-items-center justify-content-center">
                <a href="<?php echo base_url('transactions/history/'.$patRecArray->pat_id); ?>" >Load More Records</a>
            </div><br />


            <table class="table table-striped table-bordered" >
                <thead class="table-dark">
                    <tr><th colspan="6"><h4 >Billing History</h4></th></tr>
                    <tr><th>#</th><th>Number</th><th>Cost</th><th>Paid</th><th>Date</th><th>&nbsp</th></tr>
                </thead>
                <tbody>
                    <?php foreach($patBillArray AS $billArr): ?>
                        <tr>
                            <th><?php echo $billCount++; ?>.</th>
                            <td><?php echo $billArr->bill_num; ?></td>
                            <td>Tsh. <?php echo number_format($billArr->bill_total_cost); ?></td>
                            <td>Tsh. <?php echo number_format($billArr->bill_total_paid); ?></td>
                            <td><?php echo date_convert($billArr->bill_siku); ?></td>
                            <td>
                                <a href="#" class="showBillPop" id="<?php echo $billArr->pat_id."_".$billArr->bill_id; ?>">
                                    <span><i class="fas fa-arrow-up-right-from-square"></i></span>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</div>

<?php endif; ?>