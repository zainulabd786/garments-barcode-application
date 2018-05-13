<?php include "header.php"; ?>

<div class="panel-group col-md-3">
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

<div class="panel-group col-md-9">
	<header class="panel panel-primary">
		<ul class="nav nav-tabs">
			<li class="active"><a data-toggle="tab" href="#cash-transactions">Cash Transactions</a></li>
			<li><a data-toggle="tab" href="#bank-transactions">Bank Transactions</a></li>
		</ul>
		<div class="tab-content">
			<div id="cash-transactions" class="tab-pane fade in active">
				<h2 class="panel-heading"> Cash Transactions </h2>
				<div class="panel-body">
					<table style="width:100%">
						<script>
							$(document).ready(function(){
								$.post("ajax-req-handler.php", {key:"load-all-cash-transactions"}, function(data){ $("#cash-transactions table").html(data); });
							});
						</script>
					</table>
				</div>
			</div>
			<div id="bank-transactions" class="tab-pane fade">
				<h2 class="panel-heading"> Bank Transactions </h2>
				<div class="panel-body">
					<table style="width:100%">
						<script>
							$(document).ready(function(){
								$.post("ajax-req-handler.php", {key:"load-all-bank-transactions"}, function(data){ $("#bank-transactions table").html(data); });
							});
						</script>
					</table>
				</div>
			</div>
		</div>
		
	</header>
</div>
<script>
	$("#ine-btn").click(function(){
		var amount = $("#amount").val();
		var remarks = $("#remarks").val();
		var bill = $("#bill").val();
		var modeOfPayment = $(".payment-modes").val();
		var type = $("input:radio[name='trans-type']:checked").val();
		if(amount != "" && type != "" && modeOfPayment != ""){
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
</script>
<?php include "footer.php"; ?>