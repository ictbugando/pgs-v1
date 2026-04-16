<!-- Modal -->
<div class="modal fade" id="prevBillPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="prevBillPopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
                <div class="clearfix" ><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
				<div class="row">
					<div class="col-6 float-start">
						<ul>
							<li><button id="PrintBill" class="btn btn-secondary float-start doBillPrint"><span><i class="fa-solid fa-print"></i></span> Print</button></li>
						</ul>
					</div>
					<div class="col-6 float-end">
						&nbsp;
					</div>
				</div>
				<hr />
				<div id="popPrevBillModal">&nbsp;</div>
			</div>
		</div>
	</div>
</div>

<input type="hidden" name="getBillPrevUrl" id="getBillPrevUrl" value="<?php echo base_url('billing/fetchbillprev'); ?>" />
<input type="hidden" name="doBillVerifyUrl" id="doBillVerifyUrl" value="<?php echo base_url('billing/processpayment'); ?>" />



