<?php if( !isset($itemData) ): ?>
	<center>
		<h3>TRANSACTION DETAILS NOT FOUND</h3>
	</center>
<?php else: ?>

<?php
//var_dump($itemData);
$trans_date	= $itemData->trans_date;
$wallet		= $itemData->wallet_name;
$st_alias	= $itemData->st_alias;
$receipt	= $itemData->receipt;
$reference	= $itemData->reference;
$amount		= $itemData->amount;
$msisdn		= $itemData->msisdn;
$patName	= $itemData->sur_name . "" . $itemData->other_names;

$trans_date	= date("d-F-Y H:i:s", $trans_date);

?>

<style>
    @media print {
        .page-break { display: block; page-break-before: always; }
    }

	body{
		overflow:auto;
	}

    #invoice-POS {
    box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
    padding: 2mm;
    margin: 0 auto;
	padding-bottom:30px;
    width: 100%;
    background: #FFF;
    }
    #invoice-POS ::selection {
    background: #f31544;
    color: #FFF;
    }
    #invoice-POS ::moz-selection {
    background: #f31544;
    color: #FFF;
    }
    #invoice-POS h1 {
    font-size: 1.5em;
    color: #222;
    }
    #invoice-POS h2 {
    font-size: 1.1em;
    }
    #invoice-POS h3 {
    font-size: 1.2em;
    font-weight: 300;
    line-height: 2em;
    }
    #invoice-POS p {
    font-size: 1.0em;
    color: #666;
    line-height: 1.3em;
    }
    #invoice-POS #top, #invoice-POS #mid, #invoice-POS #bot {
    /* Targets all id with 'col-' */
    border-bottom: 1px solid #EEE;
    }
    #invoice-POS #top {
    min-height: 100px;
    }
    #invoice-POS #mid {
    min-height: 80px;
    }
    #invoice-POS #bot {
    min-height: 50px;
    }
    #invoice-POS .info {
    display: block;
    margin-left: 0;
    }
    #invoice-POS table {
    width: 100%;
	margin-top:20px;
	margin-bottom:20px;
    border-collapse: collapse;
    }
    #invoice-POS .tabletitle {
	padding:7px;
    }
    #invoice-POS #legalcopy {
    margin-top: 5mm;
    }
</style>

<div id="divPrintReceipt" >
	<div id="invoice-POS">
        <center id="top">
            <div class="logo">
                <img width="90px" height="90px" style="width:80px; height:80px;" src="<?php echo base_url('images/bugando.png'); ?>">
            </div>
            <div class="info"> 
                <h2>Bugando Medical Centre <br />Payment Receipt</h2>
            </div><!--End Info-->
        </center><!--End InvoiceTop-->

        <center id="mid">
        <div class="info">
            <br />
            <p> 
                Address : P.O BOX 1370, Mwanza Tanzania<br />
                Email   : info@bmc.go.tz<br />
                Website: http://www.bmc.go.tz<br />
                Phone   : +255 028 2500513<br />
            </p>
        </div>
        </center><!--End Invoice Mid-->

        <center id="bot">
            <div id="table">
                <table>
					<tbody class="tabletitle">
						<tr>
							<td><h2><b>Date:</b></h2></td>
							<td><h2><?php echo $trans_date; ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>File Number:</b></h2></td>
							<td><h2><?php echo $itemData->pat_num; ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>Name:</b></h2></td>
							<td><h2><?php echo strtoupper($itemData->other_names." ".$itemData->sur_name); ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>Control Number:</b><span>&nbsp;&nbsp;</span></h2></td>
							<td><h2><?php echo $reference; ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>Payment Source:</b></h2></td>
							<td><h2><?php echo $wallet; ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>Receipt Number:</b><span>&nbsp;&nbsp;</span></h2></td>
							<td><h2><?php echo $receipt; ?></h2></td>
						</tr>

						<tr>
							<td><h2><b>Amount Paid:</b></h2></td>
							<td><h2>Tsh. <?php echo number_format($amount); ?></h2></td>
						</tr>
					</tbody>
                </table>
            </div><!--End Table-->

			<div id="legalcopy">
				<p class="legal"><strong>This is a payment acknowledgement receipt!</strong>  
				<br />A service payment receipt will be provided once a bill has been generated. 
				</p>
			</div>

			<div>
				<?php echo '<img src="data:image/svg+xml;base64,', base64_encode($qrSimple), '" />'; ?>
			</div>
			<br />
			<p><b>Scan QR Code to Verify</b><p>
			
        </center><!--End InvoiceBot-->
        <center id="bot">
            <div>
                <p>Powered By Lipa Switch <a href="">https://lipaswitch.co.tz</a></p>
            </div>
        </center>
	</div><!--End Invoice-->
</div>


<?php endif; ?>