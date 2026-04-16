<div class="row">
    <div class="col-5">
        <div class="input-group">
            <input type="text" name="prodSearchWord" class="form-control" placeholder="Search Products" >
            <div class="input-group-append"><button id="prodWord" class="btn btn-outline-secondary prodWord" type="button"><i class="fas fa-thin fa-magnifying-glass"></i></button></div>
        </div>
    </div>
    <div class="col-3">&nbsp;</div>
    <div class="col-4">
        <div class="text-right"><button type="button" class="btn btn-success bill-product-pop" >Create New Product</button></div>
    </div>
</div>
<hr />
<?php
    $productsArray  = get_instance()->Engine->fetchBillProducts($word = "" , $start = 0 , $type = 1);
    //$productsArray  = get_instance()->Engine->fetchBillProducts($start = 0 );
?>

<table class="table table-striped table-hover">
	<thead class="table-dark">
		<tr>
			<th>#</th><th>Product</th><th>Price</th><th>Description</th><th>Action</th>
		</tr>
	</thead>
    <tbody id="dispProdPatDetails">
        <?php require_once("disp_prod_list.php"); ?>
    </tbody>
</table>

<!-- Modal -->
<div class="modal fade" id="newProductPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newProductPopLabel" aria-hidden="true">
	<div class="modal-dialog modal-md">
		<div class="modal-content">
			<div class="modal-body">
                <div class="clearfix" ><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
				<div id="popNewProdForm">&nbsp;</div>
			</div>
		</div>
	</div>
</div>

<input type="hidden" name="genNewProdFormUrl" id="genNewProdFormUrl" value="<?php echo base_url('billing/newprodform'); ?>" />