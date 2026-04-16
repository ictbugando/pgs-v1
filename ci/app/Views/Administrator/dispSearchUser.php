<?php
    $count = 1;

    //var_dump($usersData);
?>
<?php foreach($usersData as $userArray): ?>
    <?php var_dump( $userArray ); ?>
    <tr>
        <td><?php echo $count++ ?>.</td>
        <td><?php echo strtoupper($userArray->name); ?></td>
        <td><?php echo $userArray->username; ?></td>
        <td><?php echo $userArray->email; ?></td>
        <td class="hideMobile"><?php echo $userArray->title; ?></td>
        <td class="hideMobile"><?php echo $userArray->registerDate; ?></td>
        <td class="hideMobile"><?php echo $userArray->lastvisitDate; ?></td>
        <td>
            <a href="<?php echo base_url('dashboard/settings/'.$userArray->id); ?>">
            <span><i class="fas fa-cog"></i></span>
            </a>
        </td>
    </tr>
<?php endforeach; ?>