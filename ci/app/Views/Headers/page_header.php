<?php
$router			= service('router');
$controller		= $router->controllerName();
$method			= $router->methodName();

$hSelect		= "";
$tSelect		= "";
$rSelect		= "";
$refSelect		= "";
$pSelect		= "";
$bSelect		= "";
$aSelect		= "";
$sSelect		= "";

if( $controller == "\App\Controllers\Dashboard" && ($method == "index") ){
	$hSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Transactions" && ($method == "history") ){
	$tSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Patients" ){
	$pSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Billing" ){
	$bSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Dashboard"  && ($method == "admin") ){
	$aSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Dashboard" && ($method == "reports") ){
	$rSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Refund" && ($method == "index") ){
	$refSelect	= "mselected";
}
else if( $controller == "\App\Controllers\Dashboard" && ($method == "settings") ){
	$sSelect	= "mselected";
}
//settings
?>
<div class="mymenu-wrapper">
	<div class="mysidebar-header">
		<div class="msideBar">
			<div ><img style="width:70px; height:60px;" src="<?php echo base_url('images/bugando.png'); ?>" /></div>
			<ul class="myside-menu">
				<!--
				<?php if( getAccessTransactionMenu() ): ?>
					<li class="<?php echo $hSelect; ?>">
						<a href="<?php echo base_url('dashboard/'); ?>">
							<i class="fas fa-home"></i><label>Home</label>
						</a>
					</li>
				<?php endif; ?>
				-->
				
				<?php if( getAccessTransactionMenu() ): ?>
					<li class="<?php echo $tSelect; ?>">
						<a href="<?php echo base_url('transactions/history'); ?>">
							<i class="fas fa-solid fa-money-bill-transfer"></i><label>Transactions</label>
						</a>
					</li>
				<?php endif; ?>
				
				<?php if( getAccessPatientMenu() ): ?>
					<li class="<?php echo $pSelect; ?>">
						<a href="<?php echo base_url('patients'); ?>">
							<i class="fas fa-solid fa-table-columns"></i><label>Patients</label>
						</a>
					</li>
				<?php endif; ?>
				
				<?php if( getAccessBillingMenu() ): ?>
					<li class="<?php echo $bSelect; ?>">
						<a href="<?php echo base_url('billing'); ?>">
							<i class="fas fa-solid fa-table-columns"></i><label>Billing</label>
						</a>
					</li>
				<?php endif; ?>
				
				<!--
				<li class="<?php echo $refSelect; ?>">
					<a href="<?php echo base_url('refund'); ?>">
						<i class="fas fa-solid fa-table-columns"></i><label>Refund</label>
					</a>
				</li>
				-->
				<?php if( getAccessReportMenu() ): ?>
					<li class="<?php echo $rSelect; ?>">
						<a href="<?php echo base_url('dashboard/reports'); ?>">
							<i class="fas fa-solid fa-money-bill-transfer"></i><label>Reports</label>
						</a>
					</li>
				<?php endif; ?>
				
				<?php if( getAccessAdminMenu() ): ?>
					<li class="<?php echo $aSelect; ?>">
						<a href="<?php echo base_url('dashboard/admin'); ?>">
							<i class="fas fa-solid fa-user-gear"></i><label>Administrator</label></li>
						</a>
					</li>
				<?php endif; ?>

				<!--
				<li class="<?php echo $sSelect; ?>">
					<a href="<?php echo base_url('dashboard/settings'); ?>">
						<i class="fas fa-solid fa-money-bill-transfer"></i><label>Settings</label>
					</a>
				</li>
				-->
				<li>
					<a href="<?php echo base_url('dashboard/signout'); ?>">
						<i class="fas fa-light fa-arrow-right-from-bracket"></i><label>Signout</label>
					</a>
				</li>

				<li>&nbsp;</li>

			</ul> 
			<div style="background:#FFF;padding:5px;position:absolute;bottom:0;left:0%;width:100%;display:; visibility:hidden;">
				<img style="width:49%; height:40px;" src="<?php echo base_url('images/lipa-switch-nw.png'); ?>" />
				<!--
				<img style="width:69%; height:40px;" src="<?php echo base_url('images/lipaswitch-hr.png'); ?>" />
				-->
			</div>
			<!--
			<div style="background:#001629;padding:5px;position:absolute;bottom:0;left:0%;width:100%;">
				<img style="width:190px; height20px;" src="<?php echo base_url('images/lipaswitch-hr2.png'); ?>" />
			</div>
			-->
			<span class="cross-icon"><i class="fas fa-times"></i></span>
		</div>
		<div class="backdrop"></div>
		<div class="content">
			<header>
				<div class="menu-button" id='mdesktop'>
					<div></div>
					<div></div>
					<div></div>
				</div>
				<div class="menu-button" id='mymobile'>
					<div></div>
					<div></div>
					<div></div>
				</div>
				<h1><?php echo get_instance()->userInfo->name ?? ''; ?></h1>
				<a href="<?php echo base_url('dashboard/settings'); ?>"><img src="<?php echo base_url('images/profile-avatar.png'); ?>" /></a>
			</header>
			<div id="mydata-loader" class="content-data">