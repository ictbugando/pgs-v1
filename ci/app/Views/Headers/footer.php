    <?php $lnkDateTime  = strtotime(date("Y-m-d H:i:s")); ?>
	
	<script src="<?php echo base_url('js/jquery-3.6.0.min.js'); ?>"></script>	
	<script type="text/javascript" src="<?php echo base_url('js/bootstrap.bundle.min.js'); ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url('js/datepicker-full.min.js'); ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url('js/chart.min.js'); ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url('js/flatpickr.js'); ?>" ></script>
	<script type="text/javascript" src="<?php echo base_url('js/script.js?'.$lnkDateTime); ?>" ></script>
	<?php if(url_is('/dashboard/')): ?>
		<script type="text/javascript"> doChartLoad();</script>
	<?php endif; ?>	
	</body>
</html>