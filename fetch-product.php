<?php
    include "database_connection.php";
    $key = $_REQUEST['key'];
    switch($key){
        case "fetch-products-details":
            $pid = $_REQUEST['sendPid'];
            $sql = "SELECT * FROM products WHERE pid = '$pid'";
            $result = $conn->query($sql); ?>
            <center>
            <table class="product-details">
            <?php
            if(num_rows($result)>0){ 
                while($row=$result->fetch_assoc()){
                    $pid = $row['pid'];
                    $name = $row['pname'];
                    $brand_name = $row['brand_name'];
                    $size = $row['psize'];
                    $sprice = $row['sprice'];
                    $bprice = $row['bprice'];
                    $sgst = $row['sgst'];
                    $bgst = $row['bgst'];
					$category = $row['ptype'];
					switch($category){
						case "c":
							$category = "Clothing";
						break;
						case "f":
							$category = "Footwear";
						break;
						case "h":
							$category = "Handbag";
						break;
					}
					$sorig_price = round(($sprice)/((1)+(($sgst)/(100))));
					$borig_price = round(($bprice)/((1)+(($bgst)/(100))));
                    $stotal = $sprice;
                    $btotal = $bprice;
                    $inventory = $row['inventory']; ?>
                    <div class="status-msg"></div>
                    <input type="hidden" value="<?php echo $pid; ?>" id="ppid">
                    <tr style="border-bottom: 1px dotted;">
                        <td>In Stock<br/><input type='text' value='<?php echo $inventory; ?>' id="pinv" readonly></td>
                        <td>Brand Name<br/><input type='text' value='<?php echo $brand_name; ?>' id="pbname" readonly></td>
                        <td>Category<br/>
							<select type='text' value='<?php echo $category; ?>' id="pptype" readonly>
								<option <?php if($category=='Clothing') echo "selected"; ?> value="c">Clothing</option>
								<option <?php if($category=='Footwear') echo "selected"; ?> value="f">Footwear</option>
								<option <?php if($category=='Handbag') echo "selected"; ?> value="h">Handbag</option>
							</select>
						</td>
                        <td style="padding:0px;">
							<button type="button" style="margin-top:20px;height:44px;" class="get-barcode btn btn-success"><i class="fa fa-barcode" aria-hidden="true"></i> Reprint Barcode</button>
							<button type="button" style="margin-top:20px;height:44px;" class="delete-product btn btn-danger"><i class="fa fa-trash-o" aria-hidden="true"></i> Delete</button>
						</td>
                    </tr>
                    <tr>
                        <td>Product Name</td>
                        <td><input type='text' value='<?php echo $name; ?>' id="ppname" readonly></td>
                        <td>Size</td>
                        <td><input type='text' value='<?php echo $size; ?>' id="ppsize"  readonly></td>
                    </tr>
                    <tr>
                        <td>Selling Price exclusive of Tax</td>
                        <td><input type='text' value='<?php echo $sorig_price; ?>' disabled></td>
                        <td>Buying Price exclusive of Tax</td>
                        <td><input type='text' value='<?php echo $borig_price; ?>' disabled></td>
                    </tr>
                    <tr style="padding: 0;text-align: center;">
                        <td style="padding: 0;font-size:30px" colspan=2>+</td>
                        <td style="padding: 0;font-size:30px" colspan=2>+</td>
                    </tr>
                    <tr class="alert alert-danger">
                        <td>GST(&#8377;<?php echo round(($sgst)*($sorig_price)/(100)); ?>)</td>
                        <td><input type='text' value='<?php echo $sgst; ?>%' id="psgst" readonly></td>
                        <td>GST(&#8377;<?php echo round(($bgst)*($borig_price)/(100)); ?>)</td>
                        <td><input type='text' value='<?php echo $bgst; ?>%' id="pbgst" readonly></td>
                    </tr>
                    <tr class="alert alert-success">
                        <td>Selling Price</td>
                        <td><input type='text' value='<?php echo $stotal; ?>' id="psprice" readonly></td>
                        <td>Buying Price</td>
                        <td><input type='text' value='<?php echo $btotal; ?>'  id="pbprice" readonly></td>
                    </tr>
                    <tr>
                        <td colspan=4><button type="button" class="btn btn-primary btn-block edit-btn">Edit</button></td>
                        <td colspan=4><button type="button" class="btn btn-success btn-block done-btn">Done</button></td>
                    </tr> <?php
                }
            } ?>
            <script>
				$(".delete-product").click(function(){
					$.confirm({
						title: 'Delete',
						content: 'Are you sure you want to Delete This Product? You cannot undo this action',
						type: 'red',
						typeAnimated: true,
						buttons: {
							confirm: function () {
								$.post("ajax-req-handler.php", {
									key: "Delete-Product",
									id: $("#ppid").val()
								}, function(resp){
									$.confirm({
										title: 'Product Successfully Deleted', 
										type: 'green',
										typeAnimated: true,
										columnClass: 'col-md-8 col-md-offset-2',
										buttons: {
											OK: {
												text: 'OK',
												action: function(){
													location.reload();
												}
											}
										},
										content: resp,
										contentLoaded: function(data, status, xhr){
											this.setContentAppend('<br>Status: ' + status);
										}
									});
								});
							},
							cancel: function () {
							}
						}
					});
				});
                $(".done-btn").hide();
                $(".edit-btn").click(function(){
                    $(".product-details :input").removeAttr("readonly");
                    $(".done-btn").show();
                    $(".edit-btn").hide();
                });
                $(".get-barcode").click(function(){
                    $.post("ajax-req-handler.php",
                    {
                        key: "reprint-barcode",
                        pid: $("#ppid").val(),
						pname: $('#ppname').val(),
                        psize: $('#ppsize').val(),
                        sprice: $('#psprice').val(),
                        brand: $('#pbname').val()
                    },
                    function( resp ){
                       $.print( resp );
                    });
                });
                $(".done-btn").click(function(){
                    $(".edit-btn").show();
                    $(".done-btn").hide();
                    $(".product-details :input").attr('readonly', true);
                    $.post("ajax-req-handler.php",
                    {
                        key: "update-product-details",
                        pid: $("#ppid").val(),
                        pname: $('#ppname').val(),
                        psize: $('#ppsize').val(),
                        sprice: $('#psprice').val(),
                        bprice: $('#pbprice').val(),
                        sgst: $('#psgst').val(),
                        bgst: $('#pbgst').val(),
                        inventory: $('#pinv').val(),
                        ptype: $('#pptype').val(),
                        brand: $('#pbname').val()
                    },
                    function( data ){
                        $(".status-msg").html( data );
                    });
					console.log($('#ptype').val());
                });
            </script>
            </table>
            </center> <?php
        break;
    }
?>
