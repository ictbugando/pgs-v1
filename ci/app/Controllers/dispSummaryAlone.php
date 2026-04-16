<?php
    $count = 1;
?>
<table class="table table-bordered">
    <thead class="table-dark">
        <tr>
            <td colspan="6">
                <h6><b>PERIOD:&nbsp;&nbsp;&nbsp;<?php echo $startDate." <--> ".$stopDate; ?></b></h6>
            </td>
        </tr>        
        <tr><th colspan="3"></th><th colspan="2">Amount Collected</th><th>&nbsp;</th></tr>
        <tr><th>&nbsp;</th><th>Source</th><th>Transactions #</th><th>Control Number</th><th>Wallet</th><th>Total</th></tr>
    </thead>
    <tbody>
        <?php foreach($walletsSumArr AS $walletArr): ?>
            <tr>
                <td><?php echo $count++; ?></td>
                <td><?php echo $walletArr["name"]; ?></td>
                <td><?php echo $walletArr["txnCount"]; ?></td>
                <td>Tsh. <?php echo number_format($walletArr["walletTotal"]); ?></td>
                <td>Tsh. <?php echo number_format($walletArr["controlTotal"]); ?></td>
                <td>Tsh. <?php echo number_format($walletArr["total"]); ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <th></th>
        <th>Total</th>
        <th><?php echo $malipoSummArr["walletTxnTotals"]; ?></th>
        <th>Tsh. <?php echo number_format($malipoSummArr["walletDepositTotals"]); ?></th>
        <th>Tsh. <?php echo number_format($malipoSummArr["walletControlTotals"]); ?></th>
        <th>Tsh. <?php echo number_format($malipoSummArr["walletGrandTotals"]); ?></th>
    </tfoot>
</table>