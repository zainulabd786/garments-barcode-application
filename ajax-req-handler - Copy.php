<?php
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__).'/log.txt');
	error_reporting(E_ALL);
    //include "database_connection.php";
    include "php-barcode-generator-master/src/BarcodeGenerator.php";
    include "php-barcode-generator-master/src/BarcodeGeneratorPNG.php";
    class database extends SQLite3{
    	function __construct(){
			$db_file = "data/safg.sqlite3";
    		$this->open($db_file);
    	}
    }
    $conn = new database();
    function num_rows( $result ){
    	$nrows = 0;
		$result->reset();
		while ($result->fetchArray())
		    $nrows++;
		$result->reset();
		return $nrows;
    }
	function is_connected(){
		$connected = @fsockopen("www.example.com", 80); 
											//website, port  (try 80 or 443)
		if ($connected){
			$is_conn = true; //action when connected
			fclose($connected);
		}else{
			$is_conn = false; //action in connection failure
		}
		return $is_conn;
	}
    $key = $_REQUEST['key'];
    switch($key){
        case "insert-product-details" :
            $product_name = ucfirst($_REQUEST['pname']);
			$brand_name = $_REQUEST['bname'];
            $product_size = $_REQUEST['psize'];
            $product_sprice = $_REQUEST['sprice'];
			$product_bprice = $_REQUEST['bprice'];
			$product_type = $_REQUEST['ptype'];
            $inv = $_REQUEST['inventory'];
            if(!empty($product_name) && !empty($product_size) && !empty($product_sprice) && !empty($product_bprice && !empty($product_type))){
				if(strstr($product_size, '/')){
					$size_array = explode('/', $product_size);
					$size_array2 = explode('/', $product_size);
					$inv_array = explode('/', $inv);
					if($product_type == "f"){
						for($i=0; $i<count($size_array); $i++){
							$matching_sql = "SELECT * FROM products WHERE ptype='f' && pname='$product_name' && brand_name='$brand_name' && psize='$size_array[$i]' && sprice='$product_sprice' && bprice='$product_bprice' LIMIT 1";
							$result = $conn->query($matching_sql);
							if(num_rows($result)>0){ 
								while($row=$result->fetch_assoc()){
									$mpid = $row['pid'];
									$mname = $row['pname'];
									$mbrand = $row['brand_name'];
									$msize = $row['psize'];
									$msprice = $row['sprice'];
									$mbprice = $row['bprice'];
									//echo $mpid."--".$size_array[$i].">".$inv_array[$i]."<br/>";
									$update_existing_sql = "UPDATE products SET inventory=inventory+$inv_array[$i] WHERE pid='$mpid' && psize='$size_array[$i]'";
									if($conn->query($update_existing_sql)){ ?>
										<div class="bar-label"> <?php
											$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
											echo '<strong>Product Name: </strong>'.$mname.' <br/> 
											<strong>Brand Name: </strong>'.$mbrand.'<br/> 
											<strong>Size: </strong>'.$size_array[$i].' <br/> 
											<strong>Price: </strong> &#8377;'.$msprice.' /-<br/>
											<center><img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($mpid, $generator::TYPE_CODE_128)) . '">
											<br/>'.$mpid.'</center>'; ?>
										</div> <?php
									}
									else{
										?> <div class='alert alert-danger'>ERROR!<?php echo $con->error; ?></div> <?php
									}
									if($size_array[$i] == $msize) unset($size_array2[$i]);
								}
							}
							else{
								array_values($size_array2);
								$pid = "";
								$snAbr = explode(" ",$product_name);
								foreach($snAbr as $val){
									$pid .= substr($val,0,1);
								}
								$pid .= date("dmysi").$size_array[$i];
								//echo "new--".$size_array2[$i].">".$inv_array[$i]."<br/>";
								($product_sprice <= 525) ? $sgst = 5 : $sgst = 18;
								($product_bprice <= 525) ? $bgst = 5 : $bgst = 18;
								$insert_rec_sql = "INSERT INTO products (pid, pname, brand_name, ptype, psize, sprice, bprice, sgst, bgst, inventory)
								VALUES('$pid','$product_name','$brand_name','f','$size_array2[$i]','$product_sprice','$product_bprice','$sgst','$bgst','$inv_array[$i]')";
								if($conn->query($insert_rec_sql)){ ?>
									<div class="bar-label"> <?php
										$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
										echo '<strong>Product Name: </strong>'.$product_name.' <br/> 
										<strong>Brand Name: </strong>'.$brand_name.'<br/> 
										<strong>Size: </strong>'.$size_array2[$i].' <br/> 
										<strong>Price: </strong> &#8377;'.$product_sprice.' /-<br/>
										<center><img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($pid, $generator::TYPE_CODE_128)) . '">
										<br/>'.$pid.'</center>'; ?>
									</div> <?php
								}
								else{
									?> <div class='alert alert-danger'>ERROR!<?php echo $con->error; ?></div> <?php
								}
							}
						}
					}
					else{
						if(count($size_array) == count($inv_array)){
							for($i=0; $i<count($size_array); $i++){
								$pid = "";
								$snAbr = explode(" ",$product_name);
								foreach($snAbr as $val){
									$pid .= substr($val,0,1);
								}
								$pid .= date("dmysi").$size_array[$i];
								switch( $product_type ){
									case "c" :
										( $product_sprice > 1050) ? $product_sgst = 12 : $product_sgst = 5;
										( $product_bprice > 1050) ? $product_bgst = 12 : $product_bgst = 5;
									break;
									
									case "f" :
										( $product_sprice > 525) ? $product_sgst = 18 : $product_sgst = 5;
										( $product_bprice > 525) ? $product_bgst = 18 : $product_bgst = 5;
									break;
									
									case "o":
										$product_sgst = 18;
										$product_bgst = 18;
									break;
								}
								$insert_details_sql = "INSERT INTO products (pid, pname, brand_name, ptype, psize, sprice, bprice, sgst, bgst, inventory) 
								VALUES('$pid','$product_name','$brand_name','$product_type','$size_array[$i]','$product_sprice','$product_bprice','$product_sgst','$product_bgst','$inv_array[$i]')";
								if($conn->query($insert_details_sql)==TRUE){ ?>
									<div class="bar-label"> <?php
										$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
										echo '<strong>Product Name: </strong>'.$product_name.' <br/> 
										<strong>Brand Name: </strong>'.$brand_name.'<br/> 
										<strong>Size: </strong>'.$size_array[$i].' <br/> 
										<strong>Price: </strong> &#8377;'.$product_sprice.' /-<br/>
										<center><img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($pid, $generator::TYPE_CODE_128)) . '">
										<br/>'.$pid.'</center>'; ?>
									</div> <?php
								}
								else{
									echo "<div class='alert alert-danger><strong>ERROR!</strong><br/>Product annot be deleted<br/>".$conn->error."</div>'";
								}
							}
						}
						else{
							echo "<div class='alert alert-danger'>Number of Elements in 'Size' and 'Inventory' Field should be equal</div>";
						}
					}
				}
				else{
					$pid = "";
					$snAbr = explode(" ",$product_name);
					foreach($snAbr as $val){
						$pid .= substr($val,0,1);
					}
					$pid .= date("dmysi").$product_size;
					switch( $product_type ){
						case "c" :
							( $product_sprice > 1050) ? $product_sgst = 12 : $product_sgst = 5;
							( $product_bprice > 1050) ? $product_bgst = 12 : $product_bgst = 5;
						break;
						
						case "f" :
							( $product_sprice > 525) ? $product_sgst = 18 : $product_sgst = 5;
							( $product_bprice > 525) ? $product_bgst = 18 : $product_bgst = 5;
						break;
						
						case "o":
							$product_sgst = 18;
							$product_bgst = 18;
						break;
					}
					
					$insert_details_sql = "INSERT INTO products (pid, pname, brand_name, ptype, psize, sprice, bprice, sgst, bgst, inventory) 
					VALUES('$pid','$product_name','$brand_name','$product_type','$product_size','$product_sprice','$product_bprice','$product_sgst','$product_bgst','$inv')";
					if($conn->query($insert_details_sql)==TRUE){ ?>
						<div class="bar-label"> <?php
							$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
							echo '<strong>Product Name: </strong>'.$product_name.' <br/> 
							<strong>Brand Name: </strong>'.$brand_name.'<br/> 
							<strong>Size: </strong>'.$product_size.' <br/> 
							<strong>Price: </strong> &#8377;'.$product_sprice.' /-<br/>
							<center><img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($pid, $generator::TYPE_CODE_128)) . '">
							<br/>'.$pid.'</center>'; ?>
						</div> <?php
					}
					else{
						echo "<div class='alert alert-danger><strong>ERROR!</strong><br/>Product annot be deleted<br/>".$conn->error."</div>'";
					}
				}
			}
			else{ ?>  <div class="alert alert-danger">All Fields are Mandatory</div> <?php }
        break;

        case "live-rec-search" :
			$search_inp = $_REQUEST['search'];
			$fill_id = 0;
			$sql = "SELECT pid, pname FROM products WHERE (pid LIKE '%$search_inp%' || pname LIKE '%$search_inp%')";
			$result = $conn->query($sql);
			if($result->num_rows>0){ 
				while($row=$result->fetch_assoc()){
					$name = $row['pname'];
					$id = $row['pid']; 
					if(!empty($search_inp)){ ?>
						<div class="row live-search-box"  id="fill-cid-<?php echo $fill_id ?>">
							<div class="col-sm-4"><?php echo $name; ?></div>
							<div style="text-align: right;" class="col-sm-4"><?php echo $id; ?></div>
						</div> <?php
					}
					$fill_id++;
				}
			}
			?>
			<script>
				$(".live-search-box").click(function(){
					var hash = "#";
					var fillId = $(this).attr('id');
					var makefillId = hash + fillId;
					var dispFillId = $(makefillId).text();
					var rmvbrk = $.trim(dispFillId.replace(/[\t\n]+/g,' '));
					var pid = rmvbrk.split(" ").pop();
					$.post("fetch-product.php",
					{
						key: "fetch-products-details",
						sendPid: pid
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
			</script>
			<?php
		break;
		
		case "search-customer-record":
			$search_inp = $_REQUEST['search'];
			$fill_id = 0;
			$sql = "SELECT * FROM customers WHERE (mobile LIKE '%$search_inp%' || name LIKE '%$search_inp%')";
			$result = $conn->query($sql);
			if($result->num_rows>0){ 
				while($row=$result->fetch_assoc()){
					$name = $row['name'];
					$cid = $row['mobile']; 
					if(!empty($search_inp)){ ?>
						<div class="row live-search-box"  id="fill-cid-<?php echo $fill_id ?>">
							<div class="col-sm-4"><?php echo $name; ?></div>
							<div style="text-align: right;" class="col-sm-4"><?php echo $cid; ?></div>
						</div> <?php
					}
					$fill_id++;
				}
			}
			?>
			<script>
				$(".live-search-box").click(function(){
					var hash = "#";
					var fillId = $(this).attr('id');
					var makefillId = hash + fillId;
					var dispFillId = $(makefillId).text();
					var rmvbrk = $.trim(dispFillId.replace(/[\t\n]+/g,' '));
					var cid = rmvbrk.split(" ").pop();
					$.post("ajax-req-handler.php",
					{
						key: "fetch-customer-details",
						sendCid: cid
					},
					function( resp ){
						$.confirm({
							title: "Customer Info", 
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
			</script>
			<?php
		break;
		
		case "fetch-customer-details":
			$id = $_REQUEST['sendCid']; ?>
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#products">Products</a></li>
				<li><a data-toggle="tab" href="#payments">Payments</a></li>
				<li><a data-toggle="tab" href="#bills">Bills</a></li>
			</ul>
			<div class="disp-due" style="float:right"></div> <?php
			 $sql_cname = "SELECT name FROM customers WHERE mobile='$id'";
			 $result_cname = $conn->query($sql_cname);
			 if($result_cname->num_rows>0){
				 $row = $result_cname->fetch_assoc();
				 $name = $row['name'];
			 }
			 ?>
			<div class="name alert alert-info" style="float:right"><?php echo $name; ?></div>
			<div class="mob alert alert-info" style="float:right"><?php echo $id; ?></div>
			<script>
				$.post("ajax-req-handler.php", { key: "find-dues-if-any", value: '<?php echo $id; ?>'}, function(data){ $(".disp-due").html(data); })
			</script>
		  <div class="tab-content">
				<div id="products" class="tab-pane fade in active">
					<h3>Produts</h3>
					<table style="width:100%">
						<tr class="tab-head">
							<td>Date & Time</td>
							<td>Bill number</td>
							<td>Products</td>
							<td>Brand</td>
							<td>Size</td>
							<td>Quantity</td>
							<td>discount</td>
							<td>Remarks</td>
							<td>MRP</td>
							<td>Amount</td>
						</tr> <?php
						$sql_prod = "SELECT a.*, b.pname, b.brand_name, b.psize, b.sprice FROM sales a, products b WHERE a.customer_id='$id' AND b.pid=a.products ORDER BY a.sale_date DESC"; 
						$result_prod = $conn->query($sql_prod);	
						if($result_prod->num_rows>0){ 
							while($row_prod=$result_prod->fetch_assoc()){
								$date = date("d/m/Y H:i:s", strtotime($row_prod['sale_date']));
								$bill = $row_prod['bill_id'];
								$products = $row_prod['pname'];
								$brand = $row_prod['brand_name'];
								$size = $row_prod['psize'];
								$quantity = $row_prod['quantity'];
								$discount = $row_prod['discount'];
								$remarks = $row_prod['status'];
								$mrp = $row_prod['sprice'];
								$amount = $row_prod['amount']; 
								( $remarks == 'sr' ) ? $color="style='color:#a94442'" : $color="style='color:#3c763d'";
								( $remarks == 'sr' ) ? $remarks="Sale Return" : $remarks="Sale";	?>
								<tr <?php echo $color; ?>>
									<td><?php echo $date; ?></td>
									<td><?php echo $bill; ?></td>
									<td><?php echo $products; ?></td>
									<td><?php echo $brand; ?></td>
									<td><?php echo $size; ?></td>
									<td><?php echo $quantity; ?></td>
									<td><?php echo $discount; ?> %</td>
									<td><?php echo $remarks; ?></td>
									<td>&#8377 <?php echo number_format($mrp); ?>/-</td>
									<td>&#8377 <?php echo number_format($amount); ?> /-</td>
								</tr> <?php
							}	
						}
						else echo "<tr style='padding:15px;font-size:20px'><td colspan=10 style='text-align:center' class='alert alert-danger'>Oops! Nothing To Show.</td></tr>"; ?>
					</table>
				</div>
				<div id="payments" class="tab-pane fade">
					<h3>Payments</h3>
					<table style="width:100%">
						<tr class="tab-head">
							<td>Transaction Id</td>
							<td>Date & Time</td>
							<td>Bill number</td>
							<td>Remarks</td>
							<td>Type</td>
							<td>Amount</td>
							<td>Payment Mode</td>
						</tr> <?php
						$sql_pay = "SELECT *, 'cash_transactions' AS source FROM `cash_transactions` WHERE customer_id='$id' UNION SELECT *, 'bank_transactions' AS source FROM bank_transactions WHERE bcustomer_id='$id' ORDER BY transaction_date DESC"; 
						$result_pay = $conn->query($sql_pay);	
						if($result_pay->num_rows>0){ 
							while($row_pay=$result_pay->fetch_assoc()){
								$date = date("d/m/Y H:i:s", strtotime($row_pay['transaction_date']));
								$tid = $row_pay['transaction_id'];
								$bill = $row_pay['bill_id'];
								$remarks = $row_pay['remarks'];
								$type = $row_pay['type'];
								$amount = $row_pay['amount'];
								$mop = $row_pay['source'];
								( $type != 'in' ) ? $col="style='color:#a94442'" : $col="style='color:#3c763d'";
								( $mop == 'cash_transactions' ) ? $mop="Cash" : $mop="Bank"; ?>
								<tr <?php echo $col; ?>>
									<td><?php echo $tid; ?></td>
									<td><?php echo $date; ?></td>
									<td><?php echo $bill; ?></td>
									<td><?php echo $remarks; ?></td>
									<td><?php echo $type; ?></td>
									<td>&#8377 <?php echo number_format($amount); ?> /-</td>
									<td><?php echo $mop; ?></td>
								</tr> <?php
							}	
						}
						else echo "<tr style='padding:15px;font-size:20px'><td colspan=10 style='text-align:center' class='alert alert-danger'>Oops! Nothing To Show.</td></tr>"; ?>
					</table>
				</div>
				<div id="bills" class="tab-pane fade">
					<h3>Bills</h3>
					<table style="width:100%">
						<tr class="tab-head">
							<td>Date & Time</td>
							<td>Bill number</td>
							<td>Total</td>
							<td>Paid</td>
							<td>Due</td>
						</tr> <?php
						$sql = "SELECT * FROM `bill_info` WHERE customer_id='$id' ORDER BY bill_date DESC"; 
						$result = $conn->query($sql);	
						if($result->num_rows>0){ 
							while($row=$result->fetch_assoc()){
								$date = date("d/m/Y H:i:s", strtotime($row['bill_date']));
								$bill = $row['bill_id'];
								$paid = $row['paid'];
								$total = $row['total'];
								$due = $total - $paid;
								( $paid != $total ) ? $col="style='color:#a94442'" : $col="style='color:#3c763d'"; ?>
								<tr id='<?php echo $bill; ?>' class="bill-single-row" <?php echo $col; ?>>
									<td><?php echo $date; ?></td>
									<td><?php echo $bill; ?></td>
									<td>&#8377; <?php echo number_format($total); ?> /-</td>
									<td>&#8377; <?php echo number_format($paid); ?> /-</td>
									<td>&#8377; <?php echo number_format($due); ?> /-</td>
								</tr> <?php
							}	
						} 
						else echo "<tr style='padding:15px;font-size:20px'><td colspan=10 style='text-align:center' class='alert alert-danger'>Oops! Nothing To Show.</td></tr>"; ?>
						<script>
							$(".bill-single-row").click(function(){
								var billNum = $(this).attr('id');
								$.post("ajax-req-handler.php", {
									key: "view-bill-details-for-modifications",
									billNumber: billNum
								}, function(resp){
									$.confirm({
										title: "Update Bill", 
										type: 'green',
										typeAnimated: true,
										columnClass: 'col-md-6 col-md-offset-3',
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
						</script>
					</table>
				</div>
		  </div><?php
		break;

		case "update-product-details":
			$product_id = $_REQUEST['pid'];
			$product_name = ucfirst($_REQUEST['pname']);
			$product_size = $_REQUEST['psize'];
			$product_sprice = $_REQUEST['sprice'];
			$product_bprice = $_REQUEST['bprice'];
			$product_sgst = $_REQUEST['sgst'];
			$product_bgst = $_REQUEST['bgst'];
			$brand_name = $_REQUEST['brand'];
			$inv = $_REQUEST['inventory'];
			$product_type = $_REQUEST['ptype'];
			switch( $product_type ){
				case "c" :
					( $product_sprice > 1050) ? $product_sgst = 12 : $product_sgst = 5;
					( $product_bprice > 1050) ? $product_bgst = 12 : $product_bgst = 5;
				break;
				
				case "f" :
					( $product_sprice > 525) ? $product_sgst = 18 : $product_sgst = 5;
					( $product_bprice > 525) ? $product_bgst = 18 : $product_bgst = 5;
				break;
						
				case "o":
					$product_sgst = 18;
					$product_bgst = 18;
				break;
			}
			if(!empty($product_id) && !empty($product_name) && !empty($product_size) && !empty($product_sprice) && !empty($product_bprice) && !empty($inv)){
				$update_sql = "UPDATE products SET pname = '$product_name', brand_name = '$brand_name', ptype = '$product_type',  psize = '$product_size', sprice = '$product_sprice', bprice = '$product_bprice', sgst = '$product_sgst', bgst = '$product_bgst', inventory = '$inv' WHERE pid = '$product_id'";
				if($conn->query($update_sql)==TRUE){
					echo "<div class='alert alert-success'>Record Successfully Updated</div>";
				}
			}
			else{
				echo $product_id."  ".$product_name."  ".$product_size."  ".$product_sprice."  ".$product_bprice."  ".$product_sgst."  ".$product_bgst."<div class='alert alert-danger'>All Fields Are Mandatory</div>";
			}
		break;

		case "reprint-barcode":
			$pid = $_REQUEST['pid']; 
			$product_name = $_REQUEST['pname']; 
			$brand_name = $_REQUEST['brand']; 
			$size = $_REQUEST['psize']; 
			$product_sprice = $_REQUEST['sprice'];  ?>
			<div class="bar-label"> <?php
				$generator = new \Picqer\Barcode\BarcodeGeneratorPNG();
				echo '<strong>Product Name: </strong>'.$product_name.' <br/> 
				<strong>Brand Name: </strong>'.$brand_name.'<br/> 
				<strong>Size: </strong>'.$size.' <br/> 
				<strong>Price: </strong> &#8377;'.$product_sprice.' /-<br/>
				<center><img class="barcode-img" src="data:image/png;base64,' . base64_encode($generator->getBarcode($pid, $generator::TYPE_CODE_128)) . '">
				<br/>'.$pid.'</center>'; ?>
			</div> <?php
		break;

		case "barcode-input":
			$barcode = $_REQUEST['barcodeVal'];
			$search_barcode_sql = "SELECT * FROM products WHERE pid = '$barcode'";
			$search_barcode_result = $conn->query($search_barcode_sql);
            if($search_barcode_result->num_rows>0){ 
                while($search_barcode_row=$search_barcode_result->fetch_assoc()){ 
					$pid = $search_barcode_row['pid'];
					$name = $search_barcode_row['pname'];
					$size = $search_barcode_row['psize'];
					$sprice = $search_barcode_row['sprice'];
					$bprice = $search_barcode_row['bprice'];
					$sgst = $search_barcode_row['sgst'];
					$sgst_amt = $sprice * $sgst / 100;
					$bgst = $search_barcode_row['bgst'];
					$type = $search_barcode_row['ptype'];
					$tot_price = $sprice;
					$tax_amt = ($tot_price) - (($sprice)/((1)+(($sgst)/(100)))) ;
					$inventory = $search_barcode_row['inventory']; ?>
					<div class="single-product <?php echo $pid; ?>">
						<button type="button" class="btn btn-danger single-prod-del"><i class="fa fa-trash-o" aria-hidden="true"></i></button>
						<i class="fa fa-shopping-bag fa-5x" style="width:100%" aria-hidden="true"></i>
						<div class="single-product-details">
							<?php echo $name; ?>
							<?php echo "- ".$size; ?>
							<?php echo "<br/>&#8377;".$tot_price."/-"; ?>
							<input type="hidden" class="<?php echo $sprice; ?>" id="<?php echo $sgst; ?>"><?php //echo "(".$sprice." + ".$sgst."%GST)"; ?>
							<table>
								<tr>
									<td><input type="text" value="1" class="form-control quantity col-xs-6" id="<?php echo $pid; ?>"></td>
									<td><input type="text" value="0" class="form-control discount col-xs-6" id="<?php echo $pid; ?>"></td>
								</tr>
								<tr>
									<td> Quantity </td>
									<td> Discount </td>
								</tr>
								<tr>
									<td colspan="2"><input type="text" value="<?php echo $tot_price; ?>" class="form-control adjustable-price" id="<?php echo $pid; ?>"></td>
								</tr>
							</table>
						</div>
					</div>
					<script>
						function deleteIfMatches(array, match){
							 array.some((ele)=>{ ele.indexOf(match) >= 0 ? array.splice(array.indexOf(ele),1): null });
							 return array;
						}
						$('.product-searchbar').val("");
						$(".single-prod-del:not(.clickadded)").one("click",function(event){
							event.stopPropagation();
							var pid = $(this).parents(".single-product").find(".single-product-details").find("table").find("tr:even").find("td input:even").attr('id');
							$(".tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg, .sgst, .cgst, .eden-add").remove();
							$(".bill-area").find("."+pid).remove();
							$(".checkout-product-list").find(".single-product").find(".single-product-details").find("table").find("tr:even").find("td").find("#"+pid).parents(".single-product").remove();
							//var prodArray = $(".array-of-products").text().split("-");
							deleteIfMatches(arrayOfProducts, pid);
							$.post("ajax-req-handler.php", {key: "implode-array", array: arrayOfProducts}, function(data){ $(".array-of-products").html(data) });
							
						}).addClass("clickadded");
						$(".single-product").ready(function(){
							var qtity = 1;
							var disc = 0;
							var extPrice = <?php echo $tot_price; ?>;
							var getPid = '<?php echo $pid; ?>';
							var getPname = '<?php echo $name; ?>';
							var getsprice = <?php echo $sprice; ?>;
							var mrp = <?php echo $sprice; ?>;
							var getsgst = <?php echo $tax_amt; ?>;
							var sgstPerc = <?php echo $sgst; ?>;
							var getPtype = '<?php echo $type; ?>';
							var qmp = extPrice * qtity;
							var cp = qmp * disc/100; 
							var tot_amt = qmp - cp;
							arrayOfProducts.push(getPid+"/"+qtity+"/"+disc+"/"+tot_amt);
							var  productsBought = arrayOfProducts;
							$(".quantity").off().change(function(){
								$(".tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg, .sgst, .cgst, .eden-add").remove();
								var qtity = $(this).val();
								var getPid = $(this).attr("id");
								extPrice = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								mrp = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								getPname = $(this).parents(".single-product-details").text().substring(0, $(this).parents(".single-product-details").text().indexOf('-'));
								getsprice = $(this).parents(".single-product-details").find("input[type=hidden]").attr('class');
								getsgst = $(this).parents(".single-product-details").find("input[type=hidden]").attr('id');
								var tot_amt = extPrice * qtity;
								var taxAmt = (tot_amt) - ( (tot_amt) / ((1) + ( getsgst / 100 )));
								$(this).parents("tr").find(".discount").val("0");
								var array = arrayOfProducts;
								var match = getPid;
								deleteIfMatches(array, match);
								array.push(getPid+"/"+qtity+"/"+disc+"/"+tot_amt);
								var productsBought = array;
								$.post("ajax-req-handler.php",
								{
									key: "cart-total-price",
									key2: "Array-of-products",
									extractedPrice: tot_amt,
									productsInCart: productsBought
								},
								function( data ){
									$('.prices-col table').find("."+getPid).html("<td class='to-remove'>"+getPname+"<br/>x"+qtity+"</td><td class='to-remove'>"+disc+"</td><td class='to-remove'>("+sgstPerc+"%)<br/><div class='tax-amt-box'>&#8377;"+taxAmt.toFixed()+"</div></td><td class='to-remove'>"+mrp+"</td><td> "+data+"</td>");
								});
							});
							$(".discount").on("change", function(){
								$(".tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg, .sgst, .cgst, .eden-add").remove();
								var qtity = $(this).parents("tr").find(".quantity").val();
								var disc = $(this).val();
								var getPid = $(this).attr("id");
								extPrice = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								mrp = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								getPname = $(this).parents(".single-product-details").text().substring(0, $(this).parents(".single-product-details").text().indexOf('-'));
								getsprice = $(this).parents(".single-product-details").find("input[type=hidden]").attr('class');
								getsgst = $(this).parents(".single-product-details").find("input[type=hidden]").attr('id');
								var qmp = extPrice * qtity;
								var cp = qmp * disc/100;
								var tot_amt = qmp - cp;
								switch(getPtype){
									case "c":
										if( tot_amt/qtity < 1050 ){
											sgstPerc = 5;
										}
										else{
											sgstPerc = 12;
										}
									break;
									
									case "f":
										if( tot_amt/qtity < 525 ){
											sgstPerc = 5;
										}
										else{
											sgstPerc = 18;
										}
									break;
								}
								var taxAmt = (tot_amt) - ( (tot_amt) / ((1) + ( sgstPerc / 100 )));
								var array = arrayOfProducts;
								var match = getPid;
								deleteIfMatches(array, match);
								array.push(getPid+"/"+qtity+"/"+disc+"/"+tot_amt);
								var productsBought = array;
								var getTax = $(this).parents(".single-product-details").find(".add-tax").text();
								$.post("ajax-req-handler.php",
								{
									key: "cart-total-price",
									key2: "Array-of-products",
									quantity: qtity,
									pid: getPid,
									extractedPrice: tot_amt,
									productsInCart: productsBought
								},
								function( data ){
									$('.prices-col').find("."+getPid).html("<td class='to-remove'>"+getPname+"<br/>x"+qtity+"</td><td class='to-remove'>"+disc+"</td><td class='to-remove'>("+sgstPerc+"%)<br/><div class='tax-amt-box'>&#8377;"+taxAmt.toFixed()+"</div></td><td class='to-remove'>"+mrp+"</td><td>"+data+"</td>");
								});
							});

							$(".adjustable-price").change(function(){
								$(".tot-payable-amt, .tot-paid-amt, .due-amt, .thanks-msg, .sgst, .cgst, .eden-add").remove();
								var qtity = $(this).parents("table").find("tr .quantity").val();
								var disc = $(this).parents("table").find("tr .discount").val();
								var getPid = $(this).attr("id");
								//extPrice = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								extPrice = $(this).val();
								mrp = $(this).parents(".single-product-details").text().replace(/[^0-9.]/g, ' ').replace(/\s+/g,' ').trim().split(" ").pop();
								getPname = $(this).parents(".single-product-details").text().substring(0, $(this).parents(".single-product-details").text().indexOf('-'));
								getsprice = $(this).parents(".single-product-details").find("input[type=hidden]").attr('class');
								getsgst = $(this).parents(".single-product-details").find("input[type=hidden]").attr('id');
								var tot_amt = extPrice * qtity;
								switch(getPtype){
									case "c":
										if( tot_amt/qtity < 1050 ){
											sgstPerc = 5;
										}
										else{
											sgstPerc = 12;
										}
									break;
									
									case "f":
										if( tot_amt/qtity < 525 ){
											sgstPerc = 5;
										}
										else{
											sgstPerc = 18;
										}
									break;
								}
								var taxAmt = (tot_amt) - ( (tot_amt) / ((1) + ( sgstPerc / 100 )));
								var array = arrayOfProducts;
								var match = getPid;
								deleteIfMatches(array, match);
								array.push(getPid+"/"+qtity+"/"+disc+"/"+tot_amt);
								var productsBought = array;
								var getTax = $(this).parents(".single-product-details").find(".add-tax").text();
								$(this).parents("table").find("tr .discount").val("0");
								disc = 0;
								$.post("ajax-req-handler.php",
								{
									key: "cart-total-price",
									key2: "Array-of-products",
									quantity: qtity,
									pid: getPid,
									extractedPrice: tot_amt,
									productsInCart: productsBought
								},
								function( data ){
									$('.prices-col').find("."+getPid).html("<td class='to-remove'>"+getPname+"<br/>x"+qtity+"</td><td class='to-remove'>"+disc+"</td><td class='to-remove'>("+sgstPerc+"%)<br/><div class='tax-amt-box'>&#8377;"+taxAmt.toFixed()+"</div></td><td class='to-remove'>"+mrp+"</td><td>"+data+"</td>");
								});
							});

							$.post("ajax-req-handler.php",
							{
								key: "cart-total-price",
								key2: "Array-of-products",
								extractedPrice: tot_amt,
								productsInCart: productsBought
							},
							function( data ){
								//$('.prices-col').append("<table style='width:100%' class='"+getPid+"' ><tr><td class='to-remove' style='float:left'>"+getPname+" ("+getsprice+"+"+getsgst+"%) "+"</td><td style='float:right'>&#8377; "+data+" /-</td></tr></table>");
								$('.prices-col table').append("<tr style='width:100%;' class='"+getPid+"' ><td class='to-remove'>"+getPname+"<br/>x1</td><td class='to-remove'>"+disc+"</td><td class='to-remove'>("+sgstPerc+"%)<br/><div class='tax-amt-box'>&#8377;"+getsgst.toFixed()+"</div></td><td class='to-remove'>"+mrp+"</td><td>"+data+"</td></tr>");
							});
						
						});
						$('.single-product-details').on("keyup",'.quantity',function(){
							var charTest = /[A-Za-z]/g;
							var specCharTest = /^[A-Za-z0-9]*$/g;
							if(charTest.test($(this).val()) || specCharTest.test($(this).val()) == false) $.alert("<div class='alert alert-danger'>Characters are not allowed here</div>");
						});
						$('.single-product-details').on("keyup",'.discount',function(){
							var charTest = /[A-Za-z]/g;
							var specCharTest = /^[A-Za-z0-9]*$/g;
							if(charTest.test($(this).val()) || specCharTest.test($(this).val()) == false) $.alert("<div class='alert alert-danger'>Characters are not allowed here</div>");
						});
					</script> <?php
				}
			} 
		break;
		
		case "cart-total-price":
			$extractedPrice = $_REQUEST['extractedPrice'];
			echo $extractedPrice;
			$key2 = $_REQUEST['key2'];
			$productsBought = implode("-", $_REQUEST['productsInCart']);
			if($key2 == "Array-of-products"){ ?>
				<script class="scr">
					$('.array-of-products').html('<?php echo $productsBought; ?>')
				</script> <?php
			}
			else{ ?>
				<div class="alert alert-danger">Invalid Key2</div><?php
			}
			
		break;
		
		case "deduct-quantity-from-inventory":
			$qty = $_REQUEST['quantity'];
			$product_id = $_REQUEST['pid'];
			echo $product_id."  ".$qty;
			$update_inv_sql = "UPDATE products SET inventory = (inventory - $qty) WHERE pid = '$product_id'";
			if($conn->query($update_inv_sql)){
				echo "Inventory Updated";
			}

		break;
		
		case "insert-sale-details-into-database":
			$products = $_REQUEST['Products'];
			$cname = $_REQUEST['cname'];
			$total = $_REQUEST['totalAmt'];
			$paid = $_REQUEST['paidAmt'];
			$bill_id = $_REQUEST['billId'];
			$customer_id = $_REQUEST['mobile'];
			$curr_date_time = date("Y-m-d H:i:s");
			$tid = "";
			$snAbr = explode(" ",$cname);
			foreach($snAbr as $val){
				$tid .= substr($val,0,1);
			}
			$tid .= date("dmyis");
			$prod_arr = explode("-", $products);
			$mop = $_REQUEST['modeOfPayment'];
			if(!empty($cname) && !empty($total) && !empty($customer_id)){
				for($i=0;$i<count($prod_arr);$i++){
					$prod_obj = $prod_arr[$i];
					$obj_to_arr = explode("/", $prod_obj);
					for($j=0;$j<1;$j++){
						$sql_sales = "INSERT INTO sales (sale_date, bill_id, customer_id, products, quantity, discount, amount, status)
						VALUES('$curr_date_time','$bill_id','$customer_id','$obj_to_arr[0]','$obj_to_arr[1]', '$obj_to_arr[2]', '$obj_to_arr[3]', 'sale');";
						if($conn->query($sql_sales) == FALSE){
							 echo "Error: " . $sql . "<br>" . $conn->error;
						}
					}
				}
				$validate_cust_sql = 'SELECT mobile FROM customers WHERE mobile = "$customer_id"';
				$validate_cust_result = $conn->query($validate_cust_sql);
				if($validate_cust_result->num_rows == 0){
					$sql_customer = "INSERT INTO customers (name, mobile) VALUES('$cname','$customer_id')";
					$conn->query($sql_customer);
				}
				else{
					echo "<script> alert('Error: " . $validate_cust_sql . "<br/>" . $conn->error ."'); </script>";
				}
				$sql_cbal = "SELECT balance FROM cash_transactions ORDER BY transaction_date DESC LIMIT 1";
				$result_cbal = $conn->query($sql_cbal);
				if($result_cbal->num_rows>0){ 
					while($row_cbal=$result_cbal->fetch_assoc()){
						$cbal = $row_cbal['balance'];
					}
				}
				else{
					$cbal = 0;
				}
				$sql_bbal = "SELECT bbalance FROM bank_transactions ORDER BY btransaction_date DESC LIMIT 1";
				$result_bbal = $conn->query($sql_bbal);
				if($result_bbal->num_rows>0){ 
					while($row_bbal=$result_bbal->fetch_assoc()){
						$bbal = $row_bbal['bbalance'];
					}
				}
				else{
					$bbal = 0;
				}
				switch($mop){
					case "both":
						$cash_n_card_amt = explode("," ,$paid);
						$cash_amt = $cash_n_card_amt[0];
						$card_amt = $cash_n_card_amt[1];
						$total_payment = $cash_amt+$card_amt;
						$cbal += $cash_amt;
						$bbal += $card_amt;
						$sql_bill_info = "INSERT INTO bill_info (bill_id, bill_date, customer_id, total, paid)
										VALUES('$bill_id','$curr_date_time','$customer_id','$total','$total_payment')";
						if($conn->query($sql_bill_info) == FALSE){
							 echo "<script> alert(Error: " . $sql_bill_info . "<br>" . $conn->error ."); </script>";
						}
						if(!empty($total_payment) || $total_payment == 0){
							$cash_transactions_sql = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
												VALUES('$tid','$customer_id','$bill_id','sale','in','$cash_amt','$cbal','$curr_date_time')";
							$bank_transactions_sql = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
												VALUES('$tid','$customer_id','$bill_id','sale','in','$card_amt','$bbal','$curr_date_time')";
							if($conn->query($cash_transactions_sql) == FALSE){
								 echo "<script> alert(Error: " . $cash_transactions_sql . "<br>" . $conn->error ."); </script>";
							}
							if($conn->query($bank_transactions_sql) == FALSE){
								 echo "<script> alert(Error: " . $bank_transactions_sql . "<br>" . $conn->error ."); </script>";
							}
							else echo "<div class='alert alert-success'>Record Successfully Entered</div>";
						}
					break;
					case "cash":
						$cbal += $paid;
						$sql_bill_info = "INSERT INTO bill_info (bill_id, bill_date, customer_id, total, paid)
										VALUES('$bill_id','$curr_date_time','$customer_id','$total','$paid')";
						if($conn->query($sql_bill_info) == FALSE){
							 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
						}
						if(!empty($paid) || $paid == 0){
							$transactions_sql = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
												VALUES('$tid','$customer_id','$bill_id','sale','in','$paid','$cbal','$curr_date_time')";
							if($conn->query($transactions_sql) == FALSE){
								 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
							}
							else echo "<div class='alert alert-success'>Record Successfully Entered</div>";
						}
					break;
					case "card":
						$bbal += $paid;
						$sql_bill_info = "INSERT INTO bill_info (bill_id, bill_date, customer_id, total, paid)
										VALUES('$bill_id','$curr_date_time','$customer_id','$total','$paid')";
						if($conn->query($sql_bill_info) == FALSE){
							 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
						}
						if(!empty($paid) || $paid == 0){
							$transactions_sql = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
												VALUES('$tid','$customer_id','$bill_id','sale','in','$paid','$bbal','$curr_date_time')";
							if($conn->query($transactions_sql) == FALSE){
								 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
							}
							else echo "<div class='alert alert-success'>Record Successfully Entered</div>";
						}
					break;
					case "":
						$cbal += $paid;
						$sql_bill_info = "INSERT INTO bill_info (bill_id, bill_date, customer_id, total, paid)
										VALUES('$bill_id','$curr_date_time','$customer_id','$total','$paid')";
						if($conn->query($sql_bill_info) == FALSE){
							 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
						}
						if(!empty($paid) || $paid == 0){
							$transactions_sql = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
												VALUES('$tid','$customer_id','$bill_id','sale','in','$paid','$cbal','$curr_date_time')";
							if($conn->query($transactions_sql) == FALSE){
								 echo "<script> alert(Error: " . $sql_customer . "<br>" . $conn->error ."); </script>";
							}
							else echo "<div class='alert alert-success'>Record Successfully Entered</div>";
						}
					break;
				}
			}
			else echo "<div class='alert alert-danger'> All fields are mandatory </div>"; ?>
			<script type="text/javascript">
				$(".save-btn").show();
			</script>
			<?php
		break;
		
		case "search-customer-if-it-already-exists":
			$num = $_REQUEST['val'];
			$sql = "SELECT * FROM customers WHERE mobile LIKE '%$num%'";
			$result = $conn->query($sql);
			if($result->num_rows>0){ 
                while($row=$result->fetch_assoc()){ 
					$mobile = $row['mobile'];
					$name = $row['name']; ?>
					<tr class="single-mob-row">
						<td id='cnum'><?php echo $mobile; ?></td>
						<td id='cname'><?php echo $name; ?></td>
					</tr> <?php
				}
			} ?>
			<script>
				$(".single-mob-row").click(function(){
					var mobile = $(this).find("#cnum").text().replace(/\s+/g,"").replace(/(\r\n|\n|\r)/gm,"");
					var name = $(this).find("#cname").text().replace(/\s+/g," ").replace(/(\r\n|\n|\r)/gm,"");
					$("#mob").val(mobile).trigger("change").trigger("keyup");
					$("#name").val(name).trigger("change").trigger("keyup");
					$(".cust-num-suggestions").hide();
					//$("#mob").trigger("change");
				});
			</script><?php
		break;
		
		case "fetch-customer-data-for-Sales-return":
			$bill_no = $_REQUEST['billNumber'];	?>
			<table style="width:100%;margin-bottom:10px;" class="cust-info"></table>
			<table style="border-bottom:1px solid;width:100%">
				<tr style="border-bottom: 2px double;">
					<td><strong>Date & Time</strong></td>
					<td><strong>Item Name</strong></td>
					<td><strong>Brand Name</strong></td>
					<td><strong>Quantity</strong></td>
					<td><strong>Discount</strong></td>
					<td><strong>Size</strong></td>
					<td><strong>MRP</strong></td>
					<td><strong>Amount</strong></td>
				</tr> <?php
			//$sr_sql = "SELECT a.*, b.bill_date, c.*, d.name FROM sales a, bill_info b, products c, customers d WHERE a.bill_id='$bill_no' AND b.bill_id='$bill_no' AND c.pid=a.products AND a.customer_id=d.mobile AND a.status != 'sr'";
			$sr_sql = "SELECT a.*, b.bill_date, c.*, d.name, SUM(IF(a.status='sale',a.quantity,0)) - SUM(IF(a.status='sr',a.quantity,0)) AS qty, SUM(IF(a.status='sale',a.amount,0)) - SUM(IF(a.status='sr',a.amount,0)) AS amt FROM sales a, bill_info b, products c, customers d WHERE a.bill_id='$bill_no' AND b.bill_id='$bill_no' AND c.pid=a.products AND a.customer_id=d.mobile GROUP BY a.bill_id, a.products";
			$sr_result = $conn->query($sr_sql);
			if($sr_result->num_rows>0){ 
                while($sr_row=$sr_result->fetch_assoc()){
					$pid = $sr_row['pid'];
					$date = date("d/m/Y H:i:s",strtotime($sr_row['bill_date']));
					$bill = $sr_row['bill_id'];
					$products = $sr_row['pname']; 
					$brand = $sr_row['brand_name']; 
					$quantity = $sr_row['qty'];
					$discount = $sr_row['discount'];
					$amount = $sr_row['amt'];
					$status = $sr_row['status'];
					$size = $sr_row['psize'];
					$mrp = $sr_row['sprice'];
					$cid = $sr_row['customer_id'];
					$name = $sr_row['name'];
					$returned_sql = "SELECT products, SUM(quantity) AS qty FROM sales WHERE bill_id='$bill_no' AND products='$pid' AND status='sr'";
					$returned_result = $conn->query($returned_sql);
					if($returned_result->num_rows>0){ 
						while($returned_row=$returned_result->fetch_assoc()){
							$ret_qty = $returned_row['qty'];
						}	
					} ?>
					<script>
						$(".cust-info").html("<tr><td class='alert alert-success'>"+"<?php echo $name; ?>"+"</td><td class='alert alert-success'>"+"<?php echo $cid; ?>"+"</td></tr>");
						$("#mob").val("<?php echo $cid; ?>");
						$("#name").val("<?php echo $name; ?>");
					</script>
					<tr style="border-bottom: 1px dotted;" id="<?php echo $pid; ?>">
						<td id="sdate"><?php echo $date; ?></td>
						<td id="sprod"><?php echo $products; ?></td>
						<td id="sprod"><?php echo $brand; ?></td>
						<td id="sqty"><?php echo $quantity; ?><?php if(!empty($ret_qty)){ ?><button style="margin-left: 2px;" class="btn btn-danger btn-xs">returned <?php echo $ret_qty; ?></button><?php } ?></td>
						<td id="sdisc"><?php echo $discount." %"; ?></td>
						<td id="ssize"><?php echo $size; ?></td>
						<td id="smrp">&#8377 <?php echo $mrp." /-"; ?></td>
						<td id="samt">&#8377 <?php echo $amount." /-"; ?></td>
					</tr> <?php
				}
				$due_sql = "SELECT SUM(total), SUM(paid) FROM bill_info WHERE customer_id='$cid'";
				$due_result = $conn->query($due_sql);
				if($due_result->num_rows>0){ 
					while($due_row=$due_result->fetch_assoc()){
						$due = $due_row["SUM(total)"] - $due_row["SUM(paid)"]; ?>
						<script>
							$(".cust-info").find("tr").append("<td class='alert alert-<?php echo ($due == 0) ? "success" : "danger"; ?> dues'>"+"Due: &#8377 <?php echo $due." /-"; ?>"+"</td>");
						</script><?php
					}
				}
			} ?>
			</table> <?php
		break;
		
		case "barcode-input-for-the-product-to-be-returned":
			$barcode_input = $_REQUEST['value'];
			$bill_num = $_REQUEST['billNum'];
			$validate_prod_sql = "SELECT a.*, b.pname FROM sales a, products b WHERE a.bill_id = '$bill_num' AND a.products = '$barcode_input' AND b.pid = a.products";
			$validate_prod_result = $conn->query($validate_prod_sql);
			if($validate_prod_result->num_rows>0){ 
                while($validate_prod_row=$validate_prod_result->fetch_assoc()){
					$qty = $validate_prod_row['quantity'];
					$amount = $validate_prod_row['amount'];
					$pid = $validate_prod_row['products'];
					$cid = $validate_prod_row['customer_id'];
					$pname = $validate_prod_row['pname'];
					$unit_price = $amount/$qty;
					if($qty > 1){ ?>
						<input id="qtbr" style="width:100%;padding:10px" min="1" max="<?php echo $qty; ?>" value="<?php echo $qty; ?>" type="text"> 
						<script>
							$("#qtbr").one("change",function(){
								var pid = '<?php echo $pid; ?>';
								var amt = '<?php echo $amount; ?>';
								var cid = '<?php echo $cid; ?>';
								var billNum = '<?php echo $bill_num; ?>';
								var prodName = '<?php echo $pname; ?>';
								var unitPrice = '<?php echo $unit_price; ?>';
								var qty = $(this).val();
								var amtToBeRet = parseInt(qty)*parseInt(unitPrice);
								prodArrForSaleRet.push(billNum+"/"+cid+"/"+pid+"/"+qty+"/"+amtToBeRet+"/"+"sr");
								$.post("ajax-req-handler.php", {key: "implode-array", array: prodArrForSaleRet}, function(data){ $(".sale-return-array").html(data) });
								$(".returned-products-container table").append("<tr><td>"+prodName+"</td><td>"+qty+"</td><td>"+amtToBeRet+"</td></tr>");
								$("#total").val(parseInt($("#total").val())+parseInt(amtToBeRet));
								$("#paid").val(parseInt($("#paid").val())+parseInt(amtToBeRet));
								$(".cust-info").find("tr").append("<td class='alert alert-danger'>Amount to be returned: &#8377 "+$("#total").val()+"</td>");
								$('#with').focus();
								$(".exchange-panel .ret").append("<div id='"+pid+"' style='text-align:center' class='alert alert-danger'>"+amtToBeRet+"</div>");
							});
						</script> <?php
					}
					else{ ?>
						<script>
							var pid = '<?php echo $pid; ?>';
							var amt = '<?php echo $amount; ?>';
							var cid = '<?php echo $cid; ?>';
							var billNum = '<?php echo $bill_num; ?>';
							var prodName = '<?php echo $pname; ?>';
							var unitPrice = '<?php echo $unit_price; ?>';
							var qty = 1;
							var amtToBeRet = parseInt(qty)*parseInt(unitPrice);
							prodArrForSaleRet.push(billNum+"/"+cid+"/"+pid+"/"+qty+"/"+amtToBeRet+"/"+"sr");
							$.post("ajax-req-handler.php", {key: "implode-array", array: prodArrForSaleRet}, function(data){ $(".sale-return-array").html(data) });
							$(".returned-products-container table").append("<tr><td>"+prodName+"</td><td>"+qty+"</td><td>"+amtToBeRet+"</td></tr>");
							$("#total").val(parseInt($("#total").val())+parseInt(amtToBeRet));
							$("#paid").val(parseInt($("#paid").val())+parseInt(amtToBeRet));
							$(".cust-info").find("tr").append("<td class='alert alert-danger'>Amount to be returned: &#8377 "+$("#total").val()+"</td>");
							$('#with').focus();
							$(".exchange-panel .diff").html(amtToBeRet);
							$(".exchange-panel .ret").append("<div id='"+pid+"' style='text-align:center' class='alert alert-danger'>"+amtToBeRet+"</div>");
						</script> <?php
					}	
				}
			}
			else{
				echo "<div class='alert alert-danger>ERROR!This product was not bought by this customer</div>'";
			}
		break;
		
		case "implode-array":
			if(!empty($_REQUEST['array'])) $array = implode("-", $_REQUEST['array']);
			if(!empty($array)) echo $array;
			
		break;
		
		case "submit-sales-return-data":
			$mobile = $_REQUEST['mobile'];
			$bill_num = $_REQUEST['billNum'];
			$paid = $_REQUEST['paidAmt'];
			$total = $_REQUEST['total'];
			$ret_prods = $_REQUEST['retProds'];
			$name = $_REQUEST['name'];
			$curr_date = date('Y-m-d H:i:s');
			$tid = "";
			$snAbr = explode(" ",$name);
			foreach($snAbr as $val){
				$tid .= substr($val,0,1);
			}
			$tid .= date("dmyis"); 
			$prod_arr = explode("-", $ret_prods);
			for($i=0;$i<count($prod_arr);$i++){
				$single_array_ele = explode("/", $prod_arr[$i]);
				$sale_sql = "INSERT INTO sales (sale_date, bill_id, customer_id, products, quantity, amount, status)
				VALUES('$curr_date','$single_array_ele[0]','$single_array_ele[1]','$single_array_ele[2]','$single_array_ele[3]','$single_array_ele[4]','$single_array_ele[5]')";
				if($conn->query($sale_sql) == FALSE) echo "<script> alert(Error: " . $sale_sql . "<br>" . $conn->error ."); </script>";
				$update_inv_sql = "UPDATE products SET inventory=inventory+'$single_array_ele[3]' WHERE pid='$single_array_ele[2]'";
				if($conn->query($update_inv_sql) == FALSE) echo "<script> alert(Error: " . $update_inv_sql . "<br>" . $conn->error ."); </script>";
				else echo "<div class='alert alert-success'>Product Returned Successfully</div>";
			}
			$sql_cbal = "SELECT balance FROM cash_transactions ORDER BY transaction_date DESC LIMIT 1";
			$result_cbal = $conn->query($sql_cbal);
			if($result_cbal->num_rows>0){ 
				while($row_cbal=$result_cbal->fetch_assoc()){
					$cbal = $row_cbal['balance'] + $paid;
				}
			}
			else{
				$cbal = $paid;
			}
			
			$cash_trans_sql = "INSERT INTO cash_transactions(transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
			VALUES('$tid','$mobile','$bill_num','sale return','out','$paid','$cbal','$curr_date')";
			if($conn->query($cash_trans_sql) == FALSE) echo "<script> alert(Error: " . $cash_trans_sql . "<br>" . $conn->error ."); </script>";
			
			$bill_info_sql = "UPDATE bill_info SET total=total-'$total', paid=paid-'$paid' WHERE bill_id = '$bill_num'";
			if($conn->query($bill_info_sql) == FALSE) echo "<script> alert(Error: " . $bill_info_sql . "<br>" . $conn->error ."); </script>";
		break;
		
		case "draw-line-chart":
			$sql = "SELECT DATE(sale_date) AS sale_date, SUM(amount) AS total FROM sales WHERE status != 'sr' GROUP BY DATE(sale_date)";
			$result = $conn->query($sql);
			/*$db_data = array();
			while ($row = $result->fetch_assoc()) {
				$db_data[] = $row;
			}
			echo json_encode($db_data);*/
			 $db_data = array(); //change from this line onward
			 while ($row = $result->fetch_assoc()) {
			   $db_data[$row['sale_date']] = $row;
			 }
			$date_array = array_keys($db_data);

			$final_date_array = [];

			$begin = new DateTime($date_array[0]);
			$end = new DateTime(end($date_array));

			$daterange = new DatePeriod($begin, new DateInterval('P1D'), $end);

			foreach($daterange as $date){
				$final_date_array[] =  $date->format("Y-m-d");
			}
			$final_date_array[] = $end->format("Y-m-d");

			$actual_array = [];
			 foreach ($final_date_array as $arr){
				if(isset($db_data[$arr])){
					$actual_array[] = ['sale_date'=>$arr,'total'=>$db_data[$arr]['total']]; 
				}else{
					$actual_array[] = ['sale_date'=>$arr,'total'=>0];
				}
			 }
			echo json_encode($actual_array);
		break;
		
		case "add-invetory":
			$pid = $_REQUEST['pid'];
			$supplier = $_REQUEST['supplier'];
			$inventory = $_REQUEST['inventory'];
			$total = $_REQUEST['total'];
			$paid = $_REQUEST['paid'];
			$curr_date = date('Y-m-d H:i:s');
			$stid = "";
					$snAbr = explode(" ",$supplier);
					foreach($snAbr as $val){
						$stid .= substr($val,0,1);
					}
					$stid .= date("dmysi");
			if(strstr($pid, ' / ')){
				$array_pid = explode(' / ', $pid);
				$array_inv = explode('/', $inventory);
				array_pop($array_pid);
				if(count($array_pid) == count($array_inv)){
					for($i=0; $i<count($array_pid); $i++){
						$sql = "INSERT INTO stock (date_of_trans, stid, pid, party, quantity, paid, total) 
							VALUES('$curr_date','$stid$i','$array_pid[$i]','$supplier','$array_inv[$i]','$paid','$total')";
						$sql_update = "UPDATE products SET inventory=inventory+$array_inv[$i] WHERE pid='$array_pid[$i]'";
						if($conn->query($sql)==TRUE && $conn->query($sql_update)==TRUE){
							echo "<div class='alert alert-success'>Record Successfully Entered</div>";
						}
						else{
							echo "<div class='alert alert-danger'>Error</div>";
						}
					}
				}
				else{
					echo "<div class='alert alert-danger'><strong>ERROR!<br/></strong> Number of elements in 'Product ID' and 'Inventory' should be same</div>";
				}
			}
			else{
				
				$sql = "INSERT INTO stock (date_of_trans, stid, pid, party, quantity, paid, total) 
						VALUES('$curr_date','$stid','$pid','$supplier','$inventory','$paid','$total')";
				$sql_update = "UPDATE products SET inventory=inventory+$inventory WHERE pid='$pid'";
				if($conn->query($sql)==TRUE && $conn->query($sql_update)==TRUE){
					echo "<div class='alert alert-success'>Record Successfully Entered</div>";
				}
				else{
					echo "<div class='alert alert-danger'>Error</div>";
				}
			}
		break;
		
		case "get-product-ID-for-inventory":
			$pid_input = $_REQUEST['pidInp'];
			if(strstr($pid_input, ' / ')){
				$pid_array = explode(" / ", $pid_input);
				$next_id = end($pid_array);
				echo $next_id;
				$sql = "SELECT pid, pname, psize FROM products WHERE pid LIKE '%$next_id%' OR pname LIKE '%$next_id%'";
				$result = $conn->query($sql);
				if($result->num_rows>0){
					while($row=$result->fetch_assoc()){
						$pid = $row['pid'];
						$name = $row['pname'];
						$size = $row['psize']; ?>
						<tr id="<?php echo $pid; ?>" class="prod-details"><?php echo "<td>".$name."</td><td>".$size."</td><td>".$pid."</td>"; ?></tr> <?php
					}
				} 
			}
			else{
				$sql = "SELECT pid, pname, psize FROM products WHERE pid LIKE '%$pid_input%' OR pname LIKE '%$pid_input%'";
				$result = $conn->query($sql);
				if($result->num_rows>0){
					while($row=$result->fetch_assoc()){
						$pid = $row['pid'];
						$name = $row['pname'];
						$size = $row['psize']; ?>
						<tr id="<?php echo $pid; ?>" class="prod-details"><?php echo "<td>".$name."</td><td>".$size."</td><td>".$pid."</td>"; ?></tr> <?php
					}
				} 
			} ?>
			<script>
				$(".prod-details").click(function(){
					$(".products-arr").append($(this).attr("id")+" / ");
					$("#pid").val($(".products-arr").html());
					$(".pid-resp").hide();
					$("#pid").focus();
				});
			</script> <?php
		break;
		
		case "Delete-Product":
			$pid = $_REQUEST['id'];
			$sql = "DELETE FROM products WHERE pid='$pid'";
			if($conn->query($sql)){
				echo "<div class='alert alert-success>Product Successfully Deleted</div>'";
			}
			else{
				echo "<div class='alert alert-danger><strong>ERROR!</strong><br/>Product annot be deleted<br/>".$conn->error."</div>'";
			}
		break;
		
		case "apply-filters":
			$sql = "SELECT * FROM products WHERE 1=1 AND ";
			$sql2 = "SELECT SUM(inventory) FROM products WHERE 1=1 AND ";
			foreach ($_REQUEST as $key => $value) {
				$columnName = '';
				switch ($key) {
					case 'name':
						$columnName = 'pname';
						break;

					case 'brand':
						$columnName = 'brand_name';
						break;

					case 'cat':
						$columnName = 'ptype';
						break;

					case 'size':
						$columnName = 'psize';
						break;

					case 'price':
						$columnName = 'sprice';
						break;

					case 'inv':
						$columnName = 'inventory';
						break;
				}

				if (!empty($columnName) && !empty($value)) {
					$sql .= " $columnName LIKE '%$value%' AND";
					$sql2 .= " $columnName LIKE '%$value%' AND";
				}
			}
			$sql = rtrim($sql, 'AND');
			$sql2 = rtrim($sql2, 'AND');
			$result = $conn->query($sql); 
			$result2 = $conn->query($sql2); ?>
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
			</tr> <?php
			if($result2->num_rows>0){
				while($row2=$result2->fetch_assoc()){
					$tot_inv = $row2['SUM(inventory)'];
				}
			}
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$pid = $row['pid'];
					$pname = $row['pname'];
					$pbrand = $row['brand_name'];
					$pcat = $row['ptype'];
					$pinv = $row['inventory'];
					$psprice = $row['sprice'];
					$pbprice = $row['bprice'];
					$psgst = $row['sgst'];
					$pbgst = $row['bgst'];
					$psize = $row['psize'];
					switch($pcat){
						case "c":
							$pcat = "Clothing";
						break;
						case "f":
							$pcat = "Footwear";
						break;
						case "o":
							$pcat = "Other";
						break;
					}	?>
					<tr class="single-product-row" id="<?php echo $pid; ?>">
						<td><?php echo $pid; ?></td>
						<td><?php echo $pname; ?></td>
						<td><?php echo $pbrand; ?></td>
						<td><?php echo $pcat; ?></td>
						<td><?php echo $psize; ?></td>
						<td>
							<table>
								<tr>
									<td style="width:50%;"><?php echo number_format($psprice); ?></td>
									<td style="width:50%;"><?php echo number_format($pbprice); ?></td>
								</tr>
							</table>
						</td>
						<td>
							<table>
								<tr>
									<td style="width:50%;"><?php echo $psgst; ?></td>
									<td style="width:50%;"><?php echo $pbgst; ?></td>
								</tr>
							</table>
						</td>
						<td><?php echo $pinv; ?></td>
					</tr> <?php
				}
			} ?>
				<script>
					$(".tot-inv").html('<?php echo $tot_inv; ?>');
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
				</script> <?php
		break;
		
		case "Load-all-products":
			$sql = "SELECT SUM(inventory) FROM products";
			$prod_list_sql = "SELECT * FROM products ORDER BY pname DESC";
			$prod_list_result = $conn->query($prod_list_sql); 
			$result = $conn->query($sql); ?>
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
			</tr> <?php
			if($result->num_rows>0){ 
				while($row=$result->fetch_assoc()){ 
					$tot_inv = $row['SUM(inventory)'];
				}
			}
			if($prod_list_result->num_rows>0){ 
				while($prod_list_row=$prod_list_result->fetch_assoc()){ 
					$id = $prod_list_row['pid'];
					$name = $prod_list_row['pname'];
					$brand = $prod_list_row['brand_name'];
					$category = $prod_list_row['ptype'];
					$size = $prod_list_row['psize'];
					$sp = $prod_list_row['sprice'];
					$bp = $prod_list_row['bprice'];
					$sgst = $prod_list_row['sgst'];
					$bgst = $prod_list_row['bgst'];
					$inventory = $prod_list_row['inventory']; 
					switch($category){
						case "c":
							$category = "Clothing";
						break;
						case "f":
							$category = "Footwear";
						break;
						case "o":
							$category = "Other";
						break;
					} ?>
					<tr class="single-product-row" id="<?php echo $id; ?>">
						<td><?php echo $id; ?></td>
						<td><?php echo $name; ?></td>
						<td><?php echo $brand; ?></td>
						<td><?php echo $category; ?></td>
						<td><?php echo $size; ?></td>
						<td>
							<table>
								<tr>
									<td style="width:50%;"><?php echo number_format($sp); ?></td>
									<td style="width:50%;"><?php echo number_format($bp); ?></td>
								</tr>
							</table>
						</td>
						<td>
							<table>
								<tr>
									<td style="width:50%;"><?php echo $sgst; ?></td>
									<td style="width:50%;"><?php echo $bgst; ?></td>
								</tr>
							</table>
						</td>
						<td><?php echo $inventory; ?></td>
					</tr> <?php
				}
			} ?>
			<script>
				$(".tot-inv").html('<?php echo $tot_inv; ?>');
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
				</script> <?php
		break;
		
		case "check-existing-entries":
			$product_name = ucfirst($_REQUEST['pname']);
			$brand_name = $_REQUEST['bname'];
            $product_size = $_REQUEST['psize'];
            $product_sprice = $_REQUEST['sprice'];
			$product_bprice = $_REQUEST['bprice'];
			$prod_list_sql = "SELECT * FROM products WHERE pname='$product_name' && brand_name='$brand_name' && psize='$product_size' && sprice='$product_sprice' && bprice='$product_bprice' LIMIT 1";
			$prod_list_result = $conn->query($prod_list_sql);
			if($prod_list_result->num_rows>0){
				while($row=$prod_list_result->fetch_assoc()){
					$pid = $row['pid'];
					$pname = $row['pname'];
					$pbrand = $row['brand_name'];
					$pcat = $row['ptype'];
					$pinv = $row['inventory'];
					$psprice = $row['sprice'];
					$pbprice = $row['bprice'];
					$psgst = $row['sgst'];
					$pbgst = $row['bgst'];
					$psize = $row['psize'];
					if($product_name == $pname && $brand_name == $pbrand && $product_size == $psize && $product_sprice == $psprice && $product_bprice == $pbprice){
						echo "<strong style='cursor:pointer' class='alert alert-warning ' id='".$pid."'>WARNING! An entry with same credentials already exists</strong>";
					} ?>
					<script>
						$("#<?php echo $pid ?>").click(function(){
							var id = "<?php echo $pid ?>";
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
					</script> <?php
				}
			} 
		break;
		
		case "predict-product-name":
			$value = $_REQUEST['val'];
			$sql = "SELECT DISTINCT pname FROM products WHERE pname LIKE '%$value%'";
			$result = $conn->query($sql);
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$pnames = $row['pname'];
					echo "<tr class='name-row'><td>".$pnames."</td></tr>";
				}
			} ?>
			<script>
				$(".name-row").click(function(){
					$("#pname").val($(this).text());
					$('#pname').trigger('change');
					$(".name-resp").hide();
				});
			</script> <?php
		break;
		
		case "predict-brand-name":
			$value = $_REQUEST['val'];
			$sql = "SELECT DISTINCT brand_name FROM products WHERE brand_name LIKE '%$value%'";
			$result = $conn->query($sql);
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$bnames = $row['brand_name'];
					echo "<tr class='brand-row'><td>".$bnames."</td></tr>";
				}
			} ?>
			<script>
				$(".brand-row").click(function(){
					$("#bname").val($(this).text());
					$('#bname').trigger('change');
					$(".brand-resp").hide();
				});
			</script> <?php
		break;
		
		/*case "decode-products":
			$date = $_REQUEST['date'];
			$pid = $_REQUEST['id'];
			$qty = $_REQUEST['qty'];
			$price = $_REQUEST['price'];
			$sql = "SELECT * FROM products WHERE pid='$pid' LIMIT 1";
			$result = $conn->query($sql); 
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$pid = $row['pid'];
					$name = $row['pname'];
					$brand = $row['brand_name'];
					$size = $row['psize'];
					$mrp = $row['sprice'];
				}
			} ?>
			
			<tr id="<?php echo $pid; ?>">
				<td><?php echo $date; ?></td>
				<td><?php echo $name; ?></td>
				<td><?php echo $brand; ?></td>
				<td class="qty"><?php echo $qty; ?></td>
				<td><?php echo $size; ?></td>
				<td><?php echo $mrp; ?></td>
				<td class="price"><?php echo $price; ?></td>
			</tr> <?php
			
		break;*/
		
		case "load-today's-sales":
			$curr_date = date('Y-m-d');
			$sql = "select a.products, a.sale_date, b.pid, b.pname, b.brand_name, b.psize, b.sprice, 
			SUM(IF(a.status='sale',a.quantity,0)) - SUM(IF(a.status='sr',a.quantity,0)) AS qty, 
			SUM(IF(a.status='sale',a.amount,0)) - SUM(IF(a.status='sr',a.amount,0)) AS amt 
			FROM sales a, products b WHERE a.products=b.pid AND DATE(a.sale_date) = '$curr_date' GROUP BY a.products ORDER BY a.sale_date DESC";
			$result = $conn->query($sql); ?>
			<tr class="tab-head">
				<td>Date</td>
				<td>Product Id</td>
				<td>Product</td>
				<td>Brand Name</td>
				<td>Quantity</td>
				<td>Size</td>
				<td>MRP</td>
				<td>Amount</td>
			</tr> <?php
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$date = date("d/m/Y", strtotime($row['sale_date']));
					$id = $row['pid'];
					$name = $row['pname'];
					$brand = $row['brand_name'];
					$quantity = $row['qty'];
					$size = $row['psize'];
					$mrp = $row['sprice'];
					$amount = $row['amt'];	
					$returned_sql = "SELECT products, SUM(quantity) AS rqty FROM sales WHERE DATE(sale_date)='$curr_date' AND products='$id' AND status='sr'";
					$returned_result = $conn->query($returned_sql);
					if($returned_result->num_rows>0){
						while($returned_row=$returned_result->fetch_assoc()){
							$ret_qty = $returned_row['rqty'];
						}
					}	?>
					<tr>
						<td><?php echo $date; ?></td>
						<td><?php echo $id; ?></td>
						<td><?php echo $name; ?></td>
						<td><?php echo $brand; ?></td>
						<td><?php echo $quantity; ?><?php if(!empty($ret_qty)){ ?><button style="margin-left:2px;;float:right" class="btn btn-danger btn-xs">returned <?php echo $ret_qty; ?></button><?php } ?></td>
						<td><?php echo $size; ?></td>
						<td><?php echo $mrp; ?></td>
						<td><?php echo $amount; ?></td>
					</tr> <?php
				}
			}
			else echo "<tr><td colspan=8 style='text-align:center' class='alert alert-warning'>Oops! Nothing Found</td></tr>";
		break;
		
		case "date-range-for-sales":
			$from = $_REQUEST['fromDate'];
			$to = $_REQUEST['toDate'];
			if(!empty($from) && !empty($to)){
				//$sql = "SELECT products, DATE(sale_date) FROM customers WHERE trans_detail != 'sr' AND total = paid AND  DATE(sale_date) BETWEEN '$from' AND '$to';";
				$sql = "select a.products, a.sale_date, b.pid, b.pname, b.brand_name, b.psize, b.sprice, SUM(IF(a.status='sale',a.quantity,0)) - SUM(IF(a.status='sr',a.quantity,0)) AS qty, SUM(IF(a.status='sale',a.amount,0)) - SUM(IF(a.status='sr',a.amount,0)) AS amt FROM sales a, products b WHERE a.products=b.pid AND DATE(a.sale_date) BETWEEN '$from' AND '$to' GROUP BY a.products ORDER BY a.sale_date DESC";
				$result = $conn->query($sql); ?>
				<tr class="tab-head">
					<td>Date</td>
					<td>Product Id</td>
					<td>Product</td>
					<td>Brand Name</td>
					<td>Quantity</td>
					<td>Size</td>
					<td>MRP</td>
					<td>Amount</td>
				</tr> <?php
				if($result->num_rows>0){
					while($row=$result->fetch_assoc()){
						$date = date("d/m/Y", strtotime($row['sale_date']));
						$id = $row['pid'];
						$name = $row['pname'];
						$brand = $row['brand_name'];
						$quantity = $row['qty'];
						$size = $row['psize'];
						$mrp = $row['sprice'];
						$amount = $row['amt'];
						
						$returned_sql = "SELECT products, SUM(quantity) AS rqty FROM sales WHERE DATE(sale_date) BETWEEN '$from' AND '$to' AND products='$id' AND status='sr'";
						$returned_result = $conn->query($returned_sql);
						if($returned_result->num_rows>0){
							while($returned_row=$returned_result->fetch_assoc()){
								$ret_qty = $returned_row['rqty'];
							}
						}	?>
						<tr>
							<td><?php echo $date; ?></td>
							<td><?php echo $id; ?></td>
							<td><?php echo $name; ?></td>
							<td><?php echo $brand; ?></td>
							<td><?php echo $quantity; ?><?php if(!empty($ret_qty)){ ?><button style="margin-left:2px;float:right" class="btn btn-danger btn-xs">returned <?php echo $ret_qty; ?></button><?php } ?></td>
							<td><?php echo $size; ?></td>
							<td><?php echo $mrp; ?></td>
							<td><?php echo $amount; ?></td>
						</tr> <?php 
					}
				}
				else echo "<tr><td colspan=8 style='text-align:center' class='alert alert-warning'>Oops! Nothing Found</td></tr>";
			}
			elseif(!empty($from) && empty($to)){
				$to = date('Y-m-d');
				$sql = "select a.products, a.sale_date, b.pid, b.pname, b.brand_name, b.psize, b.sprice, SUM(IF(a.status='sale',a.quantity,0)) - SUM(IF(a.status='sr',a.quantity,0)) AS qty, SUM(IF(a.status='sale',a.amount,0)) - SUM(IF(a.status='sr',a.amount,0)) AS amt FROM sales a, products b WHERE a.products=b.pid AND DATE(a.sale_date) BETWEEN '$from' AND '$to' GROUP BY a.products ORDER BY a.sale_date DESC";
				echo $sql;
				$result = $conn->query($sql); ?>
				<tr class="tab-head">
					<td>Date</td>
					<td>Product Id</td>
					<td>Product</td>
					<td>Brand Name</td>
					<td>Quantity</td>
					<td>Size</td>
					<td>MRP</td>
					<td>Amount</td>
				</tr> <?php
				if($result->num_rows>0){
					while($row=$result->fetch_assoc()){
						$date = date("d/m/Y", strtotime($row['sale_date']));
						$id = $row['pid'];
						$name = $row['pname'];
						$brand = $row['brand_name'];
						$quantity = $row['qty'];
						$size = $row['psize'];
						$mrp = $row['sprice'];
						$amount = $row['amt'];	
						
						$returned_sql = "SELECT products, SUM(quantity) AS rqty FROM sales WHERE DATE(sale_date) BETWEEN '$from' AND '$to' AND products='$id' AND status='sr'";
						echo $returned_sql;
						$returned_result = $conn->query($returned_sql);
						if($returned_result->num_rows>0){
							while($returned_row=$returned_result->fetch_assoc()){
								$ret_qty = $returned_row['rqty'];
							}
						}	?>
						<tr>
							<td><?php echo $date; ?></td>
							<td><?php echo $id; ?></td>
							<td><?php echo $name; ?></td>
							<td><?php echo $brand; ?></td>
							<td><?php echo $quantity; ?><?php if(!empty($ret_qty)){ ?><button style="margin-left:2px;;float:right" class="btn btn-danger btn-xs">returned <?php echo $ret_qty; ?></button><?php } ?></td>
							<td><?php echo $size; ?></td>
							<td><?php echo $mrp; ?></td>
							<td><?php echo $amount; ?></td>
						</tr> <?php
					}
				}
				else echo "<tr><td colspan=8 style='text-align:center' class='alert alert-warning'>Oops! Nothing Found</td></tr>";
			}
		break;
		
		case "Record-Transaction":
			$curr_date_time = date("Y-m-d H:i:s");
			$amount = $_REQUEST['amt'];
			$remarks = $_REQUEST['rem'];
			$bill_id = $_REQUEST['billId'];
			$mode_of_payment = $_REQUEST['mop'];
			$type = $_REQUEST['type'];
			$tid = "";
			$snAbr = explode(" ",$remarks);
			foreach($snAbr as $val){
				$tid .= substr($val,0,1);
			}
			$tid .= date("dmyis");
			if($bill_id != 0 || !empty($bill_id)){
				$bill_sql = "SELECT bill_id, customer_id FROM bill_info WHERE bill_id='$bill_id' LIMIT 1";
				$bill_result = $conn->query($bill_sql);
				if($bill_result->num_rows>0){ 
					while($bill_row=$bill_result->fetch_assoc()){
						$customer_id = $bill_row['customer_id'];
					}
				}
				else exit("Error!Invalid Bill Number");
			}
			else{
				$customer_id = "";
			}
			
			($type == "in") ? $bill_info_sql = "UPDATE bill_info SET paid = paid+'$amount' WHERE bill_id = '$bill_id'" : $bill_info_sql = "UPDATE bill_info SET paid = paid-'$amount' WHERE bill_id = '$bill_id'";
			
			if($conn->query($bill_info_sql) == FALSE) echo "<div class='alert alert-danger alert-lg'>".$conn->error."</div>";
			
			switch($mode_of_payment){
				case "cash":
					$sql_cbal = "SELECT balance FROM cash_transactions ORDER BY transaction_date DESC LIMIT 1";
					$result_cbal = $conn->query($sql_cbal);
					if($result_cbal->num_rows>0){ 
						while($row_cbal=$result_cbal->fetch_assoc()){
							$cbal = $row_cbal['balance'];
						}
					}
					else{
						$cbal = $amount;
					}
					($type == "in") ? $cbal += $amount : $cbal -= $amount;
					$cash_transaction_sql = "INSERT INTO cash_transactions(transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
					VALUES('$tid','$customer_id','$bill_id','$remarks','$type','$amount','$cbal','$curr_date_time')";
					if($conn->query($cash_transaction_sql) == FALSE) echo "<div class='alert alert-danger alert-lg'>".$conn->error."</div>";
					else echo "<div class='alert alert-success'>Entry Successful</div>";
				break;
				
				case "card":
					$sql_bbal = "SELECT bbalance FROM bank_transactions ORDER BY btransaction_date DESC LIMIT 1";
					$result_bbal = $conn->query($sql_bbal);
					if($result_bbal->num_rows>0){ 
						while($row_bbal=$result_bbal->fetch_assoc()){
							$bbal = $row_bbal['bbalance'];
						}
					}
					else{
						$bbal = $amount;
					}
					($type == "in") ? $bbal += $amount : $bbal -= $amount;
					$bank_transaction_sql = "INSERT INTO bank_transactions(btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
					VALUES('$tid','$customer_id','$bill_id','$remarks','$type','$amount','$bbal','$curr_date_time')";
					if($conn->query($bank_transaction_sql) == FALSE) echo "<div class='alert alert-danger alert-lg'>".$conn->error."</div>";
					else echo "<div class='alert alert-success'>Entry Successful</div>";
				break;
			}
		break;
		
		case "validate-bill-number-for-transaction-entry":
			$val = $_REQUEST['value'];
			$sql = "SELECT bill_id FROM bill_info WHERE bill_id = '$val'";
			$result = $conn->query($sql);
			if($result->num_rows == 0){
				echo "<div class='alert alert-danger'>Invalid Bill Number</div>";
			}
		break;
		
		case "find-dues-if-any":
			$val = $_REQUEST['value'];
			$vc_cust_sql = "SELECT mobile FROM customers WHERE mobile = '$val'";
			$vc_cust_result = $conn->query($vc_cust_sql);
			if($vc_cust_result->num_rows>0){ 
				$sql = "SELECT SUM(total) AS tot, SUM(paid) AS paid FROM bill_info WHERE customer_id='$val'";
				$result = $conn->query($sql);
				if($result->num_rows>0){ 
					while($row=$result->fetch_assoc()){
						$total = $row['tot'];
						$paid = $row['paid'];
						$due = $total-$paid;
						if( !empty($due) || $due !=0 ) echo "<div class='alert alert-warning'>&#8377 ".$due." are due to this customer</div>'";
					}
				}
			}
		break;
		
		case "suggest-bill-numbers":
			$val = $_REQUEST['value'];
			$sql_bill_sug = "SELECT a.bill_id, a.customer_id, b.name FROM bill_info a, customers b WHERE a.customer_id = b.mobile AND (a.bill_id LIKE '%$val%' || b.mobile LIKE '%$val%' || b.name LIKE '%$val%')";
			$result_bill_sug = $conn->query($sql_bill_sug); ?>
			<table style="width:100%"> <?php
			if($result_bill_sug->num_rows>0){
				while($row_bill_sug=$result_bill_sug->fetch_assoc()){
					$bill_id = $row_bill_sug['bill_id'];
					$customer_id = $row_bill_sug['customer_id'];
					$name = $row_bill_sug['name']; ?>
					<tr class="suggestion-single-row" id="<?php echo $bill_id; ?>" >
						<td><?php echo $bill_id; ?></td>
						<td><?php echo $customer_id; ?></td>
						<td><?php echo $name; ?></td>
					</tr> <?php
				}
			} ?>
			</table>
			<script>
				$("body").click(function(){ $(".suggestions-container").hide(); });
				$(".suggestion-single-row").click(function(){
					$(".suggestions-container").hide();
					var billNum = $(this).attr('id');
					$.post("ajax-req-handler.php", {
						key: "view-bill-details-for-modifications",
						billNumber: billNum
					}, function(resp){
						$.confirm({
							title: "Update Bill", 
							type: 'green',
							typeAnimated: true,
							columnClass: 'col-md-6 col-md-offset-3',
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
			</script>	<?php
		break;
		
		case "view-bill-details-for-modifications":
			$bill_id = $_REQUEST['billNumber'];
			$sql = "SELECT a.*, b.name, c.total, c.paid, d.pid, d.pname, d.brand_name, d.psize, d.sprice, d.sgst, d.ptype,
			SUM(IF(a.status='sale',a.quantity,0)) - SUM(IF(a.status='sr',a.quantity,0)) AS qty, 
			SUM(IF(a.status='sale',a.amount,0)) - SUM(IF(a.status='sr',a.amount,0)) AS amt 
			FROM sales a, customers b, bill_info c, products d 
			WHERE a.bill_id = '$bill_id' AND a.customer_id = b.mobile AND a.bill_id = c.bill_id AND a.products = d.pid 
			GROUP BY a.products";
			$result = $conn->query($sql); ?>
			<div class="update-msg"></div>
			<table style="width:100%">
				<tr>
					<td style="width:50%"><button style="border-radius:0" type="button" class="btn btn-success btn-block reprint-bill">Reprint Bill</button></td>
					<td style="width:50%">
						<button style="border-radius:0" type="button" class="btn btn-info btn-block edit-btn">Edit</button>
						<button style="border-radius:0;display:none;margin-top:0;" type="button" class="btn btn-primary btn-block done-btn">Done</button>
					</td>
				</tr>
			</table>
			<div class="bill mod-bill">
				<p style="float:left;width:50%;font-size:11px;text-align:left">GST No.<br/> 09AAYPA5007D1ZU</p>
				<p class='bill-id' style="float:right;width:49%;font-size:11px;text-align:right"><?php echo $bill_id; ?></p>
				<div>&nbsp </div>
				<h2>S.A Fashion Gallery</h2>
				<p>Pratap Nager, Saharanpur-247001</p>
				<div class="cust-info row">
					<div style="padding:0" class="cust-name col-xs-4"></div>
					<div style="padding:0" class="cust-contact col-xs-4"></div>
					<div class="cust-time col-xs-4"></div>
				</div> 
			<table style="width:100%;background-color:#E8EEF1;" class="soft-bill">
				<tr>
					<td><strong>Item</strong></td>
					<td style="max-width:40px;"><strong>Disc<br/>(%)</strong></td>
					<td><strong>GST<br/>(%)&#8377;</strong></td>
					<td><strong>MRP<br/>(&#8377;)</strong></td>
					<td><strong>Price<br/>(&#8377;)</strong></td>
				</tr><?php
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$pid = $row['pid'];
					$mobile = $row['customer_id'];
					$name = $row['name'];
					$date = date("d/m/Y H:i:s", strtotime($row['sale_date']));
					$product = $row['pname'];
					$quantity = $row['qty'];
					$discount = $row['discount'];
					$type = $row['ptype'];
					$gst = $row['sgst'];
					$mrp = $row['sprice'];
					$amount = $row['amt'];
					$total = $row['total'];
					$paid = $row['paid'];
					$due = $total - $paid;
					switch( $type ){
						case "c" :
							( $amount > 1050) ? $gst = 12 : $gst = 5;
						break;
						
						case "f" :
							( $amount > 525) ? $gst = 18 : $gst = 5;
						break;
								
						case "o":
							$gst = 18;
						break;
					} 
					if(!empty($quantity) || $quantity != 0){ ?>
					<tr class="bill-prod-row" id="<?php echo $pid; ?>">
						<td><?php echo $product; ?><div class="qty"><?php echo "x ".$quantity; ?></div></td>
						<td style="max-width:40px;"><input style="height:39px;" type="text" class="mod-bill-inp mod-disc" value="<?php echo $discount; ?>" readonly></td>
						<td class="gst"><?php echo $gst; ?></td>
						<td class="mrp" ><?php echo $mrp; ?></td>
						<td style="max-width:40px;" class="amount"><input style="height:39px;" type="text" class="mod-bill-inp mod-amt" value="<?php echo $amount; ?>" readonly></td>
						<td class="type" style="display: none;"><?php echo $type; ?></td>
					</tr> <?php
					}
				}
			} ?>
				<tr>
					<td colspan=3>Total:</td>
					<td class="total" colspan=2>&#8377; <?php echo $total; ?> /-</td>
				</tr>
				<tr>
					<td colspan=3>Paid:</td>
					<td class="paid" style="width:30%;" colspan=2>&#8377; <?php echo $paid; ?> /-</td>
				</tr>
				<?php 
					if(!empty($due) || $due != 0) { ?>
				<tr class='due-tr'>
					<td colspan=3>Due:</td>
					<td class="due" colspan=2>&#8377; <?php echo $due; ?> /-</td>
				</tr>
					<?php } ?>
			</table>
			<p style="text-align:center"> Thank you for shopping with us<br/>Please come again soon</p>
			<p style="text-align:center;font-size:15px">COPY</p>
			<p style="text-align:center; font-size:12px"> Eden Solutions(https://edensolutions.co.in) | 9808033480</p>
			<script>
				updateArray = [];
				$(".update-array-container").html("");
				$(".cust-name").html('<input type="text" class="mod-bill-inp mod-name" value="<?php echo $name; ?>" readonly>');
				$(".cust-contact").html('<input type="text" class="mod-bill-inp mod-mob" value="<?php echo $mobile; ?>" readonly>');
				$(".cust-time").html("<?php echo $date; ?>");
				$(".edit-btn").click(function(){
					$(".mod-name, .mod-mob, .mod-disc, .mod-amt"). removeAttr('readonly');
					$(this).hide();
					$(".done-btn").show();
				});
				$(".done-btn").click(function(){
					var updateArr = $(".update-array-container").text();
					$.post("ajax-req-handler.php", {
						key: "submit-data-for-bill-updation",
						str: updateArr
					}, function(data){
						$(".update-msg").html(data);
					});
					$(".mod-name, .mod-mob, .mod-disc, .mod-amt").attr('readonly', true);
					$(this).hide();
					$(".edit-btn").show();
				});
				$(".mod-disc").change(function(){
					function deleteIfMatches(array, match){
						array.some((ele)=>{ ele.indexOf(match) >= 0 ? array.splice(array.indexOf(ele),1): null });
						 return array;
					}
					function getSum(total, num) {
						return +total + +Math.round(num);
					}
					var amountsArr = [];
					var billNumber = '<?php echo $bill_id; ?>'
					var rowId = $(this).parents("tr").attr("id");
					var disc = $(this).val();
					var mrp = $("#"+rowId+" .mrp").text();
					var qty = $("#"+rowId+" .qty").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					var gst = $("#"+rowId+" .gst").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					var amt = $("#"+rowId+" .amount input").val();
					var type = $("#"+rowId+" .type").text();
					var paid = $(".paid").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					var discAmt = parseInt(mrp) * parseInt(disc) / 100;
					var discountedAmt = ((parseInt(mrp)) - (parseInt(discAmt))) * (parseInt(qty));
					switch(type){
						case "c" :
							( parseInt(discountedAmt) > 1050) ? gst = 12 : gst = 5;
						break;
						
						case "f" :
							( parseInt(discountedAmt) > 525) ? gst = 18 : gst = 5;
						break;
								
						case "o":
							gst = 18;
						break;
					}
					$("#"+rowId+" .amount input").val(discountedAmt);
					$("#"+rowId+" .gst").text(gst);
					var loopCount = parseInt($('.soft-bill .bill-prod-row').children('.amount').length)+parseInt(2);
					for(var i=2; i<loopCount; i++){
						values = $('.soft-bill .bill-prod-row:nth-child('+i+') .amount input').val();
						amountsArr.push(values);
					}
					total = amountsArr.reduce(getSum);
					var due = parseInt(total) - parseInt(paid);
					$(".total").html("&#8377;"+total+" /-");
					$(".due").html("&#8377;"+due+" /-");
					if(due != 0 || due != ""){
						$(".soft-bill .due-tr").remove();
						$(".soft-bill").append("<tr class='due-tr'><td colspan=3>Due:</td><td class='due' colspan=2>&#8377;"+due+"/-</td></tr>");
					}
					else{
						$(".soft-bill .due-tr").remove();
					}

					deleteIfMatches(updateArray, rowId);
					updateArray.push(rowId+"/"+billNumber+"/"+disc+"/"+discountedAmt+"/"+total);
					$.post("ajax-req-handler.php", {key: "implode-array", array: updateArray}, function(data){ $(".update-array-container").html(data) });
				});
				$(".mod-amt").change(function(){
					$(".mod-disc").val("0");
					function deleteIfMatches(array, match){
						array.some((ele)=>{ ele.indexOf(match) >= 0 ? array.splice(array.indexOf(ele),1): null });
						 return array;
					}
					function getSum(total, num) {
						return +total + +Math.round(num);
					}
					var amountsArr = [];
					var billNumber = '<?php echo $bill_id; ?>'
					var rowId = $(this).parents("tr").attr("id");
					var disc = 0;
					var mrp = $("#"+rowId+" .mrp").text();
					var qty = $("#"+rowId+" .qty").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					var gst = $("#"+rowId+" .gst").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					var amt = $(this).val() * qty;
					var type = $("#"+rowId+" .type").text();
					var paid = $(".paid").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
					switch(type){
						case "c" :
							( parseInt(amt) > 1050) ? gst = 12 : gst = 5;
						break;
						
						case "f" :
							( parseInt(amt) > 525) ? gst = 18 : gst = 5;
						break;
								
						case "o":
							gst = 18;
						break;
					}
					$("#"+rowId+" .amount input").val(amt);
					$("#"+rowId+" .gst").text(gst);
					var loopCount = parseInt($('.soft-bill .bill-prod-row').children('.amount').length)+parseInt(2);
					for(var i=2; i<loopCount; i++){
						values = $('.soft-bill .bill-prod-row:nth-child('+i+') .amount input').val();
						amountsArr.push(values);
					}
					total = amountsArr.reduce(getSum);
					var due = parseInt(total) - parseInt(paid);
					$(".total").html("&#8377;"+total+" /-");
					$(".due").html("&#8377;"+due+" /-");
					if(due != 0 || due != ""){
						$(".soft-bill .due-tr").remove();
						$(".soft-bill").append("<tr class='due-tr'><td colspan=3>Due:</td><td class='due' colspan=2>&#8377;"+due+"/-</td></tr>");
					}
					else{
						$(".soft-bill .due-tr").remove();
					}

					deleteIfMatches(updateArray, rowId);
					updateArray.push(rowId+"/"+billNumber+"/"+disc+"/"+amt+"/"+total);
					$.post("ajax-req-handler.php", {key: "implode-array", array: updateArray}, function(data){ $(".update-array-container").html(data) });
				});
				$(".reprint-bill").click(function(){
					$.print(".bill");
				});
			</script>
			</div> <?php
		break;
		
		case "submit-data-for-bill-updation":
			$update_string = $_REQUEST['str'];
			if(!empty($update_string)){
				$string_to_array = explode("-", $update_string);
				for($i=0;$i<count($string_to_array);$i++){
					$extract_values = explode("/", $string_to_array[$i]);
					$product = $extract_values[0];
					$bill = $extract_values[1];
					$disc = $extract_values[2];
					$amount = $extract_values[3];
					$bill_amt = $extract_values[4];
					$sql_sales = "UPDATE sales SET discount='$disc', amount = '$amount' WHERE bill_id='$bill' AND products='$product' AND status='sale'";
					$sql_bill_info = "UPDATE bill_info SET total = '$bill_amt' WHERE bill_id='$bill'";
					if($conn->query($sql_sales) && $conn->query($sql_bill_info)){
						echo "<div class='alert alert-success'>Bill Successfully Updated</div>";
					}
					else{
						echo "<div class='alert alert-danger'>".$conn->error."</div>";
					}
				}
			}
		break;
		
		case "load-all-cash-transactions":
			$sql = "SELECT * FROM cash_transactions ORDER BY transaction_date DESC";
			$result = $conn->query($sql); ?>
			<tr class="tab-head">
				<td>Date & Time</td>
				<td>Transaction Id</td>
				<td>Remarks</td>
				<td>Type</td>
				<td>Amount</td>
				<td>Balance</td>
			</tr> <?php
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					if(!empty($row['customer_id'])){
						$cid = $row['customer_id']; 
						$sql_cname = "SELECT name FROM customers WHERE mobile='$cid'";
						$result_cname=$conn->query($sql_cname);
						if($result_cname->num_rows>0){
							while($row_cname=$result_cname->fetch_assoc()){
								$name = $row_cname['name'];
							}
						}
					}
					$transaction_id = $row['transaction_id'];
					$bill_id = $row['bill_id'];
					$date = date("d/m/Y H:i:s", strtotime($row['transaction_date']));
					$remarks = $row['remarks'];
					$type = $row['type'];
					$amount = $row['amount'];
					$balance = $row['balance'];
					( $type != 'in' ) ? $color="style='color:#a94442'" : $color="style='color:#3c763d'";	
					if( $remarks == 'sale' ){
						 $remarks="Sale"; 
					}
					elseif( $remarks == 'sale return' ){
						$remarks="Sale Return";
					}			
					else{
						$remarks = $remarks;
					}	
					if(!empty($amount)){ ?>
						<tr <?php echo $color; ?>>
							<td><?php echo $date; ?></td>
							<td><?php echo $transaction_id; ?></td>
							<td><?php echo $remarks; 
							if(!empty($bill_id)) {
								if($type == "in") {
									echo " To ".$name.", Bill: ".$bill_id; 
								}
								else{
									echo " From ".$name.", Bill: ".$bill_id; 
								}
							}							?></td>
							<td><?php echo $type; ?></td>
							<td>&#8377; <?php echo number_format($amount); ?> /-</td>
							<td>&#8377; <?php echo number_format($balance); ?> /-</td>
						</tr> <?php
					}
				}
			} 
		break;
		
		case "load-all-bank-transactions":
			//$sql = "SELECT a.*, b.name FROM bank_transactions a, customers b WHERE b.mobile = a.bcustomer_id ORDER BY a.btransaction_date DESC";
			$sql = "SELECT * FROM bank_transactions ORDER BY btransaction_date DESC";
			$result = $conn->query($sql); ?>
			<tr class="tab-head">
				<td>Date & Time</td>
				<td>Transaction Id</td>
				<td>Remarks</td>
				<td>Type</td>
				<td>Amount</td>
				<td>Balance</td>
			</tr> <?php
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					if(!empty($row['bcustomer_id'])){
						$cid = $row['bcustomer_id']; 
						$sql_cname = "SELECT name FROM customers WHERE mobile='$cid'";
						$result_cname=$conn->query($sql_cname);
						if($result_cname->num_rows>0){
							while($row_cname=$result_cname->fetch_assoc()){
								$name = $row_cname['name'];
							}
						}
					}
					$transaction_id = $row['btransaction_id'];
					$bill_id = $row['bbill_id'];
					$date = date("d/m/Y H:i:s", strtotime($row['btransaction_date']));
					$remarks = $row['bremarks'];
					$type = $row['btype'];
					$amount = $row['bamount'];
					$balance = $row['bbalance'];
					( $type != 'in' ) ? $color="style='color:#a94442'" : $color="style='color:#3c763d'";	
					if( $remarks == 'sale' ){
						 $remarks="Sale"; 
					}
					elseif( $remarks == 'sale return' ){
						$remarks="Sale Return";
					}			
					else{
						$remarks = $remarks;
					}
					if(!empty($amount)){	?>
						<tr <?php echo $color; ?>>
							<td><?php echo $date; ?></td>
							<td><?php echo $transaction_id; ?></td>
							<td><?php echo $remarks; 
							if(!empty($bill_id)){
								if($type == "in") {
									echo " To ".$name.", Bill: ".$bill_id;
								}								
								else{ 
									echo " From ".$name.", Bill: ".$bill_id;  
								}
							} ?></td>
							<td><?php echo $type; ?></td>
							<td>&#8377; <?php echo number_format($amount); ?> /-</td>
							<td>&#8377; <?php echo number_format($balance); ?> /-</td>
						</tr> <?php
					}
				}
			} 
		break;
		
		case "Exchange-product-with":
			$barcode_value = $_REQUEST['barcodeVal'];
			$sql = "SELECT * FROM products WHERE pid='$barcode_value'";
			$result = $conn->query($sql); ?> <?php  
			if($result->num_rows>0){
				while($row=$result->fetch_assoc()){
					$pid = $row['pid'];
					$name = $row['pname'];
					$size = $row['psize'];
					$sprice = $row['sprice'];
					$bprice = $row['bprice'];
					$sgst = $row['sgst'];
					$sgst_amt = $sprice * $sgst / 100;
					$bgst = $row['bgst'];
					$type = $row['ptype'];
					$tot_price = $sprice;
					$tax_amt = ($tot_price) - (($sprice)/((1)+(($sgst)/(100)))) ; ?>
					<tr id="<?php echo $pid; ?>">
						<td><?php echo $name; ?></td>
						<td><?php echo $size; ?></td>
						<td><input type="text" class="form-control qty" value="1" placeholder="Quantity"></td>
						<td><input type="text" class="form-control dis" value="0" placeholder="Discount"></td>
						<td class="mrp"><?php echo $sprice; ?></td>
						<td class="amt"><?php echo $sprice; ?></td>
					</tr>
					<script>
						$(document).ready(function(){
							function deleteIfMatches(array, match){
								array.some((ele)=>{ ele.indexOf(match) >= 0 ? array.splice(array.indexOf(ele),1): null });
								return array;
							}
							var pid = "<?php echo $pid; ?>";
							var qty = 1;
							var dis = 0;
							var amt = "<?php echo $sprice; ?>";
							arrayOfProducts.push(pid+"/"+qty+"/"+dis+"/"+amt);
							$.post("ajax-req-handler.php", {key: "implode-array", array: arrayOfProducts}, function(data){ $(".array-of-products").html(data) });
							$(".exchange-panel .rec").append("<div id='"+pid+"' style='text-align:center' class='alert alert-success'>"+amt+"</div>");
							$(".qty").change(function(){
								var mrp = $(this).parents("tr").find(".mrp").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
								qty = $(this).val();
								var total = parseInt(mrp) * parseInt(qty);
								pid = $(this).parents("tr").attr("id");
								$(this).parents("tr").find(".dis").val("0");
								deleteIfMatches(arrayOfProducts, pid);
								arrayOfProducts.push(pid+"/"+qty+"/"+0+"/"+total);
								$.post("ajax-req-handler.php", {key: "implode-array", array: arrayOfProducts}, function(data){ $(".array-of-products").html(data) });
								$(this).parents("tr").find(".amt").text(total);
								$(".exchange-panel .rec #"+pid).html(total.toFixed());
								$("#total").val(total.toFixed());
								$("#paid").val(total.toFixed());
							});
							$(".dis").change(function(){
								var mrp = $(this).parents("tr").find(".mrp").text().replace(/[^0-9.]/g, '').replace(/\s+/g,'');
								qty = $(this).parents("tr").find(".qty").val();
								var total = parseInt(mrp) * parseInt(qty);
								pid = $(this).parents("tr").attr("id");
								dis = $(this).val();
								total = (parseInt(total)) - (parseInt(total) * (parseInt(dis) / 100));
								deleteIfMatches(arrayOfProducts, pid);
								arrayOfProducts.push(pid+"/"+qty+"/"+dis+"/"+total);
								$.post("ajax-req-handler.php", {key: "implode-array", array: arrayOfProducts}, function(data){ $(".array-of-products").html(data) });
								$(this).parents("tr").find(".amt").text(total);
								$(".exchange-panel .rec #"+pid).html(total.toFixed());
								$("#total").val(total.toFixed());
								$("#paid").val(total.toFixed());
							});
							
						});
					</script> <?php
				}
			}
		break;
		
		case "submit-data-for-the-products-exchanged":
			$replaced_prods = explode( "-", $_REQUEST['replacedProds']);
			$returned_prods = explode("-", $_REQUEST['retProds']);
			$curr_date_time = date("Y-m-d H:i:s");
			$bill_id = $_REQUEST['billNum'];
			$total = $_REQUEST['total'];
			$paid = $_REQUEST['paid'];
			$mob = $_REQUEST['cid'];
			$mop = $_REQUEST['modeOfPayment'];
			$tid = "";
			$snAbr = explode(" ",$mop);
			foreach($snAbr as $val){
				$tid .= substr($val,0,1);
			}
			$tid .= date("dmyis");
			for($j=0;$j<count($returned_prods);$j++){
				$returned_prods_details = explode("/", $returned_prods[$j]);
				$sql_sale_ret = "INSERT INTO sales(sale_date, bill_id, customer_id, products, quantity, amount, status)
				VALUES('$curr_date_time','$bill_id','$returned_prods_details[1]','$returned_prods_details[2]','$returned_prods_details[3]','$returned_prods_details[4]','sr')";
				$sql_products = "UPDATE products SET inventory=inventory+$returned_prods_details[3] WHERE pid='$returned_prods_details[2]'" ;
				if($conn->query($sql_sale_ret) == FALSE || $conn->query($sql_products) == FALSE){ error_log($conn->error, 0); echo $conn->error;}
				
			}
			for($i=0;$i<count($replaced_prods);$i++){
				$replaced_prods_details = explode("/", $replaced_prods[$i]);
				$sql_sale = "INSERT INTO sales(sale_date, bill_id, customer_id, products, quantity, discount, amount, status)
				VALUES('$curr_date_time','$bill_id','$mob','$replaced_prods_details[0]','$replaced_prods_details[1]','$replaced_prods_details[2]','$replaced_prods_details[3]','sale')";
				$sql_products = "UPDATE products SET inventory=inventory-$replaced_prods_details[1] WHERE pid='$replaced_prods_details[0]'" ;
				if($conn->query($sql_sale) == FALSE || $conn->query($sql_products) == FALSE){error_log($conn->error, 0); echo $conn->error;}
				else{ echo $sql_products;}
			}
			$sql_cbal = "SELECT balance FROM cash_transactions ORDER BY transaction_date DESC LIMIT 1";
			$result_cbal = $conn->query($sql_cbal);
			if($result_cbal->num_rows>0){ 
				while($row_cbal=$result_cbal->fetch_assoc()){
					$cbal = $row_cbal['balance'];
				}
			}
			else{
				$cbal = 0;
			}
			$sql_bbal = "SELECT bbalance FROM bank_transactions ORDER BY btransaction_date DESC LIMIT 1";
			$result_bbal = $conn->query($sql_bbal);
			if($result_bbal->num_rows>0){ 
				while($row_bbal=$result_bbal->fetch_assoc()){
					$bbal = $row_bbal['bbalance'];
				}
			}
			else{
				$bbal = 0;
			}
			switch($mop){
				case "both":
					$paid_arr = explode(",", $paid);
					$cash = $paid_arr[0];
					$card = $paid_arr[1];
					$tot_paid = $cash + $card;
					if($total < 0){ 
						$cbal -= $cash;
						$bbal -= $card;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid-'$tot_paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','out','$cash','$cbal','$curr_date_time')";
						$sql_bank = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','out','$cash','$bbal','$curr_date_time')";
					}
					else{ 
						$cbal += $cash;
						$bbal += $card;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid+'$tot_paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','in','$cash','$cbal','$curr_date_time')";
						$sql_bank = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','in','$cash','$bbal','$curr_date_time')";
					}
					if($conn->query($sql_cash) == TRUE && $conn->query($sql_bill) == TRUE && $conn->query($sql_bank) == TRUE ) echo "<div class='alert alert-success'>Successful</div>";
					else echo "<div class='alert alert-danger'>".$conn->error."</div>";
				break;
				
				case "cash":
					if($total < 0){ 
						$cbal -= $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid-'$paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','out','$paid','$cbal','$curr_date_time')";
					}
					else{ 
						$cbal += $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid+'$paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','in','$paid','$cbal','$curr_date_time')";
					}
					if($conn->query($sql_cash) == TRUE && $conn->query($sql_bill) == TRUE ) echo "<div class='alert alert-success'>Successful</div>";
					else echo "<div class='alert alert-danger'>".$conn->error."</div>";
				break;
				
				case "card":
					if($total < 0){ 
						$bbal -= $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid-'$paid' WHERE bill_id='$bill_id'";
						$sql_bank = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','out','$paid','$bbal','$curr_date_time')";
					}
					else{ 
						$bbal += $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid+'$paid' WHERE bill_id='$bill_id'";
						$sql_bank = "INSERT INTO bank_transactions (btransaction_id, bcustomer_id, bbill_id, bremarks, btype, bamount, bbalance, btransaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','in','$paid','$bbal','$curr_date_time')";
					}
					if($conn->query($sql_bank) == TRUE && $conn->query($sql_bill) == TRUE ) echo "<div class='alert alert-success'>Successful</div>";
					else echo "<div class='alert alert-danger'>".$conn->error."</div>";
				break;
				
				case "":
					if($total < 0){ 
						$cbal -= $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid-'$paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','out','$paid','$cbal','$curr_date_time')";
					}
					else{ 
						$cbal += $paid;
						$sql_bill = "UPDATE bill_info SET total=total+'$total', paid=paid+'$paid' WHERE bill_id='$bill_id'";
						$sql_cash = "INSERT INTO cash_transactions (transaction_id, customer_id, bill_id, remarks, type, amount, balance, transaction_date)
						VALUES('$tid','$mob','$bill_id','Products Exchanged For Bill Number $bill_id','in','$paid','$cbal','$curr_date_time')";
					}
					if($conn->query($sql_cash) == TRUE && $conn->query($sql_bill) == TRUE ) echo "<div class='alert alert-success'>Successful</div>";
					else echo "<div class='alert alert-danger'>".$conn->error."</div>";
				break; ?>
				<script type="text/javascript">
					$(".save-btn").show();
				</script>
			<?php
			}
		break;
		
		case "load-accrued-and-outstanding-incomes":
			$aoi_sql = "SELECT a.customer_id, SUM(a.total) AS tot, SUM(a.paid) AS pd, b.name FROM bill_info a, customers b WHERE total != paid AND a.customer_id = b.mobile GROUP BY customer_id";
			$aoi_result=$conn->query($aoi_sql);
			if($aoi_result->num_rows>0){ 
				while($aoi_row=$aoi_result->fetch_assoc()){
					$aoi_total = $aoi_row["tot"];
					$aoi_paid = $aoi_row["pd"];
					$aoi_due = $aoi_total - $aoi_paid;
					$aoi_name = $aoi_row['name'];
					$cid = $aoi_row['customer_id'];
					($aoi_due > 0) ? $tr_color = "style='color: #3D843D;'" : $tr_color = "style='color: #D45956;'";
					if(!empty($aoi_due)){	?>
						<tr class="aoi-single-row" id="<?php echo $cid; ?>" <?php echo $tr_color; ?>>
							<td><?php echo $aoi_name; ?></td>
							<td><?php echo $aoi_due; ?></td>
						</tr> <?php 
					}
				}
			}
			else{
				echo "<div class='alert alert-warning'>Oops! Nothing to show!</div>";
			}			?>
			<script>
				$(".aoi-single-row").click(function(){
					var cid = $(this).attr('id');
					$.post("ajax-req-handler.php", {
						key: "fetch-customer-details",
						sendCid: cid
					}, function(resp){
						$.confirm({
							title: "Customer Info", 
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
			</script> <?php
		break;
		
		case "load-inventory-status":
			$is_sql = "SELECT pid, pname, brand_name, psize, inventory FROM `products` WHERE inventory<=1";
			$is_result=$conn->query($is_sql);
			if($is_result->num_rows>0){ 
				while($is_row=$is_result->fetch_assoc()){
					$is_pid = $is_row['pid'];	
					$is_size = $is_row['psize'];	
					$is_inv = $is_row['inventory'];	
					$is_brand = $is_row['brand_name'];	
					$is_name = $is_row['pname'];	?>
					<tr>
						<td><?php echo $is_pid; ?></td>
						<td><?php echo $is_name; ?></td>
						<td><?php echo $is_brand; ?></td>
						<td><?php echo $is_size; ?></td>
						<td><?php echo $is_inv; ?></td>
					</tr> <?php
				}
			}
			else{
				echo "<div class='alert alert-warning'>Oops! Nothing to show!</div>";
			}	
		break;
    }
?>
