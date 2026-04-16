$(document).ready(function(){	
	postDataScripts();
	pickBMCDate();
})

function doChartLoad(){	
	var URL			=	$('input[name="fetchChartData"]').val();
				
	//$("#spinner-model").modal('show');
	
	$.ajax({
		type: "POST",
		url:  URL,
	success: function (data) {
		var data = JSON.parse(data);

		//$("#spinner-model").modal('hide');

		$("#fetchChartContainer").empty();
		$("#fetchChartContainer").append(data.html);
		$("#fetchChartContainer").show();			

		loadChartsWeekly(data);
		loadChartsMonthly(data);
		loadChartAnnually(data);
	}
	})
}


function postDataScripts(){
	$(document.body).on('click', '.login-btn' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');

		$(".mySpinnerLogin").show();
		$("#formPostErr").hide();
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			var data = JSON.parse(data);

			$("#alertMsg").empty();
			$(".mySpinnerLogin").hide();

			if(data.status == "111"){
				window.location.replace(data.redLink); //Success
			}
			else{
				$("#alertMsg").append(data.msg);
				$("#formPostErr").show();
			}
			
		}
		})
		
	})

	$(document.body).on('click', '.updateuser-btn' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');
		var rType	= this.id;
		var errorId = "#edtPassErr";

		if(rType == "1"){
			var errorId = "#edtProfErr";
		}

		//$(".mySpinnerLogin").show();
		$(errorId).hide();
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			//alert(data);
			var data 		= JSON.parse(data);
			var alertMsg	= "#"+rType+"alertMsg";

			$(alertMsg).empty();
			//$(".mySpinnerLogin").hide();

			$(alertMsg).append(data.msg);
			$(errorId).show();			

			if(data.status == "11"){
				window.location.replace(data.redLink); //Success
			}
		}
		})
		
	})	
	
	$("#mymobile").click(function() {
		$('.msideBar').addClass("showMenu");
		$('.msideBar').removeClass("widthChange");
		$('.backdrop').addClass('showBackdrop');
	});
	$(".cross-icon").click(function() {
		$('.msideBar').removeClass("showMenu");
		$('.backdrop').removeClass('showBackdrop');
	});
	$(".backdrop").click(function() {
		$('.msideBar').removeClass("showMenu");
		$('.backdrop').removeClass('showBackdrop');
	});
	$("#mdesktop").click(function() {
		$('li label').toggleClass("hideMenuList");
		$('.msideBar').toggleClass("widthChange");
	});
	$('.myside-menu li').click(function() {
		$('.myside-menu li').removeClass();
		$(this).addClass('mselected');
		$('.msideBar').removeClass("showMenu");
	});
	
	$(".txn-page-reload").click(function() {
		location.reload();
	});



	$(document.body).on('click', '.newuser-btn' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');

		$(".mySpinnerForm").show();
		$("#formPostErr").hide();
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			var data = JSON.parse(data);

			$("#alertMsg").empty();
			$(".mySpinnerForm").hide();

			if(data.status == "111"){
				location.reload();
				//window.location.replace(data.redLink); //Success
			}
			else{
				$("#alertMsg").append(data.msg);
				$("#formPostErr").show();
			}
			
		}
		})
		
	})
	
	$(document).on("click", '.newUserPop , .user-pop',function(){
		var user_id	=	this.id;
		var URL		=	$('input[name="getUserPopUrl"]').val();

		if(user_id == "99"){
			var URL		=	$('input[name="newUserFormPopUrl"]').val();
		}		
		
		$("#spinner-model").modal('show');
		
		$.ajax({
			type: "POST",
			url:  URL,
			data: "user_id=" + user_id,
		success: function (html) {
			$("#popUserModal").empty();
			$("#popUserModal").append(html);			
			
			$("#spinner-model").modal('hide');
			$("#newUserPop").modal('show');
		}
		})
		
	});

	$(document).on("click", '.txn-pop',function(){
		var item_id	=	this.id;
		var URL		=	$('input[name="getItemPopUrl"]').val();
		
		$("#spinner-model").modal('show');
		
		$.ajax({
			type: "POST",
			url:  URL,
			data: "item_id=" + item_id,
		success: function (html) {
			$("#popItemModal").empty();
			$("#popItemModal").append(html);
			
			$("#spinner-model").modal('hide');
			$("#staticBackdrop").modal('show');
		}
		})
		
	});

	$(document.body).on('click', '.doprocesspay ' ,function(e){
		var bill	= this.id;
		var URL		= $('input[name="doBillVerifyUrl"]').val();
		
		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "bill_string=" + bill,
		success: function (html) {
			$("#popNewProdForm").empty();
			//$("#popNewProdForm").append(html);	
			
			alert("Process Malipo:: "+ html);
						
			//$("#newProductPop").modal('show');
			$("#spinner-model").modal('hide');
		}
		})
		
	});

	$(document.body).on('click', '.popGroupRole' ,function(e){		
		var gid		= this.id;
		var URL		= $('input[name="editRoleFormPopUrl"]').val();

		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "gid=" + gid,
		success: function (html) {			
			$("#popRoleEditModal").empty();
			$("#popRoleEditModal").append(html);
			$("#editRolePop").modal('show');
			
			$("#spinner-model").modal('hide');
		}
		})
		
	});


	$(document.body).on('click', '.newRefundPop' ,function(e){
		
		$("#refundBillPop").modal('show');

		/*
		var gid		= this.id;
		var URL		= $('input[name="newRefundFormPopUrl"]').val();

		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "gid=" + gid,
		success: function (html) {			
			$("#popRoleEditModal").empty();
			$("#popRoleEditModal").append(html);
			$("#editRolePop").modal('show');
			
			$("#spinner-model").modal('hide');
		}
		})*/
		
	});

	$(document.body).on('click', '.getrefundbills' ,function(e){
		var pWord	= $('input[name="patnumrefund"]').val();
		var URL		= $('input[name="getRefundBillsUrl"]').val();

		$.ajax({
			type: "POST",
			url:  URL,
			data: "pWord=" + pWord,
		success: function (html) {
			$("#displayRefundBillsSearch").empty();
			$("#displayRefundBillsSearch").append(html);
		}
		})
		
	});

	$('#payReportWallet , #payReportDay , #payReportMwezi , #payReportMwaka ').on('change', function() {
		var wallet 		=	$('select[name=payReportWallet]').val();
		var siku		=	$('select[name=payReportDay]').val();
		var mwezi 		=	$('select[name=payReportMwezi]').val();
		var mwaka 		=	$('select[name=payReportMwaka]').val();
		var startD 		=	$('input[name="reportStartD"]').val();
		var stopD 		=	$('input[name="reportStopD"]').val();

		doFetchGeneratedReport(wallet , siku , mwezi, mwaka, startD , stopD, flag=0);
	});
	
	$('#payHistoWallet , #payHistoStatus , #payHistoMwezi , #payHistoMwaka ').on('change', function() {
		loadPaymentDataGlobal("1");
	});
	
	$(document.body).on('click', '#morePayData' ,function(e){
		loadPaymentDataGlobal("2");
	});
	
	$(document.body).on('click', '#payHistoSearch' ,function(e){
		loadPaymentDataGlobal("3");
	});		
	
	
	$('#refHistoStatus , #refHistoMwezi , #refHistoMwaka ').on('change', function() {
		loadReferenceDataGlobal("1");
	})
	
	$(document.body).on('click', '#moreRefData' ,function(e){
		loadReferenceDataGlobal("2");
	});	

	$(document.body).on('click', '#refHistoSearch' ,function(e){
		loadReferenceDataGlobal("3");
	});

	$(document.body).on('click', '.exp-report' ,function(e){
		var startD 		=	$('input[name="reportStartD"]').val();
		var stopD 		=	$('input[name="reportStopD"]').val();
		var URL			=	$('input[name="expUrlBmc"]').val();
		var btnId		= this.id;

		$("#overlay-cover-bmc").show();

		$.ajax({
			type: "POST",
			url:  URL,
			data: "startDate=" + startD+ "&stopDate=" + stopD+ "&btnId=" + btnId,
		success: function (data) {
			$("#overlay-cover-bmc").hide();

			if( isJson(data) ){
				var data = JSON.parse(data);

				if(data.status == "11"){
					if(btnId == "1"){
						window.location.replace(data.path);
					}
					else{
						$("#printPrevReportBmc").empty();
						$("#printPrevReportBmc").append(data.print);
						//$("#staticBackdrop").modal('show');

						printDiv(divId="printPrevReportBmc");
					}
				}
				else{
					alert("Unable to export. Invalid date Range selected");
				}
			}
			else{
				alert("Unable to export. Invalid date Range selected *");
			}	
		}
		})

		$("#spinner-model").modal('hide');

	});
	

	$(document.body).on('click', '.subnewbill' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');

		document.getElementById("bill_details").value = getCookie( document.getElementById("billTempId").value );

		$("#spinner-model").modal('show');
		//$("#formPostErr").hide();
		
		//frm.submit();
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			var data = JSON.parse(data);

			$("#ajaxAlertMsg").empty();
			$("#spinner-model").modal('hide');

			if(data.status == "111"){
				window.location.replace(data.redLink); //Success
			}
			else{
				$("#ajaxAlertMsg").append(alertMessageDisp(data.msg));
			}
		}
		})
	});

	$(document).on("click", '.bill-product-pop',function(){	
		var URL		=	$('input[name="genNewProdFormUrl"]').val();
		
		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
		success: function (html) {
			$("#popNewProdForm").empty();
			$("#popNewProdForm").append(html);			
						
			$("#newProductPop").modal('show');
			$("#spinner-model").modal('hide');
		}
		})
	});

	$('#sysUserTypes ').on('change', function() {
		loadUserList()
	});

	$(document).on("click", '.userLoadMore , .bmcUserSearch',function(){
		loadUserList();
	});

	$(document).on("click", '.prodLoadMore , .prodSearch , .prodWord',function(){
		var URL		=	$('input[name="prodListLoadUrl"]').val();
		var start	=	$('input[name="prodListLoadStart"]').val();
		var word	=	$('input[name="prodListLoadWord"]').val();
		var type	=	$('input[name="prodListType"]').val();

		if(this.id == "prodWord"){
			var word	=	$('input[name="prodSearchWord"]').val();
			var start	=	"0";
			var type	=	"2";
		}

		URL = URL+"/"+start;
		
		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "word=" + word + "&type=" + type,
		success: function (html) {
			$("#dispProdPatDetails").empty();
			$("#dispProdPatDetails").append(html);

			document.getElementById("dispProdPatDetails").scrollIntoView({ behavior: "smooth" });

			
			$("#spinner-model").modal('hide');
		}
		})
	});	

	$(document).on("click", '.billLoadMore , #billWord',function(e){
		e.preventDefault();
		var URL		=	$('input[name="billListLoadUrl"]').val();
		var start	=	$('input[name="billHistoStart"]').val();
		
		if(this.id == "billWord"){
			var word	=	$('input[name="patBillSearch"]').val();
			var start	=	"0";

			$("#billDispBody").empty();
		}
		else{
			var word	=	$('input[name="billListLoadWord"]').val();
		}
		
		URL = URL+"/"+start;
		
		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "word=" + word,
		success: function (html) {
			$("#prependBillLoad").remove();
			$("#billDispBody").append(html);
			
			$("#spinner-model").modal('hide');
		}
		})
	});

	$(document).on("click", '.patLoadMore , .patSearch',function(){
		var URL		=	$('input[name="patListLoadUrl"]').val();
		var start	=	$('input[name="patListLoadStart"]').val();
		var word	=	$('input[name="patListLoadWord"]').val();

		if(this.id == "patWord"){
			var word	=	$('input[name="patSearchWord"]').val();
			var start	=	"0";
		}

		URL = URL+"/"+start;
		
		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
			data: "word=" + word,
		success: function (html) {
			$("#dispLoadPatDetails").empty();
			$("#dispLoadPatDetails").append(html);
			
			$("#spinner-model").modal('hide');
		}
		})
	});
	
	$(document.body).on('click', '.newProdsubmit' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');

		$("#spinner-model").modal('show');
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			var data = JSON.parse(data);

			$("#ajaxAlertMsg").empty();
			$("#spinner-model").modal('hide');

			if(data.status == "111"){
				window.location.replace(data.redLink); //Success
			}
			else{
				$("#ajaxAlertMsg").append(alertMessageDisp(data.msg));
			}
		}
		})

	});

	$(document.body).on('click', '.subprod-srch' ,function(e){
		var URL		= $('input[name="searchProdUrl"]').val();
		var sword	= $('input[name="prod-sword"]').val() 

		$("#spinner-model").modal('show');
		
		$.ajax({
			type: "POST",
			url:  URL,
			data: "sword=" + sword,
		success: function (data) {
			$("#popGetProdForm").empty();
			$("#popGetProdForm").append(data);
			$("#spinner-model").modal('hide');
		}
		})
	});

	
	$("body").on('change', '#newBillCostVal', function(){
		alert("Changed Cost");
	})

	$(document.body).on('click', '.bill-del-item' ,function(e){
		var	prod_id		= this.id;
		var cname		= document.getElementById("billTempId").value;
		var cname2		= document.getElementById("billTempId").value+"_"+prod_id;
		var cname3		= document.getElementById("billTempId").value+"_count"
		var itemCount	= Number( getCookie(cname3) );
		var itemString	= getCookie(cname);
		var exdays		= 1;

		var itemArray   = new Array();
		itemArray = JSON.parse(itemString);
		delete itemArray[prod_id];

		document.getElementById("billrow_"+prod_id).remove();

		setCookie(cname, JSON.stringify(itemArray), exdays);
		setCookie(cname2, "", -2);
		setCookie(cname3, (itemCount-1), exdays);

		updateBillRowNumber();
		updateBillTotal();
	});	

	$(document.body).on('click', '.prodselect' ,function(e){
		var pr 			= 'prod_'+this.id
		var datastring	= $('[data-side="'+pr+'"]').data('params'); // object
		var exdays		= 1;
		var cname		= document.getElementById("billTempId").value;
		var cname2		= document.getElementById("billTempId").value+"_"+datastring.prod_id;
		var cname3		= document.getElementById("billTempId").value+"_count";
		var itemCount	= Number( getCookie(cname3) );
		var itemString	= getCookie(cname);

		var itemArray   = new Array();
		var itemDetObj	= {"id":datastring.prod_id,"qty":datastring.qty,"cost":datastring.cost_amt,"tax":datastring.tax,
						  "discount":datastring.discount,"total":datastring.total};
		
		if( !isJson(itemString) ){
			setCookie(cname, "", -2);
			setCookie(cname2, "", -2);
			setCookie(cname3, "", -2);

			itemArray[ datastring.prod_id ] = itemDetObj;
		}
		else{
			itemArray = JSON.parse(itemString);
			itemArray[ datastring.prod_id ] = itemDetObj;
		}

		itemCount = itemCount+1;

		if( !checkCookie(cname2) ){
			$("#newbillitems").append( genBillHtml(datastring) );			
			setCookie(cname, JSON.stringify(itemArray), exdays);
			setCookie(cname2, datastring.prod_id, exdays);
			setCookie(cname3, itemCount, exdays);

			updateBillRowNumber();
			updateBillTotal();
		}
	});

	$("body").on('change', '#mkoaselect , #wilayaselect', function(){
		var mkoa	= $('select[name=mkoaselect]').val();
		var wilaya	= $('select[name=wilayaselect]').val();
		var URL		= $('input[name="fetchLocsUrl"]').val() + "/" + mkoa + "/" + wilaya;
		var currId	= this.id;

		if(currId == "mkoaselect"){
			$('#wilayaselect').empty().append('<option selected value="0">Not Set</option>');
			$('#kataselect').empty().append('<option selected value="0">Not Set</option>');
		}
		
		if(currId == "wilayaselect"){
			$('#kataselect').empty().append('<option selected value="0">Not Set</option>');
		}		
		
		$.ajax({
			type: "POST",
			url:  URL,
			success: function (data) {
				var data   = JSON.parse(data);

				if(currId == "mkoaselect"){
					$(data.wilaya).each(function(i, val) {
						$("#wilayaselect").append( $('<option></option>').text(val.wilaya_name).val(val.wilaya_id) );
					})
				}

				if(currId == "wilayaselect"){
					$(data.kata).each(function(i, val) {
						$("#kataselect").append( $('<option></option>').text(val.kata_name).val(val.kata_id) );
					})
				}
			}
		})
	});

	$(document).on("click", '.doBillPrint , .doAllPrint',function(){
		var divId = "div"+this.id;

		printDiv(divId)
	 });

	$(document).on("click", '.showBillPop',function(){		
		var URL		= $('input[name="getBillPrevUrl"]').val()+"/"+this.id;

		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
		success: function (html) {
			$("#popPrevBillModal").empty();
			$("#popPrevBillModal").append(html);			
						
			$("#prevBillPop").modal('show');
			$("#spinner-model").modal('hide');
		}
		})	
	});	

	$(document).on("click", '.newPatientPop , .patient-pop',function(){		
		var URL		=	$('input[name="genNewPatFormUrl"]').val();

		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
		success: function (html) {
			$("#popNewPatientModal").empty();
			$("#popNewPatientModal").append(html);			
						
			$("#newPatientPop").modal('show');
			$("#spinner-model").modal('hide');
		}
		})	
	});

	$(document.body).on('click', '.newpatsubmit' ,function(e){
		e.preventDefault();
		var frm    	= $(this).closest('form');

		$("#spinner-model").modal('show');
		
		$.ajax({
			type: frm.attr('method'),
			url:  frm.attr('action'),
			data: frm.serialize(),
		success: function (data) {
			var data = JSON.parse(data);

			$("#ajaxAlertMsg").empty();
			$("#spinner-model").modal('hide');

			if(data.status == "111"){
				window.location.replace(data.redLink); //Success
			}
			else{
				$("#ajaxAlertMsg").append(alertMessageDisp(data.msg));
			}
		}
		})

	});

	$(document).on("click", '.popBillDetail ',function(){		
		var URL		=	$('input[name="genNewPatFormUrl"]').val();

		$("#spinner-model").modal('show');

		$.ajax({
			type: "POST",
			url:  URL,
		success: function (html) {
			$("#popNewPatientModal").empty();
			$("#popNewPatientModal").append(html);			
						
			$("#newPatientPop").modal('show');
			$("#spinner-model").modal('hide');
		}
		})	
	});	
	
}

