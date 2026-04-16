
<ul class="myfilter-tabs clearfix">
    <li> 
        <div class="form-group"> 
            <select class="form-select " name="refundReportDay" id="refundReportDay"> 
                <option value="99">Day - All</option>
                <?php for($i=1;$i<=31;$i++): ?>
                    <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                <?php endfor; ?>
            </select>
        </div>
    </li>
    <li class=""> 
        <div class="form-group">
            <select class="form-select " name="refundReportMwezi" id="refundReportMwezi">
                <option value ="<?php echo $curr_month; ?>"><?php echo $months[$curr_month]; ?></option>
                <?php foreach($months AS $key => $value): ?>
                    <option value ="<?php echo $key; ?>"><?php echo $value; ?></option>';
                <?php endforeach; ?>
            </select>
        </div>
    </li>
    <li>
        <div class="form-group">
            <select class="form-select " name="refundReportMwaka" id="refundReportMwaka">
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
    <li><button type="button" id="99" class="btn btn-primary newUserPop" ><b>EXPORT</b></button></li>
</ul>
<hr />