
<ul class="myfilter-tabs " style="">
    <li>
        <div class="form-group">
            <select class="form-select " name="payReportWallet" id="payReportWallet"> 
                <option value="99">Wallet - All</option>
                <?php foreach($myWallets AS $myWallet): ?>
                    <option value="<?php echo $myWallet->wallet_id; ?>"><?php echo $myWallet->wallet_alias; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </li>
    <li> 
        <div class="form-group"> 
            <select class="form-select " name="payReportDay" id="payReportDay"> 
                <option value="99">Day - All</option>
                <?php for($i=1;$i<=31;$i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </li>
    <li class=""> 
        <div class="form-group">
            <select class="form-select " name="payReportMwezi" id="payReportMwezi">
                <option value ="<?php echo $curr_month; ?>"><?php echo $months[$curr_month]; ?></option>
                <?php foreach($months AS $key => $value): ?>
                    <option value ="<?php echo $key; ?>"><?php echo $value; ?></option>';
                <?php endforeach; ?>
            </select>
        </div>
    </li>
    <li>
        <div class="form-group">
            <select class="form-select " name="payReportMwaka" id="payReportMwaka">
                <?php	
                    $mwaka	= $curr_year;
                    while($mwaka >= $year){
                        echo '<option value="'.$mwaka.'">'.$mwaka.'</option>';
                        $mwaka = $mwaka - 1;
                    }
                ?>
            </select>
        </div>
    </li>
    <li><div style="top:-10px;position:relative;">Custom Range: </div></li>
    <li style="width:185px;">
        <div class="form-group">
            <input class="form-select " type="text" id="mstartdate" placeholder="Start Date">
        </div>
    </li>
    <li style="width:185px;">
        <div class="form-group">
            <input class="form-select " type="text" id="menddate" placeholder="End Date">
        </div>
    </li>
</ul>
<hr />


<div id="dispBMCReport"><?php require_once("txn_report_display.php"); ?></div>

<input type="hidden" name="reportFetchUrl" id="reportFetchUrl" value="<?php echo base_url('dashboard/fetchreport'); ?>" />

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="staticBackdropLabel">Transaction Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body"><div id="printPrevReportBmc">&nbsp;</div></div>
    </div>
  </div>
</div>
