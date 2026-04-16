<?php
    $txnHtml    = "";
    $txnHtml2   = "";

    if( isset ($dataValues["txnId"]) ){
        $txnHtml    = "<TXNID>".$dataValues["txnId"]."</TXNID>";
    }
    if( isset ($dataValues["ref"]) ){
        $txnHtml2    = "<REF>".$dataValues["ref"]."</REF>";
    }

    if( isset( $dataValues["billar"]) )
        $billArray  = $dataValues["billar"];

    if( isset( $dataValues["lookArr"]) )
        $lookArray  = $dataValues["lookArr"];        
?>
<?php if( isset( $dataValues["billar"]) ): ?>
<COMMAND>
    <STATUS><?php echo $dataValues["status"]; ?></STATUS>
    <FIRSTNAME><?php echo $billArray->sur_name; ?></FIRSTNAME>
    <LASTNAME><?php echo $billArray->other_names; ?></LASTNAME>
    <DUEDATE><?php echo $billArray->bill_siku; ?></DUEDATE>
    <AMOUNT><?php echo $billArray->bill_total_cost; ?></AMOUNT>
    <CURRENCY>TZS</CURRENCY>
    <MESSAGE><?php echo $dataValues["msg"]; ?></MESSAGE>
</COMMAND>
<?php elseif( isset( $dataValues["lookArr"]) ): ?>
<COMMAND>
    <STATUS><?php echo $dataValues["status"]; ?></STATUS>
    <FIRSTNAME><?php echo $lookArray->sur_name; ?></FIRSTNAME>
    <LASTNAME><?php echo $lookArray->other_names; ?></LASTNAME>
    <MESSAGE><?php echo $dataValues["msg"]; ?></MESSAGE>
</COMMAND>    
<?php else: ?>
<COMMAND>
    <STATUS><?php echo $dataValues["status"]; ?></STATUS>
    <?php echo $txnHtml; ?>
    <MESSAGE><?php echo $dataValues["msg"]; ?></MESSAGE>
    <?php echo $txnHtml2; ?>
</COMMAND>
<?php endif; ?>