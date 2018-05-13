<?php include "header.php"; ?>
<div class="row">  
	<div class="panel-group col-md-4">
		<header class="panel panel-default">
			<div class="panel-heading">New Entry <div class="duplicate-msg"></div></div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="pname">Product Name</label>
                    <input type="text" class="form-control" id="pname" autocomplete="off">
					<table class="name-resp"></table>
                </div>
				<div class="form-group">
                    <label for="bname">Brand Name</label>
                    <input type="text" class="form-control" id="bname" autocomplete="off">
					<table class="brand-resp"></table>
                </div>
                <div class="form-group">
                    <label for="psize">Size</label>
                    <input type="text" class="form-control" id="psize">
                </div>
                <label for="pprices">Price(&#8377;)</label>
                <div class="form-inline">
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Selling Price" id="sprice">
                    </div>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Buying Price" id="bprice">
                    </div>
                </div>
				<div class="form-group">
					<label for="ptype">Product Type</label>
					<select class="form-control" id="ptype">
						<option value="">Select One</option>
						<option value="c">Clothing</option>
						<option value="f">Footwear</option>
						<option value="o">other</option>
					</select>
				</div>
                <div class="form-group">
                    <label for="inv">Inventory</label>
                    <input type="text" class="form-control" id="inv">
                </div>
                <button class="btn btn-primary btn-block save-btn">Save</button>
				<div class="bc"></div>
		    </div>
		</header>
	</div>
	
	<div class="panel-group col-md-8">
		
		<div class="panel-group col-md-12">
			<header class="panel panel-default">
				<div class="panel-heading">Apply Filters</div>
				<div class="panel-body form-inline">
					<input type="text" class="form-control filter-width namef" placeholder="Product Name">
					<input type="text" class="form-control filter-width brandf" placeholder="Brand Name">
					<input type="text" class="form-control filter-width catf" placeholder="Category">
					<input type="text" class="form-control filter-width sizef" placeholder="Size">
					<input type="text" class="form-control filter-width pricef" placeholder="Price">
					<input type="text" class="form-control filter-width invf" placeholder="Inventory">
				</div>
			</header>
		</div>
		
		<div class="panel panel-primary">
			<div class="panel-heading"> All Products (<div style="display:inline;" class='tot-inv'></div>) </div>
			<div class="panel-body">
				<table class="product-list">
					<tr class="tab-head">
						<td>Product ID</td>
						<td>Product Name</td>
						<td>Brand Name</td>
						<td>Category</td>
						<td>Size</td>
						<td>
							<table>
								<tr>
									<td colspan=2>Price</td>
								</tr>
								<tr>
									<td style="width:50%;background-color:#000000;">Selling</td>
									<td style="width:50%;background-color:#000000;">Buying</td>
								</tr>
							</table>
						</td>
						<td>
							<table>
								<tr>
									<td colspan=2>GST</td>
								</tr>
								<tr>
									<td style="width:50%;background-color:#000000;">Selling</td>
									<td style="width:50%;background-color:#000000;">Buying</td>
								</tr>
							</table>
						</td>
						<td>Inventory</td>
					</tr>
					<script>
						$(document).ready(function(){
							$.post("ajax-req-handler.php", { key: "Load-all-products" }, function(data){ $('.product-list').html(data); });
						});
					</script>
				</table>
			</div>
		</div>
	</div>
</div>
<script>
	$("#inv").focusin(function(){
		if($("#ptype").val() == ""){
			document.getElementById("#ptype").text().style.color = "red";
		}
	});
	$("body:not(.name-resp)").click(function(){
		$(".name-resp").hide();
	})
	$("#pname").keyup(function(){
		$.post("ajax-req-handler.php", { key: "predict-product-name", val: $(this).val() }, function(data){ $(".name-resp").html(data); });
		if($(this).val() == "") $(".name-resp").hide();
		else $(".name-resp").show();
	});
	$("body:not(.brand-resp)").click(function(){
		$(".brand-resp").hide();
	})
	$("#bname").keyup(function(){
		$.post("ajax-req-handler.php", { key: "predict-brand-name", val: $(this).val() }, function(data){ $(".brand-resp").html(data); });
		if($(this).val() == "") $(".brand-resp").hide();
		else $(".brand-resp").show();
	});
	$("#pname, #bname, #psize, #sprice, #bprice, #ptype").on('keyup change',function(){
		$.post("ajax-req-handler.php",
        {
            key: "check-existing-entries",
			pname: $('#pname').val(),
			bname:  $('#bname').val(),
            psize: $('#psize').val(),
            sprice: $('#sprice').val(),
            bprice: $('#bprice').val()
        },
        function( resp ){
			$(".duplicate-msg").html(resp);
		});
	});
	$(".single-product-row").click(function(){
		var id = $(this).attr('id');
		$.post("fetch-product.php",
		{
			key: "fetch-products-details",
			sendPid: id
		},
		function( resp ){
			$.confirm({
				title: "Product Details", 
				type: 'green',
				typeAnimated: true,
				columnClass: 'col-md-12 col-md-offset-0',
				buttons: {
					close: function () {text: 'Close'}
				},
				content: resp,
				contentLoaded: function(data, status, xhr){
					// data is already set in content
					this.setContentAppend('<br>Status: ' + status);
				}
			});
		});
	});
    $('.save-btn').click(function(){
		$.post("ajax-req-handler.php",
        {
            key: "insert-product-details",
			pname: $('#pname').val(),
			bname:  $('#bname').val(),
            psize: $('#psize').val(),
            sprice: $('#sprice').val(),
            bprice: $('#bprice').val(),
            ptype: $('#ptype').val(),
            inventory: $('#inv').val()
        },
        function( resp ){
			$.confirm({
				title: "Okay", 
				type: 'green',
				typeAnimated: true,
				columnClass: 'col-md-8 col-md-offset-2',
				buttons: {
					OK: {
						text: 'OK',
						action: function(){
							location.reload();
						}
					},
					PRINT: {
						text: 'PRINT',
						action: function(){
							$.print( resp );
						}
					},
					CANCEL: {
						text: 'CANCEL',
						action: function(){
							
						}
					}
				},
				content: resp,
				contentLoaded: function(data, status, xhr){
					this.setContentAppend('<br>Status: ' + status);
				}
			});
		});
	});
	$(".namef, .brandf, .catf, .sizef, .pricef, .invf").keyup(function(){
		if($(".namef").val() == "" && $(".brandf").val() == "" && $(".catf").val() == "" && $(".sizef").val() == "" && $(".pricef").val() == "" && $(".invf").val() == ""){
			$.post("ajax-req-handler.php", { key: "Load-all-products" }, function(data){ $('.product-list').html(data); });
		}
		else{
			$.post("ajax-req-handler.php", {
				key: "apply-filters",
				name: $(".namef").val(),
				brand: $(".brandf").val(),
				cat: $(".catf").val(),
				size: $(".sizef").val(),
				price: $(".pricef").val(),
				inv: $(".invf").val()
			}, function(data){
				$('.product-list').html(data);
			});
		}
	});
</script>
<?php include "footer.php"; ?>