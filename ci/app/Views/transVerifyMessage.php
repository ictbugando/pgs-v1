<?php if( isset($itemData->trans_id) ): ?>
<div class="alert alert-success" role="alert">
<h4 class="alert-heading"><b>THIS IS A VALID RECEIPT ISSUED BY BUGANDO MEDICAL CENTRE</b>!</h4>
</div>
<?php else: ?>
    <div class="alert alert-danger" role="alert">
        <h4 class="alert-heading"><b>WARNING - UNRECORGIZED DETAILS</b>!</h4>
        <p>We were unable to verify the receipt transaction details provided. This could mean the recipt is not a genuine receipt or its information is no longer valid.</p>
        <hr>
        <p class="mb-0">Use of forged receipts is a criminal offense. For further clarification kindly visit Bugando Medical Centre for assistance.</p>
    </div>
<?php endif; ?>