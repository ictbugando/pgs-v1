<?php
//var_dump($userData);
//die();
?>
<table class="table table-striped table-bordered">
	<thead>
		<tr>
			<th colspan="2" class="text-center ">Registered User Details</th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<th>Name</th>
			<td>
				<div class="input-group">
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->name; ?>" />
				</div>
			</td>
		</tr>	
		<tr>
			<th>Username</th>
			<td>
				<div class="input-group"> 
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->username; ?>" />
				</div>
			</td>
		</tr>
		<tr>
			<th>Email</th>
			<td>
				<div class="input-group"> 
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->email; ?>" />
				</div>			
			</td>
		</tr>
		<tr>
			<th>Registered</th>
			<td>
				<div class="input-group"> 
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->registerDate; ?>" />
				</div>			
			</td>
		</tr>
		<tr>
			<th>Last Active</th>
			<td>
				<div class="input-group"> 
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->lastvisitDate; ?>" />
				</div>			
			</td>
		</tr>
		<tr>
			<th>User Group</th>
			<td>
                <div class="input-group"> 
					<select class="form-select " name="userType" id="userType" > 
						<option value="99">Not Set</option>
						<?php foreach($userTypes AS $userType): ?>
                            <?php if($userType->id == $userData->group_id): ?>
							    <option selected value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                            <?php else: ?>
                                <option value="<?php echo $userType->id; ?>"><?php echo $userType->title; ?></option>
                            <?php endif; ?>
						<?php endforeach; ?>
					</select> 
				</div> 		
			</td>
		</tr>
		<tr>
			<th>Phone</th>
			<td>
				<div class="input-group"> 
					<input type="text" readonly name="itemWallet" class="form-control" value="<?php echo $userData->phone; ?>" />
				</div>			
			</td>
		</tr>			
	</tbody>
</table>