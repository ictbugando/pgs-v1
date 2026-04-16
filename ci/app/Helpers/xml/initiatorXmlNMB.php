<?php
$txType     = "cnum";
$partial    = "FALSE";
$codeDesc   = "Invalid Customer Reference/Invoice Number";
$newBillArr = array("code" => "3", "codeDesc" => $codeDesc, "reference" => "", "receipt" => "" , "patName" => "", 
                    "payerID" => "", "amount" => "", "token" => "" );

if( sizeof($dataValues) > 0){
    if($flag == "1"){
        $codeDesc   = "Successfull";
        $patName    = $dataValues["sur_name"]." ".$dataValues["other_names"];
        $txType     = $dataValues["typ"];

        if($txType != "cnum")
            $partial = "TRUE";
    
        $newBillArr = array("code" => "1", "codeDesc" => $codeDesc, "reference" => $dataValues["reference"],  "receipt" => "", "patName" => $patName, 
                            "payerID" => $dataValues["pat_num"], "amount" => $dataValues["bill_amount"], "token" => $dataValues["token"] );
    }
    else{
        $code       = getStatusCodeNMB($dataValues["status"]);
        
        $newBillArr = array("code" => $code, "codeDesc" => $dataValues["msg"], "reference" => $dataValues["reference"],  
                            "receipt" => "", "token" => "" );
    }

}

?>
<response>
    <code><?php echo $newBillArr["code"]; ?></code>
    <description><?php echo $newBillArr["codeDesc"]; ?></description>
    <reference><?php echo $newBillArr["reference"]; ?></reference>
    <?php if($flag == "2"): ?>
        <receipt><?php echo $newBillArr["receipt"]; ?></receipt>
    <?php else: ?>
        <institutionName>Bugando Medical Center</institutionName>
        <institutionCode></institutionCode>
        <payerName><?php echo $newBillArr["patName"]; ?></payerName>
        <payerID><?php echo $newBillArr["payerID"]; ?></payerID>
        <feetype></feetype>
        <amount><?php echo $newBillArr["amount"]; ?></amount>
        <token><?php echo $newBillArr["token"]; ?></token>
        <partial><?php echo $partial; ?></partial>
    <?php endif; ?>
</response>