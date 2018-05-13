<?php include "header-login.php"; ?>

<div id="id01" class="modal">
  
	<form class="modal-content animate" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']);?>">
		<div class="imgcontainer">
			<span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
			<img src="images/img_avatar.png" alt="Avatar" class="avatar img-fluid">
		</div>

		<div class="container">
			<label><b>Username</b></label>
			<input type="text" placeholder="Enter Username" name="sauname" id = "login-uname" required>

			  <label><b>Password</b></label>
			  <input type="password" placeholder="Enter Password" name="sapass" id = "login-pass" required>
				
			  <button type="submit">Login</button>
			  <input type="checkbox" checked="checked"> Remember me
		</div>

		<div class="container" style="background-color:#f1f1f1">
		  <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
		  <span class="psw">Forgot <a href="#">password?</a></span>
		</div>
	</form>
	
</div>

<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}
</script>

<?php
if($_SERVER['REQUEST_METHOD']=="POST"){
	$auname=$_POST["sauname"];
	$apsw=$_POST["sapass"];
	if($auname=="admin" && $apsw=="123456"){
		$_SESSION["sauname"]=$auname;
		echo "<script type='text/javascript'>window.location.href = 'index.php';</script>";
	}
	else{ ?>
		<script>
			$.confirm({
				title: 'Error',
				content: 'oops! Invalid usename or password.',
				type: 'red',
				typeAnimated: true,
				buttons: {
					TryAgain: function () {
					}
				}
			});
		</script>		<?php
	}
}
?>
<?php include "footer.php"; ?>
