<?php
$prodTypes  = get_instance()->Engine->getProductTypes();
?>
<h3>New Product</h3>
<hr />
<form method="post" action="<?php echo base_url('billing/newprodprocess'); ?>">
    <table class="table table-borderless">
        <tr>
            <td>
                <div class=" form-group">
                    <label for="">Product Type</label>
                    <select class="form-select " name="prodType" > 
                        <option value="0">Not Set</option>
                        <?php foreach($prodTypes AS $prodArr): ?>
                            <option value="<?php echo $prodArr->prod_type_id; ?>"><?php echo $prodArr->type_name; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>            
        </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <label for="">Product Name</label>
                    <input type="text"  name="prodName" class="form-control" placeholder = "Product Name" />
                </div>            
        </td>
        </tr>    
        <tr>
            <td>
                <div class="form-group">
                    <label for="">Product Cost</label>
                    <input type="text"  name="prodCost" class="form-control" placeholder = "Product Cost" />
                </div>            
        </td>
        </tr>
        <tr>
            <td>
                <div class="form-group">
                    <label for="newProdtxt">Product Description</label>
                    <textarea class="form-control" name="prodDesc" id="newProdtxt" rows="3"></textarea>
                </div>            
        </td>
        </tr>
        <tr>
            <td>
                <div id="ajaxAlertMsg">&nbsp;</div>
                <div class="d-grid gap-2 col-6 mx-auto">
                    <button type="submit" class="btn btn-primary newProdsubmit">Submit</button>
                </div>
            </td>
        </tr>
    </table>
</form>