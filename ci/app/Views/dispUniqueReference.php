<?php
$start	= $refData["start"];
$ct		= $refData["ct"];
$count	= ( ($start-$ct)+1 );

unset($refData["start"]);
unset($refData["ct"]);
unset($refData["load"]);
//var_dump($refData);
//die();
//echo '<br / >Blah blah blah';
?>
<?php foreach($refData as $refArr): ?>
	<tr>
		<td><?php echo $count++ ?></td>
		<td><?php echo $refArr->token_ext_reference; ?></td>
		<td><?php echo $refArr->token_unique_id; ?></td>
		<td><?php echo number_format($refArr->token_ext_amt); ?></td>
		<td class="hideMobile"><?php echo $refArr->token_ext_phone; ?></td>
		<td class="hideMobile"><?php echo $refArr->token_ext_date; ?></td>
		<td><?php echo $refArr->token_date; ?></td>
		<td>
			<div class="txn-list-status">
			<span style="color:<?php echo $refArr->st_color; ?>;"><i class="<?php echo $refArr->st_icon ; ?>"></i></span>
			</div>
		</td>
		<td>
			<div class="txn-pop" id="trans_<?php echo $refArr->token_id; ?>">
				<span><i class="fas fa-cog"></i></span>
			<div>
		</td>
	</tr>
<?php endforeach; ?>