<?php
    //var_dump($usersData);
    
    $userTypes    = get_instance()->Engine->fetchUserTypes();
    //die();
?>
<ul class="nav nav-tabs" role="tablist">
    <li ><h4><b>SYSTEM SETTINGS | &nbsp;</b></h4></li>
    <li class="nav-item" role="presentation">
        <a class="nav-link active" id="simple-tab-0" data-bs-toggle="tab" href="#simple-tabpanel-0" role="tab" aria-controls="simple-tabpanel-0" aria-selected="true">SYSTEM USERS</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="simple-tab-1" data-bs-toggle="tab" href="#simple-tabpanel-1" role="tab" aria-controls="simple-tabpanel-1" aria-selected="false">User Groups & Roles</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="simple-tab-2" data-bs-toggle="tab" href="#simple-tabpanel-2" role="tab" aria-controls="simple-tabpanel-2" aria-selected="false">System Logs</a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link" id="simple-tab-2" data-bs-toggle="tab" href="#simple-tabpanel-3" role="tab" aria-controls="simple-tabpanel-3" aria-selected="false">Backups</a>
    </li>
</ul>

<div class="tab-content pt-2" id="tab-content">
    <div class="tab-pane active" id="simple-tabpanel-0" role="tabpanel" aria-labelledby="simple-tab-0">
        <ul class="list-group list-group-horizontal ">
            <li class="list-group-item border-0"><b><h4>USERS</h4></b></li>
            <li class="list-group-item border-0">
                <div class="input-group"> 
                    <select class="form-select " name="sysUserTypes" id="sysUserTypes" > 
                        <option value="99">User Type - All</option>
                        <?php foreach($userTypes AS $userType): ?>
                            <option value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                        <?php endforeach; ?>
                    </select> 
                </div> 
            </li>
            <li class="list-group-item border-0"> 
                <div class="input-group" STYLE="width:350px;"> 
                    <input type="text" name="searchUserWord" id="searchUserWord" class="form-control" placeholder="Search User">
                    <button class="btn btn-outline-secondary bmcUserSearch" type="button" id="bmcUserSearch">
                    <i class="fas fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </li> 
            <li class="list-group-item border-0">
                <button type="button" id="99" class="btn btn-primary newUserPop" ><b>ADD NEW USER</b></button>
            </li>
        </ul>
        <hr />
        <table class="table"> 
            <thead class="table-dark"> 
                <tr>
                    <th>#</th>
                    <th>NAME</th>
                    <th>USERNAME</th>
                    <th>EMAIL</th>
                    <th>USER TYPE</th>
                    <th class="hideMobile" >REGISTERED</th>
                    <th class="hideMobile">LAST LOGIN</th>
                    <th>EDIT</th>
                </tr>
            </thead>
            <tbody id="dispBmcUsersList">
                <?php require_once("dispSearchUser.php"); ?>
            </tbody>
        </table>
    </div>
    <div class="tab-pane" id="simple-tabpanel-1" role="tabpanel" aria-labelledby="simple-tab-1">
        <?php require_once("Administrator/admin_wrap_users_groups.php"); ?>
    </div>
    <div class="tab-pane" id="simple-tabpanel-2" role="tabpanel" aria-labelledby="simple-tab-2">
        <?php require_once("Administrator/admin_wrap_audit_logs.php"); ?>
    </div>
    <div class="tab-pane" id="simple-tabpanel-3" role="tabpanel" aria-labelledby="simple-tab-3">
        <?php
            $countBacks  = 1;
        ?>
        <h3>Database Backups</h3>
        <hr />
        <div style="width:50%;">
            <table class="table"> 
                <thead class="table-dark"> 
                    <tr>
                        <th>#</th>
                        <th>Date</th>
                        <th>Backup Name</th>
                        <th>Download</th>
                    </tr>
                </thead>
                <tbody id="backupTbody">
                    <?php foreach($arrFiles AS $files): ?>
                        <tr>
                            <td><?php echo $countBacks++; ?>.</td>
                            <td></td>
                            <td><?php echo $files; ?></a></td>
                            <td>
                                <a href="<?php echo base_url("/downloads/database/".$files); ?>">
                                    <div class="" id="<?php echo $files; ?>">
                                        <span><i class="fas fa-download"></i></span>
                                    </div>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>


<input type="hidden" value="<?php echo base_url('dashboard/fetchnewuserform'); ?>" name="newUserFormPopUrl" />
<input type="hidden" value="<?php echo base_url('dashboard/fetchpopuser'); ?>" name="getUserPopUrl" />
<input type="hidden" value="<?php echo base_url('dashboard/srchuserword'); ?>" name="srchUserTypes" />
<input type="hidden" value="<?php echo base_url('dashboard/filterusertypes'); ?>" name="userListLoadUrl" />

<!-- Modal -->
<div class="modal fade" id="newUserPop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="newUserPopLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-body">
				<div class="clearfix" >
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div id="popUserModal">&nbsp;</div>
			</div>
		</div>
	</div>
</div>