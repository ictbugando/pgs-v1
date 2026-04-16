
<?php
$billArray  = get_instance()->Engine->getFullBillDetailsByBillId( $bill_id );
$billItems  = get_instance()->Engine->getAllBillItems($bill_id);
$itemCount  = 1;


$user_id	= get_instance()->userInfo->id;
$outData	= json_encode($billArray)." :: ".json_encode($billItems);
$inpData	= json_encode($_POST);

$dataLogArr	= array("user_id" => $user_id, "item_id" => $bill_id, "input_data" => $inpData,
                    "output_data" => $outData , "au_type" => "15" );

get_instance()->Engine->saveAuditActivityLogs($dataLogArr);

?>
<?php if( !isset($billArray->pat_id)): ?>
    <p>Bill Does Not Exist Or Has Expired</p>
<?php else: ?>
<?php
    $btnId  = $billArray->pat_id."_".$billArray->bill_id;
?>

<div class="">
    <div class="d-flex align-items-center justify-content-center" style="width:100%;"><img style="width:105px; height:90px;" src="<?php echo base_url('images/bugando.png'); ?>"></div>
    <div class="d-flex align-items-center justify-content-center" style="width:100%;"><h3>Bugando Medical Center - Patient Bill</h3></div>
</div>
<hr />

<table class="table">
    <thead>
        <tr>
            <td class="w-75">
                <table class="table table-bordered">
                    <tr><th colspan="2">Bill Details</th></tr>
                    <tr><th>Bill To:</th><td><?php echo strtoupper($billArray->sur_name ." ".$billArray->other_names); ?></td></tr>
                    <tr><th>File Number:</th><td><?php echo $billArray->pat_num; ?></td></tr>
                    <tr><th style="width:150px;">Control Number:</th><td><?php echo $billArray->bill_num; ?></td></tr>
                    <tr><th>Created:</th><td><?php echo $billArray->bill_siku; ?></td></tr>
                    <!-- <tr><th>Expiry:</th><td><?php echo $billArray->bill_siku; ?></td></tr> -->
                    <tr><th>Terms:</th><td><?php echo $billArray->term_alias; ?></td></tr>
                    <tr>
                        <th>Payment Status:</th>
                        <td style="font-size:11px;" >
                        <?php if( $billArray->is_closed == "1" ): ?>
                            <p class=""><span class="px-3 py-1 text-light bg-success rounded-pill align-middle">PAID</span></p>
                        <?php elseif( $billArray->is_closed == "2" ): ?>
                            <p class=""><span class="px-3 py-1 text-dark bg-warning rounded-pill align-middle">PARTIAL</span></p>
                        <?php else: ?>
                            <span class="px-3 py-1 text-light bg-danger rounded-pill align-middle">PENDING</span>
                        <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <th>EHMS Status:</th>
                        <td>
                            <?php
                                $synStatus  = $billArray->sync_status;
                                $synMsg     = $billArray->status_msg;
                                $synStatus  = ($synStatus == "1") ? "Synched" : (($synStatus == "2") ? "Unable To Synch" : "Not Synched");
                                echo $synStatus." - ".$synMsg;
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <div>
                    <ul>
                        <li></li>
                    </ul>
                </div>
            </td>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan="2">
                <table class="table table-bordered w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th><th>Product</th><th>Qty</th><th>Price</th><th>Tax</th><th>Disc</th><th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($billItems AS $itemArr): ?>
                        <tr>
                            <td><?php echo $itemCount++ ; ?></td>
                            <td style="width:200px;"><?php echo $itemArr->prod_name ; ?></td>
                            <td><?php echo number_format($itemArr->item_qty); ?></td>
                            <td><?php echo number_format($itemArr->item_cost); ?></td>
                            <td><?php echo $itemArr->item_tax ; ?></td>
                            <td><?php echo number_format($itemArr->item_discount) ; ?></td>
                            <td>Tsh. <?php echo number_format($itemArr->total_cost) ; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="5">&nbsp;</td>
                            <th>Total</th>
                            <th>Tsh. <?php echo number_format($billArray->bill_total_cost); ?></th>
                        </tr>
                    </tfoot>                    
                </table>
            </td>
        </tr>
    </tbody>
