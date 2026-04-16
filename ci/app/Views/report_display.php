<?php
    $walletsSumArr  = $malipoSummArr["walletTotals"];
    $startDate      = strtoupper(date("d-F-Y",$malipoSummArr["startDate"]));
    $stopDate       = strtoupper(date("d-F-Y",$malipoSummArr["stopDate"]));
    $patCountTotal  = 0;
    $count          = 1;
?>

<input type="hidden" name="reportStartD" id="reportStartD" value="0" />
<input type="hidden" name="reportStopD" id="reportStopD" value="0" />
<input type="hidden" name="reportFetchUrl" id="reportFetchUrl" value="<?php echo base_url('dashboard/fetchreport'); ?>" />

<table class="table table-bordered">
    <thead >
        <tr class="table-dark">
            <th>&nbsp;</th>
            <th><h6>PERIOD:</h6></th>
            <th colspan="2" ><h6><?php echo $startDate." to ".$stopDate; ?></h6></th>
        </tr>
        <tr><th>&nbsp;</th><th>Wallet</th><th>Transactions #</th><th>Amount Collected</th></tr>
    </thead>
    <tbody>
        <?php if( sizeof($walletsSumArr) == 0): ?>
            <tr>
                <td colspan="4">
                    <div class="alert alert-primary d-flex justify-content-center" role="alert">
                        There are no Transactions Recorded for this Period
                    </div>
                </td>
            </tr>
        <?php else: ?>
            <?php foreach($walletsSumArr AS $walletArr): ?>
                <?php 
                    $patCountTotal = ($patCountTotal+$walletArr["patCount"]);
                ?>
                <tr>
                    <td><?php echo $count++; ?></td>
                    <td><?php echo $walletArr["name"]; ?></td>
                    <td><?php echo $walletArr["txnCount"]; ?></td>
                    <td>Tsh. <?php echo number_format($walletArr["total"]); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
    <tfoot>
        <th></th>
        <th>Total</th>
        <th><?php echo $malipoSummArr["walletTxnTotals"]; ?></th>
        <th>Tsh. <?php echo number_format($malipoSummArr["walletGrandTotals"]); ?></th>
    </tfoot>
</table>