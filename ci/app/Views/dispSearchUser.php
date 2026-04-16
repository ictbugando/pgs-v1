<?php
    $moreShowData		= "";
    $finishedShowData	= "";
    
    $startArr       = $usersData["start"];
    $count          = ($startArr["prevstart"] + 1);
    $word           = $startArr["word"];
    $type           = $startArr["type"];
    $showLoadMore   = $startArr["load"];
    $nextstart      = $startArr["nextstart"];
    
    $showLoadMore == "0" ? $moreShowData = "hideMe" : $finishedShowData = "hideMe";
    
    unset($usersData["start"]);
?>
<?php foreach($usersData as $userArray): ?>
    <tr>
        <td><?php echo $count++ ?>.</td>
        <td><?php echo strtoupper($userArray->name); ?></td>
        <td><?php echo $userArray->username; ?></td>
        <td><?php echo $userArray->email; ?></td>
        <td class="hideMobile"><?php echo $userArray->title; ?></td>
        <td class="hideMobile"><?php echo $userArray->registerDate; ?></td>
        <td class="hideMobile"><?php echo $userArray->lastvisitDate; ?></td>
        <td>
            <a href="<?php echo base_url('dashboard/settings/'.$userArray->id); ?>">
            <span><i class="fas fa-cog"></i></span>
            </a>
        </td>
    </tr>
<?php endforeach; ?>
<tr id="userListExtra">
    <td colspan="9">
		<div id="payDataLoadMore" class="d-flex justify-content-center">
			<div id="moreShowData" class="userLoadMore <?php echo $moreShowData; ?>"><p><a href="#">LOAD MORE RECORDS</a></p></div>
			<div id="finishedShowData" class="<?php echo $finishedShowData; ?>"><p><a href="#">NO MORE RECORDS</a></p></div>
		</div>
        
        <input type="hidden" name="userListLoadStart" value="<?php echo $nextstart; ?>" />
        <input type="hidden" name="userListLoadWord" value="<?php echo $word; ?>" />
        <input type="hidden" name="userListType" value="<?php echo $type; ?>" />
        <input type="hidden" name="" value="<?php echo base_url('billing/searchprod'); ?>" />
    </td>
</tr>