function alertMessageDisp(msg){
	html =  '<div  class="alert alert-warning alert-dismissible fade show " role="alert"><strong>Message! &nbsp;</strong><span id="alertMsg">'+msg+'</span></div>';
	return html;
}

function genMsg(t,e){
	return 1==e?t='<div class="alert alert-success"><strong>Success!</strong> '+t+".</div>":2==e?t='<div class="alert alert-warning"><strong>Message!</strong> '+t+".</div>":3==e?t='<div class="alert alert-error"><strong>Error!</strong> '+t+".</div>":4==e&&(t='<div class="alert alert-info"><strong>Info!</strong> '+t+".</div>"),t
}

function loadPaymentDataGlobal(fetchType){
	var wallet 		=	$('select[name=payHistoWallet]').val();
	var pstatus		=	$('select[name=payHistoStatus]').val();
	var mwezi 		=	$('select[name=payHistoMwezi]').val();
	var mwaka 		=	$('select[name=payHistoMwaka]').val();
	var start 		=	$('input[name=payHistoStart]').val();
	var URL			=	$('input[name="payHistoUrl"]').val();
	
	//Reset Conter and Search
	if(fetchType == "1"){
		start = "0";
		$('#paySearchWord').val("");
		$('#isSetPaySearch').val("0");
		$('#paySearchString').val("0");		
	}
	
	var searchWord	=	$('input[name="paySearchWord"]').val();
	var isSearch	=	$('input[name="isSetPaySearch"]').val();
	var sString		=	$('input[name="paySearchString"]').val();
	
	if(fetchType == "3" || isSearch == "1"){
		URL	=	$('input[name="searchUrl"]').val();
		
		$('#isSetPaySearch').val("1");
		
		if(sString != searchWord){
			//If 1st time or word changed restart
			$('#paySearchString').val(searchWord);
			start = "0";
		}
	}
	
	$("#spinner-model").modal('show');
	
	$.ajax({
		type: "POST",
		url:  URL,
		data: "start=" + start+ "&wallet=" + wallet+ "&pstatus=" + pstatus+ "&mwezi=" + 
			  mwezi+ "&mwaka=" + mwaka+ "&search=" + "1" + "&searchWord=" + searchWord,
	success: function (data) {
		$("#spinner-model").modal('hide');
		var data   = JSON.parse(data);
		
		$("#payTbody").empty();
		$("#payTbody").append(data.html);
		
		$('#payHistoStart').val(data.start);
		
		if(data.load == "1"){
			$("#morePayData").show();
			$("#finishedPayData").hide();
		}
		else{
			$("#finishedPayData").show();
			$("#morePayData").hide();
		}
	}
	})
}

