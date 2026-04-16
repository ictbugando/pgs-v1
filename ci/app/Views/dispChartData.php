<?php
    $curr_year   = $monthArr['curr_year'];
    $curr_month  = $monthArr['curr_month'];
    $months 	 = $monthArr['months'];
    $year        = 2022;
?>

<div class="myChartContainer">
    <h3>Weekly Collection</h3><hr />
    <canvas id="myChartweekly" class="myChartCanvas"></canvas> 
</div>

<div class="myChartContainer">
    <h3>Monthly Collection</h3>
    <ul class="myfilter-tabs clearfix"> 		
        <li> 
            <div class="input-group"> 
                <select class="form-select " name="chartMonthlyMwezi" id="chartMonthlyMwezi" >
                    <option value ="<?php echo $curr_month; ?>"><?php echo $months[$curr_month]; ?></option>
                    <?php foreach($months AS $key => $value): ?>
                        <option value ="<?php echo $key; ?>"><?php echo $value; ?></option>';
                    <?php endforeach; ?>
                </select>
            </div>
        </li>
        <li>
            <div class="input-group"> 
                <select class="form-select " name="chartMonthlyMwaka" id="chartMonthlyMwaka" >
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
    <hr />
    <canvas id="myChartsmonthly" class="myChartCanvas"></canvas>
</div>

<div class="myChartContainer">
    <h3>Annual Collection</h3>
    <ul class="myfilter-tabs clearfix">
        <li>
            <div class="input-group"> 
                <select class="form-select " name="chartAnnualMwaka" id="chartAnnualMwaka" >
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
    <hr />
    <canvas id="myChartsannual" class="myChartCanvas"></canvas>
</div>