</table>
<center id="bot">
    <div>
        <p>Powered By Lipa Switch <a href="">https://lipaswitch.co.tz</a></p>
    </div>
</center>
<div id="" style="display:none;">
    <div id="divPrintBill">

    <style>
    @media print {
        .page-break { display: block; page-break-before: always; }
    }
    #invoice-POS {
    box-shadow: 0 0 1in -0.25in rgba(0, 0, 0, 0.5);
    padding: 2mm;
    margin: 0 auto;
    width: 150mm;
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
    #invoice-POS p {
    color: #666;
    font-size:1.2em;
    }
    #invoice-POS h2 {
        font-size:1.3em;
    }
    #invoice-POS #top, #invoice-POS #mid, #invoice-POS #bot {
    /* Targets all id with 'col-' */
    border-bottom: 1px solid #EEE;
    }

    #invoice-POS top h2 {
    font-size:1.7em;
    }

    #invoice-POS table {
    width: 100%;
    font-size:1.1em;
    border-collapse: collapse;
    }

    #invoice-POS table th , #invoice-POS table td {
    text-align: left;
    }

    #invoice-POS #legalcopy {
    margin-top: 5mm;
    }

    </style>

    <div id="invoice-POS">
        <center id="top">
            <div class="logo">
                <img style="width:60px; height:60px;" src="<?php echo base_url('images/bugando.png'); ?>">
            </div>
            <div class="info"> 
                <h2>BUGANDO MEDICAL CENTRE<br />PATIENT BILL</h2>
                <h2> 
                    Address : P.O BOX 1370, Mwanza Tanzania<br />
                    Email   : info@bmc.go.tz<br />
                    Website: http://www.bmc.go.tz<br />
                    Phone   : +255 028 2500513<br />
                </h2>
            </div><!--End Info-->
        </center><!--End InvoiceTop-->
        <hr />
        <div>
            <table width="100%">
                <tr><th>BILL TO:</th><td><?php echo strtoupper($billArray->sur_name." ".$billArray->other_names); ?></td></tr>
                <tr><th>NUMBER:</th><td><?php echo $billArray->bill_num; ?></td></tr>
                <tr><th>CREATED:</th><td><?php echo date_convert($billArray->bill_creation); ?></td></tr>
            </table>
        </div>
        <hr />
        <div id="bot">
            <div>
                <table width="100%">
                    <tr class="tabletitle">
                        <th>ITEM</th>
                        <th>QTY</th>
                        <th>SUB TOTAL</th>
                    </tr>

                    <?php foreach($billItems AS $itemArr): ?>
                    <tr class="service">
                        <td><p><?php echo $itemArr->prod_name ; ?></p></td>
                        <td><p><?php echo $itemArr->item_qty ; ?></p></td>
                        <td><p>Tsh. <?php echo number_format($itemArr->total_cost) ; ?></p></td>
                    </tr>
                    <?php endforeach; ?>

                    <tr>
                        <td></td>
                        <td><h2>Tax</h2></td>
                        <td><h2>0</h2></td>
                    </tr>

                    <tr>
                        <td></td>
                        <td><h2>TOTAL</h2></td>
                        <td><h2>TSH. <?php echo number_format($billArray->bill_total_cost); ?></h2></td>
                    </tr>

                </table>
            </div><!--End Table-->
            <HR />
            <center>
                <div id="legalcopy">
                    <p class="legal"><strong>Make payment as per this invoice!</strong>  
                    <br />Payment for this service should be made in advance. 
                    </p>
                </div>
            </center>

            <center id="bot">
                <div>
                    <p>Powered By Lipa Switch <a href="">https://lipaswitch.co.tz</a></p>
                </div>
            </center>

        </div><!--End InvoiceBot-->
    </div><!--End Invoice-->

    </div>
</div>

<?php endif; ?>