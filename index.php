<?php include "header.php"; ?>
<div class="row">
	<div class="panel-group col-md-5">
		<header class="panel panel-success">
			<div class="panel-heading">Daily Sales</div>
                <div class="panel-body">
					<canvas id="myChart"></canvas>
					<!--<div class="alert alert-danger">Graph Temporarily Unavailable!</div>-->
                </div>
		</header>
	</div>
	<div class="panel-group col-md-4 invs">
		<header class="panel panel-info">
			<div class="panel-heading">Inventory status</div>
                <div class="panel-body fixed-height" id='fixed-height-inv' style='color:#B76361'>
					<table>
						<script>
							$.post("ajax-req-handler.php", {
								key: "load-inventory-status"
							}, function(data){
								$(".invs table").html(data);
							});
						</script>
					</table>
                </div>
				<div id="show-more-inv">show more</div>
				<div id="show-less-inv">show less</div>
		</header>
	</div>
	<div class="panel-group col-md-3 aoi accrued-outstanding-incomes">
		<header class="panel panel-danger">
			<div class="panel-heading">Accrued/Outstanding Income</div>
                <div class="panel-body fixed-height fixed-height-aoi">
					<table>
						<script>
							$(document).ready(function(){
								$.post("ajax-req-handler.php", {
									key: "load-accrued-and-outstanding-incomes"
								}, function(data){
									$(".accrued-outstanding-incomes table").html(data);
								});
							});
						</script>
						<?php 
							
						?>
					</table>
                </div>
				<div id="show-more-aoi">show more</div>
				<div id="show-less-aoi">show less</div>
		</header>
	</div>
</div>

