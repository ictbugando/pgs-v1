<?php
    $monthArr       = get_instance()->Engine->getmonth();
    $myWallets		= get_instance()->Engine->getWallets();
    $myTxStatus		= get_instance()->Engine->getTxStatus();

    $malipoSummArr	= get_instance()->Engine->getMalipoSummary($startDate = 0, $stopDate = 0, $wallet = 0);

    $curr_year      = $monthArr['curr_year'];
    $curr_month     = $monthArr['curr_month'];
    $months         = $monthArr['months'];
    $year           = 2022;
?>
<div>
<ul class="myfilter-tabs clearfix">
        <li style="width:320px;">
            <h4><b>TRANSACTIONS REPORT | </b></h4>
        </li>
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
    </ul>
</div>
<hr />

<div id="dispBMCReport">
    <?php require_once("report_display.php"); ?>
</div>