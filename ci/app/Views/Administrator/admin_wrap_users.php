<?php
    $userTypes    = get_instance()->Engine->fetchUserTypes();
?>

<div>
    <ul class="myfilter-tabs clearfix" style="width:100%;">
        <li> 
            <div class="input-group"> 
                <select class="form-select " name="sysUserTypes" id="sysUserTypes" > 
                    <option value="99">User Type - All</option>
                    <?php foreach($userTypes AS $userType): ?>
                        <option value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                    <?php endforeach; ?>
                </select> 
            </div> 
        </li>
        <li> 
            <div class="input-group" > 
                <input type="text" name="sysUserWord" id="sysUserWord" class="form-control" placeholder="Search User">
                <button class="btn btn-outline-secondary" type="button" id="sysUserSearch">
                <i class="fas fa-solid fa-magnifying-glass"></i>
                </button>
            </div>
        </li>
        <li style="float:right;position:relative; top:10px;">
            <button type="button" id="99" class="btn btn-primary newUserPop" ><b>ADD NEW USER</b></button>
        </li>        
    </ul>    
    
</div>
<hr />

<table class="table"> 
    <thead class="table-dark"> 
        <tr>
            <th>#</th>
            <th>NAME</th>
            <th>USERNAME</th>
            <th>EMAIL</th>
            <th>USER GROUP</th>
            <th class="hideMobile" >REGISTERED</th>
            <th class="hideMobile">LAST LOGIN</th>
            <th></th>
        </tr>
    </thead>
    <tbody id="payTbody">
        <?php require("dispSearchUser.php"); ?>
    </tbody>
</table>

<input type="hidden" value="<?php echo base_url('dashboard/fetchnewuserform'); ?>" name="newUserFormPopUrl" />
<input type="hidden" value="<?php echo base_url('dashboard/fetchpopuser'); ?>" name="getUserPopUrl" />
<input type="hidden" value="<?php echo base_url('dashboard/srchuserword'); ?>" name="srchUserTypes" />
<input type="hidden" value="<?php echo base_url('dashboard/filterusertypes'); ?>" name="filterUserTypes" />

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