<div class="row">
	<div class="panel-group col-md-8 aoi">
		<header class="panel panel-info">
			<div class="panel-heading"> 
				<div style="text-align:right" class="form-inline">
					<span style="float:left;padding:10px;">Sales</span>
					<label for="from">From:</label>
					<input type = "date" id="from" class="form-control">
					<label for="to">To:</label>
					<input type = "date" id="to" class="form-control">
					<button class="btn btn-primary print-sales"><i class="fa fa-print"></i> Print</button>
				</div>
			</div>
                <div class="panel-body sales-print-class">
                	<center class="sales-print-header">
                		<h1>S.A Fashion Gallery</h1>
                		<h3>Sales Record</h3>
                		<h5 class="from-to-print" ></h5>
                	</center>
					<table class="sales-tab" style="@media print{ @page{ size: landscape; } }">
						
					</table>
                </div>
		</header>
	</div>
	<div class="panel-group col-md-4">
		<header class="panel panel-primary">
			<div class="panel-heading"> Record Transaction </div>
                <div class="panel-body">
					<div class="form-group">
						<label for="amount">Amount(&#8377;)</label>
						<input type="text" class="form-control" id="amount">
					</div>
					<div class="form-group">
						<label for="remarks">Remarks</label>
						<input type="text" class="form-control" id="remarks">
					</div>
					<div class="form-group">
						<label for="bill">Bill Number(optional)</label>
						<input type="text" class="form-control" id="bill">
						<p class="invalid-bill-msg" ></p>
					</div>
					<div class="form-group modes-of-payment">
						<label for="ine-cust">Mode Of Transaction</label>
                        <select class="form-control payment-modes">
							<option value="">Mode of Payment</option>
							<option selected value="cash">Cash</option>
							<option value="card">Card</option>
						</select>
                    </div>
					<div class="form-group">
						<label class="radio-inline"><input type="radio" value="in" name="trans-type" id="type">In</label>
						<label class="radio-inline"><input type="radio" value="out" name="trans-type" id="type">Out</label>
					</div>
					<div class="ine-status"></div>
					<input type="button" class="btn btn-success btn-block" value="Submit" id="ine-btn">
                </div>
		</header>
	</div>
</div>
<div class="exec-script"></div>
<script>
	var updateArray = [];
	function convertDate(inputFormat) {
		function pad(s) { return (s < 10) ? '0' + s : s; }
	 	var d = new Date(inputFormat);
	  	return [pad(d.getDate()), pad(d.getMonth()+1), d.getFullYear()].join('/');
	}
	if($("#from").val() == "" && $("#to").val() == ""){
		var today = new Date();
		var dd = today.getDate();
		var mm = today.getMonth()+1; //January is 0!
		var yyyy = today.getFullYear();

		if(dd<10) {
		    dd = '0'+dd
		} 

		if(mm<10) {
		    mm = '0'+mm
		} 

		today = dd + '/' + mm + '/' + yyyy;
		$(".from-to-print").html("Of "+today);
	}
	$(".print-sales").click(function(){
		$.print(".sales-print-class");
	});

	$("#bill").change(function(){
		$.post("ajax-req-handler.php", {
			key: "validate-bill-number-for-transaction-entry", value: $(this).val() 
			}, function(data){
				$(".invalid-bill-msg").html(data);
			});
	});
	$("#ine-btn").click(function(){
		var amount = $("#amount").val();
		var remarks = $("#remarks").val();
		var bill = $("#bill").val();
		var modeOfPayment = $(".payment-modes").val();
		var type = $("input:radio[name='trans-type']:checked").val();
		if(amount != "" || type != "" || modeOfPayment != ""){
			$.post("ajax-req-handler.php", {
				key: "Record-Transaction",
				amt: amount,
				rem: remarks,
				billId: bill,
				mop: modeOfPayment,
				type: type
			}, function(data){
				$.alert(data);
			});
		}
		else{
			$.alert("Amount and Type in Mandatory");
		}
	});
	$(document).ready(function(){ $.post("ajax-req-handler.php", { key: "load-today's-sales" }, function(data){ $(".sales-tab").html(data); }); });
	$("#from, #to").change(function(){
		if($("#from").val() != "" && $("#to").val() != ""){
			$(".from-to-print").html("From "+convertDate($("#from").val())+" To "+convertDate($("#from").val()));
		}
		else if($("#from").val() != "" && $("#to").val() == ""){
			$(".from-to-print").html("From "+convertDate($("#from").val()));
		}
		else if($("#from").val() == "" && $("#to").val() != ""){
			$(".from-to-print").html("Upto "+convertDate($("#to").val()));
		}
		else if($("#from").val() == "" && $("#to").val() == ""){
			var today = new Date();
			var dd = today.getDate();
			var mm = today.getMonth()+1; //January is 0!
			var yyyy = today.getFullYear();

			if(dd<10) {
			    dd = '0'+dd
			} 

			if(mm<10) {
			    mm = '0'+mm
			} 

			today = mm + '/' + dd + '/' + yyyy;
			$(".from-to-print").html("Of "+today);
		}
		$.post("ajax-req-handler.php",{
			key: "date-range-for-sales",
			fromDate: $("#from").val(),
			toDate: $("#to").val()
		},function(data){
			$(".sales-tab").html(data);
		});
	});
	$(document).ready(function(){
		$("#show-more-inv").click(function(){
			$("#show-more-inv").hide();
			$("#show-less-inv").show();
			document.getElementById("fixed-height-inv").style.height = "auto";
			document.getElementById("fixed-height-inv").style.maxHeight = "800px";
			document.getElementById("fixed-height-inv").style.overflow = "scroll";
		});
		
		$("#show-less-inv").click(function(){
			$("#show-more-inv").show();
			$("#show-less-inv").hide();
			document.getElementById("fixed-height-inv").style.height = "368px";
			document.getElementById("fixed-height-inv").style.maxHeight = "800px";
			document.getElementById("fixed-height-inv").style.overflow = "hidden";
		});
		
		$("#show-more-aoi").click(function(){
			$("#show-more-aoi").hide();
			$("#show-less-aoi").show();
			document.getElementById("fixed-height-aoi").style.height = "auto";
			document.getElementById("fixed-height-aoi").style.maxHeight = "800px";
			document.getElementById("fixed-height-aoi").style.overflow = "scroll";
		});
		
		$("#show-less-aoi").click(function(){
			$("#show-more-aoi").show();
			$("#show-less-aoi").hide();
			document.getElementById("fixed-height-aoi").style.height = "368px";
			document.getElementById("fixed-height-aoi").style.maxHeight = "800px";
			document.getElementById("fixed-height-aoi").style.overflow = "hidden";
		});
		
		$.post("ajax-req-handler.php",
		{
			key: "draw-line-chart"
		},
		function( data ){
			data = JSON.parse(data);
			console.log(data);
			var date = [];
			var amt = [];
			for(var i in data){		
				date.push(data[i].sale_date);
				amt.push(data[i].total);
			}
			//console.log(date);
			//console.log(amt);
			var ctx = document.getElementById("myChart").getContext('2d');
			var myChart = new Chart(ctx, {
				type: 'line',
				data: {
					labels: date,
					datasets: [{
						label: '',
						data: amt,
						backgroundColor: [
							'rgba(255, 99, 132, 0.2)',
							'rgba(54, 162, 235, 0.2)',
							'rgba(255, 206, 86, 0.2)',
							'rgba(75, 192, 192, 0.2)',
							'rgba(153, 102, 255, 0.2)',
							'rgba(255, 159, 64, 0.2)'
						],
						borderColor: [
							'rgba(255,99,132,1)',
							'rgba(54, 162, 235, 1)',
							'rgba(255, 206, 86, 1)',
							'rgba(75, 192, 192, 1)',
							'rgba(153, 102, 255, 1)',
							'rgba(255, 159, 64, 1)'
						],
						borderWidth: 1
					}]
				},
				options: {
					scales: {
						yAxes: [{
							ticks: {
								beginAtZero:true
							}
						}]
					}
				}
			});
		});
	});
	</script>
<?php include "footer.php"; ?>