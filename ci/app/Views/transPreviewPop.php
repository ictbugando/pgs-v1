<input type="hidden" value="<?php echo base_url('transactions/fetchpopitems'); ?>" name="getItemPopUrl" />

<!-- Modal -->
<div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-body" >
				<div class="row">
					<div class="col-6 float-start">
						<ul>
							<li><button id="PrintReceipt" class="btn btn-secondary float-start doBillPrint"><span><i class="fa-solid fa-print"></i></span> Print</button></li>
						</ul>
					</div>
					<div class="col-6 float-end">
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
				</div>
				<br />
				<div id="popItemModal">&nbsp;</div>
			</div>
		</div>
	</div>
</div>
