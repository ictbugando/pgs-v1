<?php
    $totalCredit    = 0;
    $totalDebit     = 0;
    $countW         = 1;
?>


<div id="divWalletStatement" class="">
    <!-- Tab navigation -->
    <ul class="nav nav-tabs" id="tabMenu" role="tablist" >
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="table1-tab" data-bs-toggle="tab" data-bs-target="#table1" type="button" role="tab" aria-controls="table1" aria-selected="true">Control Number Statement</button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="table2-tab" data-bs-toggle="tab" data-bs-target="#table2" type="button" role="tab" aria-controls="table2" aria-selected="false">Wallet Statement</button>
      </li>
    </ul>

    <!-- Tab content -->
    <div class="tab-content mt-3">
      <!-- Table 1 -->
      <div class="tab-pane fade show active" id="table1" role="tabpanel" aria-labelledby="table1-tab">
        <div class="table-responsive">
            <table class="table">
            <thead class="table-dark">
                <tr><th colspan="7"><h4 >Control Statement</h4></th></tr>
                <tr><th>#</th><th>Type</th><th>Reference</th><th>Debit</th><th>Credit</th><th>Balance</th><th>&nbsp;</th></tr>
            </thead>

            <?php foreach($patWalletLog AS $patWalletArr): ?>
                <?php
                    $totalCredit    = $totalCredit+$patWalletArr->bill_total_cost;
                    $totalDebit     = $totalDebit+$patWalletArr->amount;
                ?>
                <tr>
                    <td><?php echo $countW++; ?></td>
                    <td><?php echo $patWalletArr->postName; ?></td>
                    <td><?php echo $patWalletArr->myReference; ?></td>
                    <td><?php echo number_format($patWalletArr->bill_total_cost); ?></td>
                    <td><?php echo number_format($patWalletArr->amount); ?></td>
                    <td><?php echo number_format($patWalletArr->bal_amt); ?></td>

                    <td>
                        <?php if($patWalletArr->post_type == 1): ?>
                            <a href="#" class="txn-pop" id="trans_<?php echo $patWalletArr->trans_id; ?>">
                                <span><i class="fas fa-arrow-up-right-from-square"></i></span>
                            </a>
                        <?php else: ?>
                            <a href="#" class="showBillPop" id="<?php echo $patWalletArr->pat_id."_".$patWalletArr->bill_id; ?>">
                                <span><i class="fas fa-arrow-up-right-from-square"></i></span>
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <table class="table table-bordered">
            <tr>
                <th colspan="2"><center><h5>Statement Summary<h5></center></th>
            </tr>
            <tr>
                <th>Date.</th><td><?php echo date("Y-m-d H:i:s");?> </td>
            </tr>
            <tr>
                <th>File No.</th>
                <td><?php echo $patRecArray->pat_num; ?></td>
            </tr>
            <tr>
                <th>Patient Name.</th>
                <td><?php echo strtoupper($patRecArray->other_names." ".$patRecArray->sur_name); ?></td>
            </tr>
            <tr>
                <th>Total Credit.</th>
                <td>Tsh. <?php echo number_format($totalCredit); ?></td>
            </tr>
            <tr>
                <th>Total Debit.</th>
                <td>Tsh. <?php echo number_format($totalDebit); ?></td>
            </tr>
            <tr>
                <th>Wallet Balance.</th>
                <td>Tsh. <?php echo number_format($patRecArray->wallet_bal); ?></td>
            </tr>
            <tr>
                <th>Currency.</th><td>TZS</td>
            </tr>
            <tr>
                <td colspan="2">&nbsp;</td>
            </tr>
        </table>

        <input type="hidden" name="expUrlBmc" id="expUrlBmc" value="<?php echo base_url("/patients/wallet/".$patRecArray->pat_id);?>" />
            
        </div>
      </div>

      <!-- Table 2 -->
      <div class="tab-pane fade" id="table2" role="tabpanel" aria-labelledby="table2-tab">
        <div class="table-responsive">
          <table class="table table-bordered table-hover">
            <thead class="table-dark">
                <tr><th colspan="7"><h4 >Wallet Statement</h4></th></tr>
                <tr><th>#</th><th>Type</th><th>Reference</th><th>Open</th><th>Amount</th><th>Close</th><th>Date</th></tr>
            </thead>
            <?php $count = 1; ?>
            <tbody>
                <?php foreach($globWalletMasterArr AS $walletMasterArr): ?>
                    <tr>
                        <td><?php echo $count++; ?>.</td>
                        <td><?php echo $walletMasterArr->myType; ?></td>
                        <td><?php echo $walletMasterArr->myReference; ?></td>
                        <td><?php echo number_format($walletMasterArr->mstart_bal); ?></td>
                        <td>Tsh. <?php echo number_format($walletMasterArr->amount); ?></td>
                        <td>Tsh. <?php echo number_format($walletMasterArr->mclose_bal); ?></td>
                        <td><?php echo date_convert($walletMasterArr->siku); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>








<div id="divWalletStatement">


</div>
