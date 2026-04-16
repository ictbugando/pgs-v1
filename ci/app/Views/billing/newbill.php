<?php
//var_dump($patRecArray);
$payTermsArr    = get_instance()->Engine->getPayTerms();
?>
<form id="newBillForm" action="<?php echo base_url('billing/postnewbill'); ?>" method="post">

<div class="row align-items-start">
    <div class="col"><h3>New Bill</h3></div>
    <div class="col">&nbsp;</div>
    <div class="col"><button type="button" class="btn btn-success subnewbill">SUBMIT NEW BILL</button></div>
</div>
<div class="row"> <div class="col"><div id="ajaxAlertMsg">&nbsp;</div></div> </div>
<hr />

<div id="newBillContainer" hlass="row align-items-start">
    <div class="col">
        <div class="row align-items-start">
            <div class="col-4">
                <table class="table table-borderless">
                    <tr><th colspan="2">Patient Details</th></tr>
                    <tr>
                        <th>Name</th>
                        <td>
                            <div class="input-group my-noborder">
                                <input type="text" class="form-control" readonly value="<?php echo $patRecArray->other_names." ".$patRecArray->sur_name; ?>" >
                            </div>
                        </td>
                    <tr>
                    <tr>
                        <th>Number</th>
                        <td>
                            <div class="input-group my-noborder">
                                <input type="text" class="form-control" readonly value="<?php echo $patRecArray->pat_num; ?>" >
                            </div>
                        </td>
                    <tr>
                </table>
            </div>
            <div class="col-1">&nbsp;</div>
            <div class="col-6">
                <table class="table table-borderless">
                    <tr>
                        <th>Expiration</th>
                        <td>
                            <div class="input-group">
                                <input type="text" placeholder="Expire Date" name="billExpiryDate" class="datepicker_input form-control" />
                            </div>
                        </td>
                    <tr>
                    <tr>
                        <th>Bill Date*</th>
                        <td>
                            <div class="input-group ">
                                <input type="text" placeholder="Billing Date" value="<?php echo date("Y/m/d"); ?>" name="billCreateDate" class="datepicker_input form-control" required />
                            </div>
                        </td>                        
                    <tr>
                    <tr>
                        <th>Payment Terms*</th>
                        <td>
                            <div class=" form-group">
                                <select class="form-select " name="pay-terms" id="pay-terms" > 
                                    <option value="0">Not Set</option>
                                    <?php foreach( $payTermsArr AS $payTermsAr): ?>
                                        <option value="<?php echo $payTermsAr->terms_id; ?>"><?php echo $payTermsAr->term_alias; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </td>                        
                    <tr>
                </table>
            </div>
        </div>
        <hr />

        <table class="table table-dashed table-striped table-fixed">
            <thead>
                <tr>
                    <th style="width:5%;" class="w-5">#</th>
                    <th style="width:15%;" class="text-start">Product</th>
                    <th style="width:20%;" class="text-start">Description</th>
                    <th style="width:7.5%;" class="text-start">Quantity</th>
                    <th style="width:15%;" class="text-start">Price</th>
                    <th style="width:10%;" class="text-start">Tax</th>
                    <th style="width:10%;" class="text-start">Discount %</th>
                    <th style="width:12.5%;" class="text-start">Total</th>
                    <th style="width:5%;" class="text-start">Action</th>
                <tr>
            </thead>
            <tbody id="newbillitems">

            </tbody>
            <tfoot>
                <tr>
                    <th colspan="7" rowspan="3"><a href="#" data-bs-toggle="modal" data-bs-target="#getProductPop">Add a product</a></th>
                    <th colspan="2">&nbsp;<th>
                </tr>
                <tr><th colspan="2">Tax <span class="billTaxTotal">Tsh. 0<span><th></tr>
                <tr><th colspan="2">Total <span class="billGrandTotal">Tsh. 0<span><th></tr>
            </tfoot>
        </table>
    </div>
</div>
<input type="hidden" name="pat_id" value="<?php echo $patRecArray->pat_id; ?>" />
<input type="hidden" id="bill_details" name="bill_details" value="" />
</form>

<!-- Modal -->
<div class="modal fade" id="getProductPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="getProductPopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
                <div class="clearfix" ><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
				<div style="min-height:300px;">
                    <h3>Search Product</h3>
                    <hr />
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="prod-sword" placeholder="Search Product" >
                        <div class="input-group-append subprod-srch"><button class="btn btn-outline-secondary" type="button">Search</button></div>
                    </div>
                    <br />
                    <div id="popGetProdForm"></div>

                </div>
			</div>
		</div>
	</div>
</div>

<input type="hidden" name="billTempId" id="billTempId" value="bill_<?php echo strtotime(date("Y-m-d H:i:s")); ?>" />
<input type="hidden" name="searchProdUrl" id="searchProdUrl" value="<?php echo base_url('billing/searchword'); ?>" />

