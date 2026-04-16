<div class="row">
    <nav class="col-9" >
        <div class="nav nav-tabs" id="nav-tab" role="tablist" >
            <div style="margin-top:10px;"><h4><b>REFUNDS | <span>&nbsp;</span></b></h4></div>
            <div class="txn-page-reload" ><span><i class="fa-solid fa-arrows-rotate"></i></span></div>
        </div>

    </nav>
	<div class="col-3" >
		<div>
			<button type="button" class="btn btn-primary newRefundPop" >
                <b><i class="fa-solid fa-plus"></i> Create New Refund</b>
            </button>
		</div>
	</div>
</div>
<br />

<input type="hidden" value="<?php echo base_url('refund/getrefundform'); ?>" name="newRefundFormPopUrl" />
<input type="hidden" value="<?php echo base_url('refund/searchrefundbills'); ?>" name="getRefundBillsUrl" />

<!-- Modal -->
<div class="modal fade" id="refundBillPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editRolePopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<div class="clearfix" >
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div id="popNewRefundModal">
                    <h4>REFUND FORM</h4>

                    <div class="row align-items-start">
                        <div class="d-grid gap-2 col-8 mx-auto form-group">
                            <input type="text" name="patnumrefund" class="form-control" placeholder="Search Patient or Bill Number">
                        </div>

                        <div class="d-grid gap-2 col-4 mx-auto">
                            <button type="submit" class="btn btn-success getrefundbills">Search Record</button>
                        </div>
                    </div>
                    <hr />
                    <div id="displayRefundBillsSearch">

                    </div>
                </div>
			</div>
		</div>
	</div>
</div>