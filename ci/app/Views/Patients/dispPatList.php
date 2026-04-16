<?php
    $prevStart	= $patArray["start"]["prevstart"]++;
    $nextStart	= $patArray["start"]["nextstart"];
    $loadMore	= $patArray["start"]["loadMore"];
    $word	    = $patArray["start"]["word"];

    unset($patArray["start"]);
    $prevStart++;

    $morePatData		= "";
    $finishedPatData	= "";

    $loadMore == "1" ? $morePatData = "hideMe" : $finishedPatData = "hideMe";

?>
<?php foreach($patArray AS $patAr): ?>
    <?php
        $dob = (int)$patAr->dob > 0 ? date("Y-m-d", strtotime($patAr->dob)) : "-";
    ?>
    <tr>
        <td><?php echo $prevStart++;?></td>
        <td><?php echo $patAr->pat_num; ?></td>
        <td style="width:350px;"><?php echo strtoupper($patAr->other_names." ".$patAr->sur_name); ?></td>
        <td><?php echo $patAr->phone; ?></td>
        <td><?php echo $dob; ?></td>
        <td>Tsh. <?php echo number_format($patAr->bill_amount); ?></td>
        <td>Tsh. <?php echo number_format($patAr->wallet_bal); ?></td>
        <td>
            <a href="<?php echo base_url('patients/record/'.$patAr->pat_id); ?>"><span><i class="fas fa-arrow-up-right-from-square"></i></span></a>
        </td>
        <!--			
        <td>
            <div class="pat-edit-pop" id="pat_<?php echo $patAr->pat_id; ?>"><span><i class="fas fa-cog"></i></span></div>
        </td>
        -->
    </tr>
<?php endforeach; ?>
    <tr id="patListExtra">
        <td colspan="9">

            <div id="" class="d-flex justify-content-center" >
                <div class="<?php echo $morePatData; ?> patLoadMore"><a href="#">LOAD MORE RECORDS</a></div>
                <div class="<?php echo $finishedPatData; ?>"><a href="#">NO MORE RECORDS</a></div>
            </div>

            <input type="hidden" name="patListLoadStart" value="<?php echo $nextStart; ?>" />
            <input type="hidden" name="patListLoadWord" value="<?php echo $word; ?>" />
            <input type="hidden" name="patListLoadUrl" value="<?php echo base_url('patients/search'); ?>" />
        </td>
    </tr>