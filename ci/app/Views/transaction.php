<?php
$curr_year   = $monthArr['curr_year'];
$curr_month  = $monthArr['curr_month'];
$months 	 = $monthArr['months'];
$year        = 2022;

$morePayData		= "";
$finishedPayData	= "";
$payLoadMore 		= (int)$payLoadMore;

$moreRefData		= "";
$finishedRefData	= "";

$payLoadMore == "1" ? $finishedPayData = "hideMe" : $morePayData = "hideMe";
$refLoadMore == "1" ? $moreRefData = "hideMe" : $finishedRefData = "hideMe";

?>
<nav>
  <div class="nav nav-tabs" id="nav-tab" role="tablist">
	<h2 class="">Transactions <span>&nbsp;</span></h2>
    <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">History</button>
    <!--
	<button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">Reference</button>
	
    <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">Pending</button> -->
	<div class="txn-page-reload" ><span><i class="fa-solid fa-arrows-rotate"></i></span></div>
  </div>
</nav>

<input type="hidden" value="<?php echo base_url('transactions/fetchpayments'); ?>" name="payHistoUrl" />
<input type="hidden" value="<?php echo base_url('transactions/fetchuniqueref'); ?>" name="refHistoUrl" />
<input type="hidden" value="<?php echo base_url('transactions/searchdata'); ?>" name="searchUrl" />
		
<div class="tab-content" id="nav-tabContent">
	<div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab">
		<ul class="myfilter-tabs clearfix"> 
			<li> 
				<div class="input-group"> 
					<select class="form-select " name="payHistoWallet" id="payHistoWallet" > 
						<option value="99">Wallet - All</option> 
						<?php foreach($myWallets AS $myWallet): ?>
							<option value="<?php echo $myWallet->wallet_id; ?>"><?php echo $myWallet->wallet_alias; ?></option>
						<?php endforeach; ?>
					</select>
				</div> 
			</li>
			<li> 
				<div class="input-group"> 
					<select class="form-select " name="payHistoStatus" id="payHistoStatus" > 
						<option value="99">Status - All</option>
						<?php foreach($myTxStatus AS $txStatus): ?>
							<option value="<?php echo $txStatus->st_code; ?>"><?php echo $txStatus->st_alias; ?></option>
						<?php endforeach; ?>
					</select> 
				</div> 
			</li> 			
			<li> 
				<div class="input-group"> 
					<select class="form-select " name="payHistoMwezi" id="payHistoMwezi" >
						<option value ="<?php echo $curr_month; ?>"><?php echo $months[$curr_month]; ?></option>
						<?php foreach($months AS $key => $value): ?>
							<option value ="<?php echo $key; ?>"><?php echo $value; ?></option>';
						<?php endforeach; ?>
					</select>
				</div>
			</li>
			<li>
				<div class="input-group"> 
					<select class="form-select " name="payHistoMwaka" id="payHistoMwaka" >
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
			<li class="myfilter-srch"> 
				<div class="input-group" > 
					<input type="text" name="paySearchWord" id="paySearchWord" class="form-control" placeholder="Search">
					<button class="btn btn-outline-secondary" type="button" id="payHistoSearch">
					<i class="fas fa-solid fa-magnifying-glass"></i>
					</button>
				</div>
			</li> 
		</ul>
				
		<table class="table"> 
			<thead class="table-dark"> 
				<tr>
					<th>#</th>
					<th>Wallet</th>
					<th>Patient Name</th>
					<th class="hideMobile" >Source</th>
					<th>Transaction Receipt</th>
					<th>Control #</th>
					<th>Amount</th>
					<th class="hideMobile">Date</th>
					<th>Action</th>
				</tr>
			</thead>
			<tbody id="payTbody"><?php echo $payDataView; ?></tbody>
		</table>
		
		<div id="payDataLoadMore" class="d-flex justify-content-center">
			<div id="morePayData" class="<?php echo $morePayData; ?>"><p>LOAD MORE RECORDS</p></div>
			<div id="finishedPayData" class="<?php echo $finishedPayData; ?>"><p>NO MORE RECORDS</p></div>
		</div>
				
		<input type="hidden" value="<?php echo $payStart; ?>" name="payHistoStart" id="payHistoStart"/>
		<input type="hidden" value="0" name="isSetPaySearch" id="isSetPaySearch" />
		<input type="hidden" value="0" name="paySearchString" id="paySearchString" />
	</div>

	<div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab">
		<ul class="myfilter-tabs clearfix">
			<li>
				<div class="input-group"> 
					<select class="form-select " name="refHistoStatus" id="refHistoStatus" > 
						<option value="99">Status</option>
						<?php foreach($myTxStatus AS $txStatus): ?>
							<option value="<?php echo $txStatus->st_code; ?>"><?php echo $txStatus->st_alias; ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</li>		
			<li> 
				<div class="input-group"> 
					<select class="form-select " name="refHistoMwezi" id="refHistoMwezi" >
						<option value ="<?php echo $curr_month; ?>"><?php echo $months[$curr_month]; ?></option>
						<?php foreach($months AS $key => $value): ?>
							<option value ="<?php echo $key; ?>"><?php echo $value; ?></option>';
						<?php endforeach; ?>
					</select>
				</div>
			</li>
			<li>
				<div class="input-group"> 
					<select class="form-select " name="refHistoMwaka" id="refHistoMwaka" >
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
			<li class="myfilter-srch"> 
				<div class="input-group" > 
					<input type="text" name="refSearchWord" id="refSearchWord" class="form-control" placeholder="Search">
					<button class="btn btn-outline-secondary" type="button" id="refHistoSearch">
					<i class="fas fa-solid fa-magnifying-glass"></i>
					</button>
				</div>
			</li> 
		</ul>
		
		<table class="table"> 
			<thead class="table-dark"> 
				<tr>
					<th>#</th>
					<th>Patient Number</th>
					<th>reference</th>
					<th>amount</th>
					<th class="hideMobile">Phone</th>
					<th class="hideMobile">Generated</th>
					<th >Processed</th>
					<th>Status</th>
					<th>&nbsp;</th>
				</tr>
			</thead>
			<tbody id="refTbody" ><?php echo $refDataView; ?></tbody>
		</table>
		
		<div id="payDataLoadMore" class="d-flex justify-content-center">
			<?php echo $payLoadMore; ?>
			<div id="moreRefData" class="<?php echo $moreRefData; ?>"><p>LOAD MORE RECORDS</p></div>
			<div id="finishedRefData" class="<?php echo $finishedRefData; ?>"><p>NO MORE RECORDS</p></div>
		</div>
		
		<input type="hidden" value="<?php echo $refStart; ?>" name="refHistoStart" id="refHistoStart"/>
		<input type="hidden" value="0" name="isSetRefSearch" id="isSetRefSearch" />
		<input type="hidden" value="0" name="refSearchString" id="refSearchString" />		
		
	</div>
	
</div>
