<?php
$moreShowData		= "";
$finishedShowData	= "";

$startArr       = $productsArray["start"];
$count          = ($startArr["prevstart"] + 1);
$word           = $startArr["word"];
$type           = $startArr["type"];
$showLoadMore   = $startArr["load"];
$nextstart      = $startArr["nextstart"];

$showLoadMore == "0" ? $moreShowData = "hideMe" : $finishedShowData = "hideMe";

unset($productsArray["start"]);
?>
<?php foreach($productsArray AS $prodArr): ?>
    <tr>
        <td><?php echo $count++; //var_dump($prodArr); ?></td>
        <td><?php echo $prodArr->prod_name ; ?></td>
        <td>Tsh. <?php echo number_format($prodArr->cost_amt); ?></td>
        <td><?php echo $prodArr->prod_desc ; ?></td>
        <td>&nbsp;</td>
    </tr>
<?php endforeach; ?>
<tr id="patListExtra">
    <td colspan="9">
		<div id="payDataLoadMore" class="d-flex justify-content-center">
			<div id="moreShowData" class="prodLoadMore <?php echo $moreShowData; ?>"><p><a href="#">LOAD MORE RECORDS</a></p></div>
			<div id="finishedShowData" class="<?php echo $finishedShowData; ?>"><p><a href="#">NO MORE RECORDS</a></p></div>
		</div>
        
        <input type="hidden" name="prodListLoadStart" value="<?php echo $nextstart; ?>" />
        <input type="hidden" name="prodListLoadWord" value="<?php echo $word; ?>" />
        <input type="hidden" name="prodListType" value="<?php echo $type; ?>" />
        <input type="hidden" name="prodListLoadUrl" value="<?php echo base_url('billing/searchprod'); ?>" />
    </td>
</tr>