function loadReferenceDataGlobal(fetchType){
	var rstatus	=	$('select[name=refHistoStatus]').val();
	var mwezi 	=	$('select[name=refHistoMwezi]').val();
	var mwaka 	=	$('select[name=refHistoMwaka]').val();
	var start 	=	$('input[name=refHistoStart]').val();
	var URL		=	$('input[name="refHistoUrl"]').val();
	
	//Reset Conter and Search
	if(fetchType == "1"){
		start = "0";
		$('#refSearchWord').val("");
		$('#isSetRefSearch').val("0");
		$('#refSearchString').val("0");
	}
	
	var searchWord	=	$('input[name="refSearchWord"]').val();
	var isSearch	=	$('input[name="isSetRefSearch"]').val();
	var sString		=	$('input[name="refSearchString"]').val();
	
	if(fetchType == "3" || isSearch == "1"){
		URL	=	$('input[name="searchUrl"]').val();
		
		$('#isSetRefSearch').val("1");
		
		if(sString != searchWord){
			//If 1st time or word changed restart
			$('#refSearchString').val(searchWord);
			start = "0";
		}
	}
	
	$("#spinner-model").modal('show');
	
	$.ajax({
		type: "POST",
		url:  URL,
		data: "start=" + start+ "&rstatus=" + rstatus+ "&mwezi=" + mwezi+ "&mwaka=" + 
			   mwaka+ "&search=" + "1" + "&searchWord=" + searchWord,
	success: function (data) {			
		$("#spinner-model").modal('hide');
		var data   = JSON.parse(data);
		
		$("#refTbody").empty();
		$("#refTbody").append(data.html);
		
		$('#refHistoStart').val(data.start);
		
		if(data.load == "1"){
			$("#moreRefData").show();
			$("#finishedRefData").hide();
		}
		else{
			$("#finishedRefData").show();
			$("#moreRefData").hide();
		}			
	}
	})	
}

