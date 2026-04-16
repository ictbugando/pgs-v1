<?php
$patArray		= get_instance()->Engine->fetchPatientList($start =0);
?>

<div id="billTabsHead" >
	<ul class="nav nav-tabs mb-3" role="tablist">
		<li style="width:150px;" class="align-middle"><h4>BILLING</h4></li>
		<li class="nav-item text-center" role="presentation">
			<a class="nav-link active" id="tab1" data-bs-toggle="tab" href="#tabs1" role="tab" >History</a>
		</li>
		<li class="nav-item text-center" role="presentation">
			<a class="nav-link " id="tab2" data-bs-toggle="tab" href="#tabs2" role="tab" >Products</a>
		</li>
	</ul>
</div>

<div class="tab-content" id="ex1-content">
	<div class="tab-pane fade show active" id="tabs1" role="tabpanel"  aria-labelledby="tab1">
        <?php require_once("bill_history.php"); ?>
	</div>

	<div class="tab-pane fade " id="tabs2" role="tabpanel" aria-labelledby="tab2">
		<?php require_once("bill_products.php"); ?>
	</div>
	
	<div class="tab-pane fade" id="tabs3" role="tabpanel" aria-labelledby="tab3">
		Tab 3 content
	</div>
</div>