<?php
$user_id	= get_instance()->userInfo->id;
$outData	= json_encode($billsArray);

$dataLogArr	= array("user_id" => $user_id, "item_id" => "0", "input_data" => "",
                    "output_data" => $outData , "au_type" => "12" );

get_instance()->Engine->saveAuditActivityLogs($dataLogArr);

$start	= $billsArray["start"];
$ct		= $billsArray["ct"];
$word	= $billsArray["word"];
$count  = ( ($start-$ct)+1 );

$payLoadMore = $billsArray["load"];

$moreBillData		= "";
$finishBillData     = "";

unset($billsArray['ct']);
unset($billsArray['start']);
unset($billsArray['load']);
unset($billsArray['word']);

//var_dump( $billsArray );

$payLoadMore == "1" ? $finishBillData = "hideMe" : $moreBillData = "hideMe";
?>
<?php foreach($billsArray AS $billsArr): ?>
	<?php if(!$billsArr->is_hidden): ?>
    <tr>
        <td><?php echo $count++; ?>.</td>
        <td><?php echo $billsArr->bill_num; ?></td>
        <td style="max-width:350px;"><?php echo strtoupper($billsArr->sur_name." ".$billsArr->other_names ); ?></td>
        <td><?php echo "Tsh ".number_format($billsArr->bill_total_cost); ?></td>
        <td><?php echo "Tsh ".number_format($billsArr->bill_total_paid); ?></td>
        <td><?php echo $billsArr->staffname; ?></td>
        <td><?php echo date_convert($billsArr->bill_siku); ?></td>
        <td style="font-size:11px;" >
            <?php if( $billsArr->is_closed == "1" ): ?>
                <p class=""><span class="px-3 py-1 text-light bg-success rounded-pill align-middle">PAID</span></p>
            <?php elseif( $billsArr->is_closed == "2" ): ?>
                <p class=""><span class="px-3 py-1 text-dark bg-warning rounded-pill align-middle">PARTIAL</span></p>
            <?php else: ?>
                <span class="px-3 py-1 text-light bg-danger rounded-pill align-middle">PENDING</span>
            <?php endif; ?>
        </td>
        <td>
            <a href="#" class="showBillPop" id="<?php echo $billsArr->pat_id."_".$billsArr->bill_id; ?>">
                <span><i class="fas fa-arrow-up-right-from-square"></i></span>
            </a>
        </td>                
    </tr>
	<?php endif; ?>
<?php endforeach; ?>
<tr id="prependBillLoad">
    <td colspan="9">
        <div id="payDataLoadMore" class="d-flex justify-content-center">
            <div id="moreShowData" class="billLoadMore <?php echo $moreBillData; ?>"><p>LOAD MORE RECORDS</p></div>
            <div id="finishedShowData" class="<?php echo $finishBillData; ?>"><p>NO MORE RECORDS</p></div>
        </div>

		<input type="hidden" value="<?php echo $start; ?>" name="billHistoStart" id="billHistoStart"/>
		<input type="hidden" value="<?php echo $word; ?>" name="billListLoadWord" id="billListLoadWord" />    
    </td>
</tr>
