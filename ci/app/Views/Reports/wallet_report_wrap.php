<?php
$count = 1;
?>
<table class="table table-bordered">
    <h3>Wallet Statement</h3>
    <thead class="table-dark">
        <tr><th>&nbsp;</th>
        <th>Type</th>
        <th>Patient Name</th>
        <th>Reference</th>
        <th>Opening Balance</th>
        <th>Amount</th>
        <th>Closing Balance</th>
        <th>Date</th></tr>
    </thead>
    <tbody>
        <?php foreach($globWalletMasterArr AS $walletMasterArr): ?>
            <?php
                $type       = $walletMasterArr->entry_type == "1" ? "Deposit" : "Payment";
                $reference  = $walletMasterArr->entry_type == "1" ? $walletMasterArr->reference : $walletMasterArr->control_num;
            ?>
            <tr>
                <td><?php echo $count++; ?>.</td>
                <td><?php echo $type; ?></td>
                <td>
                    <a href="<?php echo base_url("patients/record/".$walletMasterArr->pat_id); ?>" style="text-decoration:none;font-size:14px;">
                        <?php echo strtoupper($walletMasterArr->other_names." ".$walletMasterArr->sur_name); ?>
                    </a>
                </td>
                <td><?php echo $reference; ?></td>
                <td><?php echo number_format($walletMasterArr->start_bal); ?></td>
                <td>Tsh. <?php echo number_format($walletMasterArr->amount); ?></td>
                <td>Tsh. <?php echo number_format($walletMasterArr->close_bal); ?></td>
                <td>Tsh. <?php echo $walletMasterArr->siku; ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>