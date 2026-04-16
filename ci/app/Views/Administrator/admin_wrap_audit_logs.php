<?php
    //var_dump( $auditLogs);
    $count = 1;
?>
<table class="table">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Action</th>
            <th>User</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach($auditLogs AS $auditArr): ?>
        <tr>
            <td><?php echo $count++; ?>.</td>
            <td><?php echo $auditArr->au_type_desc; ?></td>
            <td><?php echo $auditArr->name; ?></td>
            <td><?php echo $auditArr->log_siku; ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>