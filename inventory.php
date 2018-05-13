<?php include "header.php"; ?>
<div class="row">
	<div class="panel-group col-md-4">
		<header class="panel panel-default">
			<div class="panel-heading">Add Inventory</div>
            <div class="panel-body">
                <div class="form-group">
                    <label for="pid">Product ID</label>
                    <input type="text" class="form-control" id="pid" autocomplete="off">
					<table class="pid-resp"></table>
                </div>
				<div class="form-group">
                    <label for="supp">Supplier</label>
                    <input type="text" class="form-control" id="supp" autocomplete="off">
                </div>
                <div class="form-group">
                    <label for="inv">Inventory</label>
                    <input type="text" class="form-control" id="inv">
                </div>
				<div class="form-group">
                    <label for="total">Total</label>
                    <input type="text" class="form-control" id="total">
                </div>
				<div class="form-group">
                    <label for="paid">Paid</label>
                    <input type="text" class="form-control" id="paid">
                </div>
                <button class="btn btn-primary btn-block save-btn">Save</button>
				<div class="bc"></div>
		    </div>
		</header>
	</div>
</div>
<div class="products-arr"></div>
<script>
	$(".save-btn").one("click",function(){
		$.post("ajax-req-handler.php",
		{	
			key: "add-invetory",
			pid: $("#pid").val(),
			supplier: $("#supp").val(),
			inventory: $("#inv").val(),
			total: $("#total").val(),
			paid: $("#paid").val()
		},
		function( data ){
			$.alert(data);
		});
	});
	$("#pid").keyup(function(){
		if($(this).val() == ""){
			$(".pid-resp").html(" ");
		}
		else{
			$(".pid-resp").show();
			$.post("ajax-req-handler.php", { key: "get-product-ID-for-inventory", pidInp: $(this).val() }, function(data){ $(".pid-resp").html(data); });
			if($(this).val().indexOf('/') != -1){
				$.post("ajax-req-handler.php", { key: "get-product-ID-for-inventory", pidInp: $(this).val() }, function(data){ $(".pid-resp").html(data); });
			}
		}
	});
</script>
<?php include "footer.php"; ?>