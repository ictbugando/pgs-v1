<?php
$start	= $payData["start"];
$ct		= $payData["ct"];
$count	= ( ($start-$ct)+1 );

unset($payData["start"]);
unset($payData["ct"]);
unset($payData["load"]);
?>
<?php foreach($payData as $payArr): ?>
	<tr>
		<td><?php echo $count++ ?>.</td>
		<td><?php echo $payArr->pat_num; ?></td>
		<td style="max-width:350px;" class="hideMobile"><?php echo strtoupper($payArr->other_names." ".$payArr->sur_name); ?></td>
		<td class="hideMobile"><?php echo $payArr->wallet_name; ?></td>
		<td><?php echo $payArr->receipt; ?></td>
		<td><?php echo $payArr->reference; ?></td>
		<td>Tsh. <?php echo number_format($payArr->amount); ?></td>
		<td class="hideMobile"><?php echo date_convert(date("Y-m-d H:i:s" ,$payArr->trans_date)); ?></td>
		<td>
			<div class="txn-pop" id="trans_<?php echo $payArr->trans_id; ?>">
				<a href="#"><i class="fas fa-arrow-up-right-from-square"></i></a>
			<div>
		</td>
	</tr>
<?php endforeach; ?>