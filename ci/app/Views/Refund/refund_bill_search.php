<?php
    $billRefundCount = 1;
?>
<form method="post" action="<?php echo base_url('refund/savenewrefund'); ?>">
    <table class="table">
            <?php if( sizeof($billSearchArr) == 0 ): ?>
                <tr>
                    <td colspan="7">
                        <div class="alert alert-primary d-flex justify-content-center" role="alert">
                        The bill refund record entered does not exist
                        </div>
                    </td>
                </tr>
            <?php else: ?>
            <tr>
                <td colspan="7">
                <table class="table">
                    <tr>
                        <td><b>NAME</b></td>
                        <td><?php echo strtoupper($billSearchArr[0]->sur_name." ".$billSearchArr[0]->other_names) ; ?></td>
                        <td><b>PATIENT NUMBER</b></td>
                        <td><?php echo $billSearchArr[0]->pat_num; ?></td>
                    </tr>
                </table>
                </td>
            </tr>
            <tr class="table-dark">
                <td>#</td><td>SELECT</td><td>BILL #</td><td>BILL TOTAL</td><td>PAID AMOUNT</td><td>REFUND AMOUNT</td><td>PREVIEW</td>
            </tr>
            <?php foreach($billSearchArr AS $billArr): ?>
            <tr>
                <td><?php echo $billRefundCount++; ?>.</td>
                <td>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="bills[]" value="<?php echo $billArr->bill_id; ?>" />
                    </div>
                </td>
                <td><?php echo $billArr->bill_num; ?></td>
                <td>TSH. <?php echo number_format($billArr->bill_total_cost); ?></td>
                <td>TSH. <?php echo number_format($billArr->bill_total_paid); ?></td>
                <td>
                    <div class=" form-group" style="width:140px;">
                        <input type="text" name="refundamt_<?php echo $billArr->bill_id; ?>" class="form-control" >
                    </div>
                </td>
                <td><a href="<?php echo base_url('refund/getrefundform'); ?>" target="_blank"><span><i class="fas fa-arrow-up-right-from-square"></i></span></a></td>
            </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    <div class="d-grid gap-2 col-6 mx-auto">
        <button type="submit" class="btn btn-primary saverefund">Save Record</button>
    </div>
</form>