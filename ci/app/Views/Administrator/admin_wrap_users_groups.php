<?php
    $userTypes  = get_instance()->Engine->fetchAllUserGroups();
    
    //var_dump( $systemRoles );
    $groupCount = 1;
    $rolesCount = 1;
?>

<br />
<div class="container-fluid">
    <div class="row">
        <div class="col-6">
            <h6><b>USER GROUPS</b></h6>
            <table class="table"> 
                <thead class="table-dark"> 
                    <tr>
                        <th>#</th><th>GROUPS NAME</th><th>ROLES</th><th>USERS</th><th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody id="payTbody">
                    <?php foreach($userTypes AS $groupArr): ?>
                        <tr>
                            <td><?php echo $groupCount++; ?>.</td>
                            <td><?php echo $groupArr->title; ?></td>
                            <td><?php echo $groupArr->rolesCount; ?></td>
                            <td><?php echo $groupArr->userCount; ?></td>
                            <td>
                                <?php //var_dump($groupArr); ?>
                                <a class="popGroupRole" id="<?php echo $groupArr->id; ?>" href="#">
                                    <span><i class="fas fa-cog"></i></span>
                                </a>
                            </td>
                        </tr>                        
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <h6><b>USER ROLES</b></h6>
            <table class="table"> 
                <thead class="table-dark"> 
                    <tr>
                        <th>#</th><th>ROLE NAME</th><th>ROLE TYPE</th>
                    </tr>
                </thead>
                <tbody id="payTbody">
                    <?php foreach($systemRoles AS $rolesArr): ?>
                        <?php foreach($rolesArr AS $roleAr): ?>
                            <tr>
                                <td><?php echo $rolesCount++; ?>.</td>
                                <td><?php echo $roleAr->role_name; ?></td>
                                <td><?php echo strtoupper($roleAr->group_name); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<input type="hidden" value="<?php echo base_url('dashboard/fetcheditroleform'); ?>" name="editRoleFormPopUrl" />

<!-- Modal -->
<div class="modal fade" id="editRolePop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="editRolePopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<div class="clearfix" >
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div id="popRoleEditModal">&nbsp;</div>
			</div>
		</div>
	</div>
</div>