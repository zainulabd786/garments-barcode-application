<?php include "header.php"; ?>
<div class="settings-page">
	<div class="tab">
	 	<button class="tablinks" onclick="openTab(event, 'sms')" id="defaultOpen">SMS settings</button>
	  	<button class="tablinks" onclick="openTab(event, 'general')">General Settings</button>
	</div>

	<div id="sms" class="tabcontent">
	  	<h3>SMS Settings</h3>
	  	<div class="well num-of-sms-credit"></div>
	  	<form class="form-vertical sms-settings-form">
	  		<div class="form-group">
	  			<div data-toggle="tooltip" title="SMS to the customer at the time of sale." class="form-group">
                    <label for="sale-sms-status">Sale SMS</label>
                    <input type="checkbox" id="sale-sms" data-toggle="toggle" data-on="Send" data-off="Don't Send">
                </div>
	  			<textarea data-toggle="tooltip" class="form-control" id="sale-sms-content"></textarea>
	  			<div class="sms-count"></div>
	  		</div>

	  		<button type="button" class="btn btn-success sms-settings-btn">Save Settings</button>
	  	</form>
	</div>

	<div id="general" class="tabcontent">
	  	<h3>General Settings</h3>
	</div>

</div>
<div class="exec-ajax-script"></div>
<script>
	$(document).ready(function(){
		$.ajax( {
			 url: 'ajax-req-handler.php',
			 data: { key: 'load-saved-settings' },
			 dataType: 'json'
		} ).done( function( data ) {
			if( data.send_sms == 1 ) {
				$(".sms-settings-form #sale-sms").attr('checked', true);
				$(".toggle").addClass('btn-primary');
				$(".toggle").removeClass('btn-default');
				$(".toggle").removeClass('off');
			}
			else{
				$(".sms-settings-form textarea").attr("disabled", true);
			}
			$(".sms-settings-form textarea").val( data.textarea );
			$(".sms-count").text("Length: "+data.smsLength+", SMS: "+data.numSms+"(approx)");
			$(".num-of-sms-credit").html("Number Of SMS Left: <b>"+data.smsLeft+"</b>" );
		} );

		$(".sms-settings-form textarea").hover(function(){
			$(this).attr("title", "Customer Name: [name] \n Customer mobile: [mobile] \n Total Amount: [total] \n Paid Amount: [paid] \n Due Amount: [due] \n Bill Number: [bill] \n Date: [date]");
		});

		$(".sms-settings-btn").click(function(){
			if($("#sale-sms").prop("checked") == false){
				var status = 0;
			}
			else{
				var status = 1;
			}
			$.post("ajax-req-handler.php", {
				key: "save-sms-settings",
				content: $("#sale-sms-content").val(),
				status: status
			}, function(data){
				$.alert(data);
			});
		});
		$("#sale-sms").change(function(){
			if($(this).prop("checked") == false){
				$(".sms-settings-form textarea").attr("disabled", true);
			}
			else{
				$(".sms-settings-form textarea").attr("disabled", false);
			}
		});

		$("#sale-sms-content").keyup(function(){
			var smsLength = $(this).val().length;
			var sms = smsLength / 150;
			$(".sms-count").text("Length: "+smsLength+", SMS: "+Math.ceil(sms)+"(approx)");
		});

	});

	

	function openTab(evt, cityName) {
	    var i, tabcontent, tablinks;
	    tabcontent = document.getElementsByClassName("tabcontent");
	    for (i = 0; i < tabcontent.length; i++) {
	        tabcontent[i].style.display = "none";
	    }
	    tablinks = document.getElementsByClassName("tablinks");
	    for (i = 0; i < tablinks.length; i++) {
	        tablinks[i].className = tablinks[i].className.replace(" active", "");
	    }
	    document.getElementById(cityName).style.display = "block";
	    evt.currentTarget.className += " active";
	}

// Get the element with id="defaultOpen" and click on it
document.getElementById("defaultOpen").click();
</script>
<?php include "footer.php"; ?>