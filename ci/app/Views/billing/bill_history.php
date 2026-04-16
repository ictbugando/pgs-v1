<div class="row">
    <div class="col-7"><h5>Patient Bills</h5></div>
    <div class="col-5">
        <form method="post" action="<?php echo base_url('billing/dobillsearch'); ?>" onkeydown="return event.key != 'Enter';">
            <div class="input-group">
                <input type="text" class="form-control" name="patBillSearch" placeholder="Search Bills" >
                <div class="input-group-append"><button id="billWord" class="btn btn-outline-secondary " type="button"><i class="fas fa-thin fa-magnifying-glass"></i></button></div>
            </div>
        </form>
    </div>
    
</div>
<hr />
<?php
    $billsArray	= get_instance()->Engine->getBillFetchAllBillsWithWord($start = 0, $word = "");
?>
<table class="table table-striped table-dashed">
	<thead class="table-dark">
		<tr>
			<th>#</th>
            <th>Number</th>
            <th>Patient Name</th>
            <th>Bill Total</th>
            <th>Amount Paid</th>
            <th>Created by</th>
            <th>Bill Date</th>
            <th>Status</th>
            <th>View</th>
		</tr>
    </thead>
    <tbody id="billDispBody">
        <?php require("bill_hist_disp.php"); ?> 
	</tbody>
</table>
<input type="hidden" name="billListLoadUrl" id="billListLoadUrl" value="<?php echo base_url('billing/dobillsfetch'); ?>" />