function loadChartsWeekly(){
	const ctx = document.getElementById('myChartweekly').getContext('2d');

	const myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ],
			datasets: [{
				label: '# This Week',
				data: [30000000, 33000000, 12000000, 14000000, 3000000, 6000000, 5000000],
				backgroundColor: 'rgba(54, 162, 235, 0.2)',
				borderWidth: 1
			},
			{
				label: '# Last Week',
				data: [20000000, 36000000, 22000000, 11000000, 2000000, 7000000, 6000000],
				backgroundColor: 'rgba(255, 99, 132, 0.2)',
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});	
}

function loadChartsMonthly(){
	const ctx = document.getElementById('myChartsmonthly').getContext('2d');
	const myChart = new Chart(ctx, {
		type: 'bar',
		data: {
			labels: ['1', '2', '3', '4', '5', '6', '7','8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21',  
					'22', '23', '24', '25', '26', '27', '28','29', '30', '31' ],
			datasets: [{
				label: '# This Month',
				data: [30000000, 33000000, 12000000, 14000000, 3000000, 6000000, 5000000],
				backgroundColor: 'rgba(54, 162, 235, 0.2)',
				borderWidth: 1
			},
			{
				label: '# Last Month',
				data: [20000000, 36000000, 22000000, 11000000, 2000000, 7000000, 6000000],
				backgroundColor: 'rgba(255, 99, 132, 0.2)',
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});	
}

function loadChartAnnually(data){
	var mTitle		= data.annualChart.title;
	var currtVal	= data.annualChart.currtVal;
	var prevVal		= data.annualChart.prevVal;

	const ctx = document.getElementById('myChartsannual').getContext('2d');
	
	const myChart = new Chart(ctx, {
		type: 'line',
		data: {
			labels: mTitle,
			datasets: [{
				label: '# This Year',
				data: currtVal,
				backgroundColor: '#00ff00',
				borderWidth: 1
			},
			{
				label: '# Last Year',
				data: prevVal,
				backgroundColor: 'rgba(255, 99, 132, 0.2)',
				borderWidth: 1
			}]
		},
		options: {
			scales: {
				y: {
					beginAtZero: true
				}
			}
		}
	});	
	
}

function setCookie(cname, cvalue, exdays) {
	const d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	let expires = "expires="+ d.toUTCString();
	document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

function getCookie(cname) {
	let name = cname + "=";
	let decodedCookie = decodeURIComponent(document.cookie);
	let ca = decodedCookie.split(';');
	for(let i = 0; i <ca.length; i++) {
	  let c = ca[i];
	  while (c.charAt(0) == ' ') {
		c = c.substring(1);
	  }
	  if (c.indexOf(name) == 0) {
		return c.substring(name.length, c.length);
	  }
	}
	return "";
}

function checkCookie(cname) {
	let cookie = getCookie(cname);

	if (cookie != "") {
	  return true;
	} else {
	  return false;
	}
}

function genBillHtml(datastring){
	const nf = new Intl.NumberFormat();

	var html = "<tr class='billItemRow' id='billrow_"+datastring.prod_id+"' >"+
					"<th class='text-start align-middle'>"+datastring.count+".</th>"+
					"<td class='text-start align-middle'>"+datastring.prod_name+"</td>"+
					"<td class='text-start align-middle'>"+datastring.prod_desc+"</td>"+
					"<td class='text-start align-middle'>"+datastring.countHtml+"</td>"+
					"<td class='text-start align-middle'>"+datastring.costHtml+"</td>"+
					"<td class='text-start align-middle'>"+datastring.taxHtml+"</td>"+
					"<td class='text-start align-middle'>"+datastring.discHtml+"</td>"+
					"<td class='text-start align-middle'>"+datastring.totalHtml+"</td>"+
					"<td class='text-start align-middle'>"+datastring.delItemHtml+"</td>"+
				"</tr>";
				
	return html;
}

function isJson(item) {
	let value = typeof item !== "string" ? JSON.stringify(item) : item;
	try {
	  value = JSON.parse(value);
	} catch (e) {
	  return false;
	}
  
	return typeof value === "object" && value !== null;
}

function updateBillQty(prod_id){
	var newQty	= parseInt(parseMyInt(document.getElementById("billQty_"+prod_id).value));

	document.getElementById("billQty_"+prod_id).value = newQty.toLocaleString("en-US");

	var cname		= document.getElementById("billTempId").value;
	var itemString	= getCookie(cname);
	
	var itemArray   = JSON.parse(itemString);
	var itemDetObj	= itemArray[prod_id];

	itemDetObj.qty	= newQty;

	itemArray[prod_id] = itemDetObj;
	computeBillItemTotal(itemArray , prod_id);
}

function updateBillCost(prod_id){
	var newCost 			= parseInt(parseMyInt(document.getElementById("billCost_"+prod_id).value));
	
	document.getElementById("billCost_"+prod_id).value = newCost.toLocaleString("en-US");

	var cname		= document.getElementById("billTempId").value;
	var itemString	= getCookie(cname);
	var itemArray   = JSON.parse(itemString);
	var itemDetObj	= itemArray[prod_id];

	itemDetObj.cost = newCost;

	itemArray[prod_id] = itemDetObj;
	computeBillItemTotal(itemArray , prod_id);
}

function updateBillTax(prod_id){
	var newTax	= parseInt(parseMyInt(document.getElementById("tax_select_"+prod_id).value));

	var cname		= document.getElementById("billTempId").value;
	var itemString	= getCookie(cname);
	var itemArray   = JSON.parse(itemString);
	var itemDetObj	= itemArray[prod_id];

	itemDetObj.tax = newTax;

	itemArray[prod_id] = itemDetObj;
	computeBillItemTotal(itemArray , prod_id);
}

function updateBillDiscount(prod_id){
	var newDisc	= parseInt(parseMyInt(document.getElementById("billDisc_"+prod_id).value));

	document.getElementById("billDisc_"+prod_id).value = newDisc+"%";
	
	var cname		= document.getElementById("billTempId").value;
	var itemString	= getCookie(cname);
	var itemArray   = JSON.parse(itemString);
	var itemDetObj	= itemArray[prod_id];

	itemDetObj.discount = newDisc;

	itemArray[prod_id] = itemDetObj;
	computeBillItemTotal(itemArray , prod_id);
}

function computeBillItemTotal(itemArray , prod_id){
	var cname		= document.getElementById("billTempId").value;
	var itemDetObj	= itemArray[prod_id];

	var cost	= parseInt(itemDetObj.cost);
	var qty		= parseInt(itemDetObj.qty);
	var tax		= parseInt(itemDetObj.tax);
	var disc	= parseInt(itemDetObj.discount);
	var total	= 0;
	var exdays	= 1;

	total = (cost*qty);

	if( (disc > 0) && (disc < 101) ){
		total = (total - (total*(disc/100)) );
	}	

	if( (tax > 0) && (tax < 101) ){
		total = (total + (total*(tax/100)) );
	}

	itemDetObj.total	= total;
	itemArray[prod_id]	= itemDetObj;

	setCookie(cname, JSON.stringify(itemArray), exdays);
	document.getElementById("billTotal_"+prod_id).value = total.toLocaleString("en-US");

	updateBillTotal();
}

function parseMyInt(str){
	return str.replace(/\D/g, '');
}

function updateBillRowNumber(){
	var renum = 1;
	$(".billItemRow th").each(function() {
		$(this).text(renum);
		renum++;
	});	
}

function updateBillTotal(){
	var cname		= document.getElementById("billTempId").value;
	var itemString	= getCookie(cname);
	var itemArray   = JSON.parse(itemString);
	var grandTotal	= 0;
	var taxTotal	= 0;

	itemArray.forEach((itemDetObj) => {
		if(itemDetObj != null){
			if( itemDetObj.hasOwnProperty('qty') ){
				var cost	= parseInt(itemDetObj.cost);
				var qty		= parseInt(itemDetObj.qty);
				var tax		= parseInt(itemDetObj.tax);
				var disc	= parseInt(itemDetObj.discount);
				var total	= parseInt(itemDetObj.total);

				grandTotal	= grandTotal+total;
				taxTotal	= taxTotal + (cost*(tax/100));
			}
		}		
	});

	$(".billTaxTotal").text(taxTotal);
	$(".billGrandTotal").text(grandTotal);
}

function pickBMCDate(){
    const elems = document.querySelectorAll('.datepicker_input');
    for (const elem of elems) {
		const datepicker = new Datepicker(elem, {
			'format': 'yyyy/mm/dd',
			title: this.id
		})
	};	
}

function cloneElement(el) {
	const clone = el.cloneNode(true);
	copyCSS(el, clone);

	return clone;
}
  
function copyCSS(source, dest) {
	const computedStyle = window.getComputedStyle(source);
	const cssProperties = Object.keys(computedStyle);
	for (const cssProperty of cssProperties) {
		dest.style[cssProperty] = computedStyle[cssProperty];
	}

	for (let i = 0; i < source.children.length; i++) {
		copyCSS(source.children[i], dest.children[i]);
	}
}
  

function printDiv(divId) {
	var el = document.getElementById(divId);
	const clone = cloneElement(el);

	const winPrint = window.open('', '', 'width=900,height=650');
	winPrint.document.write(clone.outerHTML);
	//winPrint.document.write(Array.from(document.querySelectorAll("style")).map(x => x.outerHTML).join("") + el.innerHTML);
	winPrint.document.close();
	winPrint.focus();
	winPrint.print();
	winPrint.close()

}


function loadUserList(){
	var URL		=	$('input[name="userListLoadUrl"]').val();
	var start	=	$('input[name="userListLoadStart"]').val();
	var word	=	$('input[name="userListLoadWord"]').val();
	var type	=	$('input[name="userListType"]').val();

	if(this.id == "bmcUserSearch"){
		var word	=	$('input[name="searchUserWord"]').val();
		var start	=	"0";
	}
	
	URL = URL+"/"+start;
	
	$("#spinner-model").modal('show');

	$.ajax({
		type: "POST",
		url:  URL,
		data: "word=" + word + "&start=" + start + "&type=" + type ,
	success: function (html) {
		$("#dispBmcUsersList").empty();
		$("#dispBmcUsersList").append(html);
		
		document.getElementById("dispBmcUsersList").scrollIntoView({ behavior: "smooth" });
		
		$("#spinner-model").modal('hide');
	}
	})	
}

function doFetchGeneratedReport(wallet , siku , mwezi, mwaka, startD , stopD, flag){
	var URL	=	$('input[name="reportFetchUrl"]').val();

	$("#overlay-cover-bmc").show();

	$.ajax({
		type: "POST",
		url:  URL,
		data: "wallet=" + wallet+ "&siku=" + siku+ "&mwezi=" + mwezi+ "&mwaka=" + mwaka+ "&startD=" + startD+ 
			  "&stopD=" + stopD+ "&flag=" + flag,
	success: function (html) {
		$("#overlay-cover-bmc").hide();
		$("#dispBMCReport").empty();
		$("#dispBMCReport").append(html);
	}
	})
}


document.addEventListener("DOMContentLoaded", function () {
	let startInput = document.getElementById("mstartdate");
	let endInput = document.getElementById("menddate");
	let errorMessage = document.getElementById("error-message");

	// Clear input fields on page load to prevent saving previous selections
	startInput.value = "";
	endInput.value = "";

	let startPicker = flatpickr(startInput, {
		enableTime: true,
		time_24hr: true,
		dateFormat: "Y-m-d H:i",
		onChange: function (selectedDates, dateStr) {
			endPicker.set("minDate", dateStr);
			validateDateRange();
			onDateChange("start", dateStr, selectedDates[0]);
		}
	});

	let endPicker = flatpickr(endInput, {
		enableTime: true,
		time_24hr: true,
		dateFormat: "Y-m-d H:i",
		onChange: function (selectedDates, dateStr) {
			validateDateRange();
			onDateChange("end", dateStr, selectedDates[0]);
		}
	});

	function validateDateRange() {
		if (!startInput.value || !endInput.value) return;

		const startDate = startPicker.parseDate(startInput.value, "Y-m-d H:i:s");
		const endDate = endPicker.parseDate(endInput.value, "Y-m-d H:i:s");

		if (endDate < startDate) {
			errorMessage.style.display = "block";
			endInput.value = ""; // Clear invalid end date
		}

	}
	
	function onDateChange(field, value, dateObj) {
		const startInput = document.getElementById("mstartdate").value;
		const endInput = document.getElementById("menddate").value;

		if (startInput && endInput) {
			const startDate = new Date(startInput);
			const endDate = new Date(endInput);

			// Convert to Unix timestamp (seconds since epoch)
			const startD	= Math.floor(startDate.getTime() / 1000);
			const stopD		= Math.floor(endDate.getTime() / 1000);			

			var wallet 		=	$('select[name=payReportWallet]').val();
			var siku		=	$('select[name=payReportDay]').val();
			var mwezi 		=	$('select[name=payReportMwezi]').val();
			var mwaka 		=	$('select[name=payReportMwaka]').val();
	
			doFetchGeneratedReport(wallet , siku , mwezi, mwaka, startD , stopD, flag=1);
		}




	}



});