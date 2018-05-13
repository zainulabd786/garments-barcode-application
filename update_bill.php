<?php include "header.php"; ?>
<form class="bill-no-to-mod">
  <div class="input-group">
    <input type="text" class="form-control bill-input" placeholder="Enter bill Number">
    <div class="input-group-btn">
		<button style="height:52px;margin-top:-10px;" class="btn btn-default" type="button">
			<i class="glyphicon glyphicon-search"></i> Search
		</button>
    </div>
	<div class="suggestions-container"></div>
  </div>
</form>
<div class="update-array-container"></div>
<script>
var updateArray = [];
$(document).ready(function(){
	$(".bill-input").keyup(function(){
		if($(this).val() == ""){
			$(".suggestions-container").hide();
		}
		else{
			$(".suggestions-container").show();
		}
		$.post("ajax-req-handler.php", {
			key: "suggest-bill-numbers",
			value: $(this).val()
		}, function(data){
			$(".suggestions-container").html(data);
		});
	});
});
</script>
<?php include "footer.php"; ?>