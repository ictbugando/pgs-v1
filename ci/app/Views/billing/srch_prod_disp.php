<?php
//var_dump($srchArr);
$count = 1;
?>

<table class="table table-striped table-hover">
    <thead>
        <th>#</th>
        <th>Product</th>
        <th>Cost</th>
        <th>Description</th>
    </thead>
    <tbody>
        <?php if( sizeof($srchArr) == 0): ?>
            <tr><td colspan="4" class="text-center">Product Not Found</td></tr>
        <?php else: ?>
            <?php foreach($srchArr AS $srchAr): ?>
                <tr class="prodselect" id="<?php echo $srchAr->prod_id; ?>" data-side="prod_<?php echo $srchAr->prod_id; ?>" data-params="<?php echo htmlspecialchars(json_encode($srchAr), ENT_QUOTES, 'UTF-8'); ?>">
                    <td><?php echo $count++; ?></td>
                    <td><?php echo $srchAr->prod_name; ?></td>
                    <td><?php echo number_format($srchAr->cost_amt); ?></td>
                    <td><?php echo $srchAr->prod_desc; ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>