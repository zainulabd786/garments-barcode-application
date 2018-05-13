<?php include "header.php"; ?>
<div class="row">  
	<div class="trans-type-container form-inline">
		<strong>&nbsp Transaction Type: &nbsp </strong>
		<label class="radio-inline"><input type="radio" class="trans-type" name="trans-type" value="sale" checked>Sale</label>
		<label class="radio-inline"><input type="radio" class="trans-type" name="trans-type" value="sale-ret">Sale Return</label>
		<label class="radio-inline"><input type="radio" class="trans-type" name="trans-type" value="exc">Exchange</label>
		&nbsp <input type='text' style='width:20%;display:none;margin:10px' class='form-control bill-no' id="bill-no" placeholder="Enter Bill number">
		<div style="float: right;" class="alert alert-warning internet-alert"><i class="fa fa-warning"></i> WARNING! SMS will not be delivered, Please check Your Internet connection</div>
		<div style="float: right;" class="alert alert-warning zero-sms-alert"><i class="fa fa-warning"></i> WARNING! SMS will not be delivered, You are running out of messages.</div>
	</div>
    <div class="panel-group col-md-12 remove-for-exchange">
		<header class="panel panel-default">
			<div class="panel-heading">Products <input type = "text" class="product-searchbar" style="text-transform:uppercase" autofocus></div>
                <div class="panel-body row" style="text-align:center;">
					<div class="checkout-product-list col-md-8">
						
					</div>
					<div class="col-md-4 bill-area">
						<div class="prices-col">
							<div class="bill">
							
								<p style="float:left;width:50%;font-size:11px;text-align:left">GST No.<br/> 09BPZPA1404H1ZW </p>
								<p class='bill-id' style="float:right;width:49%;font-size:11px;text-align:right">
								<?php	
									$bill_sql = "SELECT bill_id FROM bill_info ORDER BY bill_date DESC LIMIT 1";
									$bill_result = $conn->query($bill_sql);
									if(num_rows($bill_result)>0){
										while($bill_row=$bill_result->fetch_assoc()){
											$bill_id = $bill_row['bill_id'];
											if(substr($bill_id, 0, 6) != date("ymd")){
												$bill_id = date('ymd').'0';
											}
											else{
												$bill_id += 1;
											}
										}
									}
									else{
										$bill_id = date('ymd').'0';
									}
									echo $bill_id;
									$conn->close();	?>
								</p>
								<div>&nbsp </div>
								<h2>S.A Fashion Gallery</h2>
								<p>Pratap Nager, Saharanpur-247001</p>
								<div class="cust-info row">
									<div class="cust-name col-xs-4"></div>
									<div class="cust-contact col-xs-4"></div>
									<div class="cust-time col-xs-4"><?php echo date("d/m/Y h:i:sa"); ?></div>
								</div> 
								<table class="soft-bill">
									<tr>
										<td><strong>Item</strong></td>
										<td><strong>Disc<br/>(%)</strong></td>
										<td><strong>GST<br/>(%)&#8377;</strong></td>
										<td><strong>MRP<br/>(&#8377;)</strong></td>
										<td><strong>Price<br/>(&#8377;)</strong></td>
									</tr>
								</table>
							</div>
						</div>
						<div class="total-prices"></div>
						<button type="button" class="btn btn-primary pull-right tot-btn">Total</button>
					</div>
					<div class='col-md-4 returned-products-container'><h3>Products to be returned</h3><table style="border:2px solid;"></table></div>
                </div>
		</header>
	</div>
	<!-- Exchange panel -->
	<div style="display:none;" class="exchange-panel">
		<div class="panel-group col-md-5 aoi">
			<header class="panel panel-primary">
				<div class="panel-heading form-inline"> <label for="what">Exchange: </label> <input style="width:80%;" type="text" class="form-control" id='what'></div>
					<div class="panel-body prod-in-bill">
						<table>
							<tr style="border-bottom:2px solid;">
								<td><strong>Item Name</strong></td>
								<td><strong>Size</strong></td>
								<td><strong>Quantity</strong></td>
								<td><strong>Discount</strong></td>
								<td><strong>MRP</strong></td>
								<td><strong>Amount</strong></td>
							</tr>
						</table>
					</div>
			</header>
		</div>
		
		<div class="panel-group col-md-2">
			<header class="panel panel-primary">
				<div class="panel-heading">Difference</div>
					<div class="panel-body row">
						<div class="row>">
							<div class="ret col-md-6">Return</div>
							<div class="rec col-md-6">Recieve</div>
						</div>
						<div class="row>">
							<div class="diff col-md-12"></div>
							<button type="button" class="btn btn-primary btn-block diff-btn">Find Difference</button>
						</div>
					</div>
			</header>
		</div>
		
		<div class="panel-group col-md-5 aoi">
			<header class="panel panel-primary">
				<div class="panel-heading form-inline"> <label for="with">With: </label> <input style="width:80%;" type="text" class="form-control" id='with'></div>
					<div class="panel-body prod-to-be-exchanged-with">
						<table style="width:100%;">
							<tr style="border-bottom:2px solid">
								<td><strong>Item Name</strong></td>
								<td><strong>Size</strong></td>
								<td><strong>Quantity</strong></td>
								<td><strong>Discount</strong></td>
								<td><strong>MRP</strong></td>
								<td><strong>Amount</strong></td>
							</tr>
							
						</table>
					</div>
			</header>
		</div>
	</div>
	<!-- Exchange panel ends here -->
	<div class="panel-group col-md-12">
		<header class="panel panel-default">
			<div class="panel-heading">Customer Details</div>
            <div class="panel-body" style="text-align:center;">
                <div class="form-inline">
                    <div class="form-group">
                        <input type="text" class="form-control" id="mob" autocomplete="user-mobile-number" placeholder="Contact Number">
						<table class="cust-num-suggestions"></table>
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" id="name" placeholder="Customer Name" style="text-transform:uppercase">
                    </div>
					<div class="form-group">
                        <input type="text" class="form-control" id="total" value="0" placeholder="Total" disabled>
                    </div>
					<div class="form-group">
                        <input type="text" class="form-control" id="paid" value="0" placeholder="Amount Paid">
                    </div>
					<div class="form-group modes-of-payment">
                        <select class="form-control payment-modes">
							<option value="">Mode of Payment</option>
							<option value="cash">Cash</option>
							<option value="card">Card</option>
							<option value="both">Both</option>
						</select>
                    </div>
					<div class="input-group cash-card-payment">
						<span class="input-group-btn"><input style="width:100%" id="cash" type="text" class="form-control" placeholder="Cash"></span>
						<input style="width:100%" id="card" type="text" class="form-control" placeholder="Card">
					</div>
                    <button class="btn btn-primary save-btn">Submit</button>
                </div>
				<div class="dues-to-customer"></div>
				<div class="validate-msg"></div>
            </div>
		</header>
	</div>
</div>
<div class="array-of-products"></div>
<div class="exec-script"></div>
<div class="sale-return-array"></div>
<script>				
	$(".internet-alert, .zero-sms-alert").hide();
	window.setInterval(function(){
	  $.post("ajax-req-handler.php", {key:"check-internet-connection"}, function(data){ if(data == "false") $( ".internet-alert" ).show( "slide" ); else $( ".internet-alert" ).hide( "slide" ); });
	}, 5000);
	$.post("ajax-req-handler.php", {key: "check-number-of-messages"}, function(data){ if(data == "false") $( ".zero-sms-alert" ).show( "slide" ); else $( ".internet-alert" ).hide( "slide" ); });
	$(".payment-modes").change(function(){
		if($(".payment-modes").val() == "both"){
			$(".cash-card-payment").show();
			$("#paid").hide( "slide" );
		}
		else{
			$(".cash-card-payment").hide();
			$("#paid").show( "slide" );
		}
	});
	$(".cash-card-payment").hide();
	$("#cash, #card").keyup(function(){
		var cashAmt = $("#cash").val();
		var cardAmt = $("#card").val();
		$("#paid").val(parseInt(cashAmt)+parseInt(cardAmt)).trigger("keyup");
	});
	$(".returned-products-container").hide();
	$("#mob").change(function(){
		$.post("ajax-req-handler.php", { key: "find-dues-if-any", value: $(this).val() }, function(data){ $(".dues-to-customer").html(data); });
	});
	$("#mob").keyup(function(){
		$(".cust-contact").text(+$(this).val());
		$.post("ajax-req-handler.php", { key: "search-customer-if-it-already-exists", val: $(this).val() }, function(data){
				$(".cust-num-suggestions").html(data);
		});
		$(".cust-num-suggestions").show();
		if($(this).val() == ""){
			$(".cust-num-suggestions").hide();
		}
		if($(this).val().length < 10 || $(this).val().length > 10) { 
			$(".validate-msg").html("<div class='alert alert-danger alert-xs'>Mobile number should not be less than or greater than 10 digits</div>'") 
		}		
		else{
			$(".validate-msg").html("");
		}
		var charTest = /[A-Za-z]/g;
		var specCharTest = /^[A-Za-z0-9]*$/g;
		if(charTest.test($(this).val()) || specCharTest.test($(this).val()) == false) $.alert("<div class='alert alert-danger'>ERROR! Please Enter a Valid 10 Digit Mobile Number</div>");
		
	});
	$("#name").keyup(function(){
		$(".cust-name").text($(this).val());
	});
	$("#paid").keyup(function(){
		$(".bill-paid").html("<td colspan=3>Paid: </td><td colspan=2'>&#8377; "+$(this).val()+"/-</td>");
		var due = parseInt($("#total").val()) - parseInt($(this).val());
		$(".due-amt").html("<td colspan=3>Due:</td><td colspan=2>&#8377; "+due+"/-</td>");
	});
	$("#what").change(function(){
		var id = $(this).val();
		var pickQty = $(".exchange-panel .prod-in-bill").find("#"+id).find("#sqty").text().replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');
		if(pickQty > 1){
			$.post("ajax-req-handler.php",{
				key: "barcode-input-for-the-product-to-be-returned",
				value: $(this).val(),
				billNum: $(".bill-no").val()
			},function(data){
				$.alert({
					title: "Select Quantity to Return",
					buttons: {
						OK: {text: 'OK', action: function(){ $("#qtbr").trigger('change'); }}
					},
				content: data
				});
				$('#what').val("");
				$('#with').focus();
			});
		}
		else{
			$.post("ajax-req-handler.php",{
				key: "barcode-input-for-the-product-to-be-returned",
				value: $(this).val(),
				billNum: $(".bill-no").val()
			},function(data){
				$(".exec-script").html(data);
				$('#what').val("");
				$('#with').focus();
			});
		}
	});
	$("#with").change(function(){
		var id = $(this).val();
		if(!$(".checkout-product-list").find("."+id).hasClass(id)){
			$.post("ajax-req-handler.php",
			{
				key: "Exchange-product-with",
				barcodeVal: $(this).val()
			},
			function( data ){
				$(".prod-to-be-exchanged-with table").append( data );
				$("#with").val("");
			});
			var getTax = $(".add-tax").empty();
		}
		else{
			var prevQty = $(".exchange-panel .prod-in-bill").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").val();
			$(".exchange-panel .prod-in-bill").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").val(parseInt(prevQty) + 1);
			$(this).val("");
			$(".exchange-panel .prod-in-bill").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").trigger("change");
		}
	});
	$(".trans-type").click(function(){
		if($("input:radio[name='trans-type']:checked").val() == "sale-ret"){
			$(".trans-type-container").find("input[type='text'], .sale-date, .find-cust-rec").show();
			$(".bill-area").hide();
			$(".returned-products-container").show();
			//$(".checkout-product-list").removeClass("col-md-8");
			//$(".checkout-product-list").addClass("col-md-12");
			$(".checkout-product-list").empty();
			$('.bill-no').show();
			$('#total').val(0);
			$('#paid').val(0);
			$(".exchange-panel").hide();
			$(".payment-modes").hide();
		}
		else if($("input:radio[name='trans-type']:checked").val() == "exc"){
			$(".bill-area").hide();
			$(".checkout-product-list").empty();
			$('.cnum').empty();
			$('.sale-date').empty();
			$('#total').val(0);
			$('#paid').val(0);
			$('.remove-for-exchange').hide();
			$('.bill-no').show();
			$(".exchange-panel").show();
			$(".payment-modes").show();
		}
		else{
			$(".trans-type-container").find("input[type='text'], .sale-date, .find-cust-rec").hide();
			$(".bill-area").show();
			$(".returned-products-container").hide();
			//$(".checkout-product-list").addClass("col-md-8");
			//$(".checkout-product-list").removeClass("col-md-12");
			$(".checkout-product-list").empty();
			$('.product-searchbar').val("");
			$('.cnum').empty();
			$('.sale-date').empty();
			$(".cust-info").hide();
			$('#total').val(0);
			$('#paid').val(0);
			$('.bill-no').hide();
			$('.remove-for-exchange').show();
			$(".exchange-panel").hide();
			$(".payment-modes").show();
		}
	
	});
	$(".bill-no").change(function(){
		if($("input:radio[name='trans-type']:checked").val() == "sale-ret"){
			$('.product-searchbar').val("");
			$('#total').val(0);
			$('#paid').val(0);
			$('#mob').val("");
			$('#name').val("");
			$.post("ajax-req-handler.php",
			{	
				key: "fetch-customer-data-for-Sales-return",
				billNumber: $(this).val()
			},
			function( data ){
				$(".checkout-product-list").html( data );
			});
		}
		else{
			$('.product-searchbar').val("");
			$('#total').val(0);
			$('#paid').val(0);
			$('#mob').val("");
			$('#name').val("");
			$.post("ajax-req-handler.php",
			{	
				key: "fetch-customer-data-for-Sales-return",
				billNumber: $(this).val()
			},
			function( data ){
				$(".exchange-panel .prod-in-bill").html( data );
			});
		}
	});
	var arrayOfProducts = [];
	var prodArrForSaleRet = [];
	$('.tot-btn').click(function(){
		function getSum(total, num) {
			return +total + +Math.round(num); 
		}
		$('.prices-col script').remove();
		$('.tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg').remove();
		var allPrices = $('.prices-col table tbody tr td:not(.to-remove)').text();
		var allTaxes = $('.prices-col table tbody tr .tax-amt-box').text();
		var removeBreak = $.trim(allPrices.replace(/[\t\n]+/g,'')).replace(/[^0-9.\s]/gi, '').replace(/[_\s]/g, ' ');
		var removeTaxBreak = $.trim(allTaxes.replace(/[\t\n]+/g,'')).replace(/[^0-9.\s]/gi, ' ').replace(/[_\s]/g, ' ');
		var arrayPrices = removeBreak.split(' ');
		var arrayTaxes = removeTaxBreak.split(' ');
		var sgstNcgst = arrayTaxes.reduce(getSum)/2;
		$('.prices-col .bill table').append("<tr style='border:none' class='tot-payable-amt'><td colspan=3>Total:</td> <td colspan=2>&#8377; "+arrayPrices.reduce(getSum)+"/-</td></tr>");
		$('.prices-col .bill table').append("<tr style='border:none;background-color:#ffffff' class='bill-paid tot-paid-amt'><td colspan=3>Paid:</td><td colspan=2>&#8377; "+arrayPrices.reduce(getSum)+"/-</td></tr><br/>");
		$('.prices-col .bill table').append("<tr style='border:none;background-color:#ffffff' class='sgst'><td colspan=3>SGST:</td><td colspan=2>&#8377; "+sgstNcgst+"/-</td></tr><br/>");
		$('.prices-col .bill table').append("<tr style='border:none;background-color:#ffffff' class='cgst'><td colspan=3>CGST:</td><td colspan=2>&#8377; "+sgstNcgst+"/-</td></tr><br/>");
		$('.prices-col .bill table').append("<tr style='border:none;' class='due-amt'></tr>");
		$('.prices-col').append("<div style='text-align:center;' class='thanks-msg'>Thank you for shopping with us<br/>Please come again soon</div><p class='eden-add' style='text-align:center; font-size:12px'> Eden Solutions(https://edensolutions.co.in) | 9808033480</p>");
		$('#total').val(arrayPrices.reduce(getSum));
		$('#paid').val(arrayPrices.reduce(getSum));
		console.log();
	});
	
	$(".diff-btn").click(function(){
		function getSum(total, num) {
			return +total + +Math.round(num);
		}
		var arrayOfRetAmtVal = [];
		var countRet = $('.ret').children('div').length;
		for(var i=1;i<=countRet;i++){
			var values = $( ".ret div:nth-child("+i+")" ).text();
			arrayOfRetAmtVal.push(values);
		}
		var totAmtToBeRet = arrayOfRetAmtVal.reduce(getSum);
		$(".exchange-panel .ret").append("<div class='alert alert-info'>"+totAmtToBeRet+"</div>");
		
		var arrayOfRecAmtVal = [];
		var countRec = $('.rec').children('div').length;
		for(var i=1;i<=countRec;i++){
			var values = $( ".rec div:nth-child("+i+")" ).text();
			arrayOfRecAmtVal.push(values);
		}
		var totAmtToBeRec = arrayOfRecAmtVal.reduce(getSum);
		$(".exchange-panel .rec").append("<div class='alert alert-info'>"+totAmtToBeRec+"</div>");
		var diff = totAmtToBeRec - totAmtToBeRet;
		if(diff < 0) $(".exchange-panel .diff").html("<hr/><div style='text-align:center' class='alert alert-danger'>"+diff+"</div>");
		else $(".exchange-panel .diff").html("<hr/><div style='text-align:center' class='alert alert-success'>"+diff.toFixed()+"</div>");
		$("#total").val(diff.toFixed());
		$("#paid").val(diff.toFixed().replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, ''));
	});
	
	$('.product-searchbar').change(function(){
		$(".tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg").remove();
		if($("input:radio[name='trans-type']:checked").val() == "sale-ret"){
			var id = $(this).val();
			var pickQty = $(".checkout-product-list").find("#"+id).find("#sqty").text().replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '');
			if(pickQty > 1){
				$.post("ajax-req-handler.php",{
					key: "barcode-input-for-the-product-to-be-returned",
					value: $(this).val(),
					billNum: $(".bill-no").val()
				},function(data){
					$.alert({
						title: "Select Quantity to Return",
						buttons: {
							OK: {text: 'OK', action: function(){ $("#qtbr").trigger('change'); }}
						},
					content: data
					});
					$('.product-searchbar').val("");
				});
			}
			else{
				$.post("ajax-req-handler.php",{
					key: "barcode-input-for-the-product-to-be-returned",
					value: $(this).val(),
					billNum: $(".bill-no").val()
				},function(data){
					$(".exec-script").html(data);
				});
			}
		}
		else{
			var id = $(this).val();
			if(!$(".checkout-product-list").find("."+id).hasClass(id)){
				$.post("ajax-req-handler.php",
				{
					key: "barcode-input",
					barcodeVal: $('.product-searchbar').val()
				},
				function( data ){
					$(".checkout-product-list").append( data );
				});
				var getTax = $(".add-tax").empty();
			}
			else{
				var prevQty = $(".checkout-product-list").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").val();
				$(".checkout-product-list").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").val(parseInt(prevQty) + 1);
				$(this).val("");
				$(".checkout-product-list").find("."+id).find(".single-product-details").find("table").find("tr:even").find("td .quantity").trigger("change");
			}
			
		}
	});
	
	$('.save-btn').click(function(){
		$(".save-btn").hide();
		if($("input:radio[name='trans-type']:checked").val() == "sale-ret"){
			$.post("ajax-req-handler.php",
			{
				key: "submit-sales-return-data",
				mobile: $('#mob').val(),
				paidAmt: $('#paid').val(),
				total: $('#total').val(),
				retProds: $(".sale-return-array").text(),
				name: $("#name").val(),
				billNum: $(".bill-no").val()
			},
			function( data ){
				$.alert({
					title: data,
					buttons: {
						OK: {text: 'OK', action: function(){ location.reload(); }}
					},
					content: data
				});
			});
		}
		else if($("input:radio[name='trans-type']:checked").val() == "exc"){
			if($(".payment-modes").val() == "both"){
				var cashNcardAmt = $('#cash').val()+","+$('#card').val().toString();
				$.post("ajax-req-handler.php", {
					key: "submit-data-for-the-products-exchanged",
					replacedProds: $('.array-of-products').text(),
					retProds: $('.sale-return-array').text(),
					billNum: $(".bill-no").val(),
					total: $("#total").val(),
					paid: cashNcardAmt,
					modeOfPayment: $(".payment-modes").val(),
					cid: $("#mob").val()
				}, function(data){
					$.alert(data);
				});
			}
			else{
				$.post("ajax-req-handler.php", {
					key: "submit-data-for-the-products-exchanged",
					replacedProds: $('.array-of-products').text(),
					retProds: $('.sale-return-array').text(),
					billNum: $(".bill-no").val(),
					total: $("#total").val(),
					paid: $("#paid").val(),
					modeOfPayment: $(".payment-modes").val(),
					cid: $("#mob").val()
				}, function(data){
					$.alert(data);
				});
			}
		}
		else{
			var cashNcardAmt = $('#cash').val()+","+$('#card').val().toString();
			var prodStr = $('.array-of-products').text();
			var prodArr = prodStr.split("-");
			for(var i=0; i<prodArr.length; i++){
				var prodObj = prodArr[i];
				var objToArr = prodObj.split("/");
				for(var j=0;j<1;j++){
					$.post("ajax-req-handler.php",
					{
						key: "deduct-quantity-from-inventory",
						pid: objToArr[0],
						quantity: objToArr[1]
					},
					function( data ){
						//$.alert( data );
					});
					
				}
			}
			if($(".payment-modes").val() == "both"){
				$.post("ajax-req-handler.php",
				{
					key: "insert-sale-details-into-database",
					mobile: $('#mob').val(),
					cname: $('#name').val(),
					totalAmt: $('#total').val(),
					paidAmt: cashNcardAmt,
					billId: $(".bill-id").text().replace(/\s+/g,"").replace(/(\r\n|\n|\r)/gm,""),
					Products: $('.array-of-products').text(),
					modeOfPayment: $(".payment-modes").val()
				},
				function( data ){
					$.confirm({
						title: "Okay", 
						type: 'green',
						typeAnimated: true,
						columnClass: 'col-md-8 col-md-offset-2',
						buttons: {
							OK: {
								text: 'OK',
								action: function(){
									
								}
							},
							OK: {
								text: 'Done',
								action: function(){
									location.reload();
								}
							},
							PRINT: {
								text: 'PRINT',
								action: function(){
									$.print( '.prices-col' );
								}
							}
						},
						content: data,
						contentLoaded: function(data, status, xhr){
							this.setContentAppend('<br>Status: ' + status);
						}
					});
				});
			}
			else{
				$.post("ajax-req-handler.php",
				{
					key: "insert-sale-details-into-database",
					mobile: $('#mob').val(),
					cname: $('#name').val(),
					totalAmt: $('#total').val(),
					paidAmt: $('#paid').val(),
					billId: $(".bill-id").text().replace(/\s+/g,"").replace(/(\r\n|\n|\r)/gm,""),
					Products: $('.array-of-products').text(),
					modeOfPayment: $(".payment-modes").val()
				},
				function( data ){
					$.confirm({
						title: "Okay", 
						type: 'green',
						typeAnimated: true,
						columnClass: 'col-md-8 col-md-offset-2',
						buttons: {
							DONE: {
								text: 'Done',
								action: function(){
									location.reload();
								}
							},
							OK: {
								text: 'Cancel',
								action: function(){
									
								}
							},
							PRINT: {
								text: 'PRINT',
								action: function(){
									$.print( '.prices-col' );
								}
							}
						},
						content: data,
						contentLoaded: function(data, status, xhr){
							this.setContentAppend('<br>Status: ' + status);
						}
					});
				});
			}
			//$.print('.prices-col');
		}
	});
</script>
<?php include "footer.php"; ?>