<div class="row">
    <nav class="col-4" >
        <div class="nav nav-tabs" id="nav-tab" role="tablist" >
            <div style="margin-top:10px;"><h4><b>PATIENT RECORD | <span>&nbsp;</span></b></h4></div>
            <div class="txn-page-reload" ><span><i class="fa-solid fa-arrows-rotate"></i></span></div>
        </div>

    </nav>
	<div class="col-8" >
		<div>
			<ul class="myfilter-tabs clearfix">
				<li class="myfilter-srch" style="width:350px;">
					<div class="input-group"> 
						<input type="text" name="patSearchWord" id="patSearchWord" class="form-control" placeholder="Search Patient Name or Number">
						<button class="btn btn-outline-secondary patSearch" type="button" id="patWord">
						<i class="fas fa-solid fa-magnifying-glass"></i>
						</button>
					</div>
				</li>
				<li style="width:250px;">
					<button type="button" class="btn btn-primary newPatientPop" >
						<b><i class="fa-solid fa-plus"></i> Add New Patient</b>
					</button>
				</li>
			</ul>
		</div>
	</div>
</div>
<br />

<table class="table table-striped table-hover">
	<thead class="table-dark">
		<tr>
			<th>#</th>
			<th>Number</th>
			<th>Name</th>
			<th>Phone</th>
			<th>Born</th>
			<th>Bills Unpaid</th>
			<th>Wallet Balance</th>
			<th colspan="2">Action</th>
		</tr>
	</thead>
	<tbody id="dispLoadPatDetails"><?php echo $dispPatList; ?></tbody>
</table>



<!-- Modal -->
<div class="modal fade" id="newPatientPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newPatientPopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
                <div class="clearfix" ><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
				<div id="popNewPatientModal">&nbsp;</div>
			</div>
		</div>
	</div>
</div>

<input type="hidden" name="genNewPatFormUrl" id="genNewPatFormUrl" value="<?php echo base_url('patients/fetchpatform'); ?>" />