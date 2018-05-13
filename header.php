<?php
	session_start();
	ini_set('display_errors', 1);
	ini_set('log_errors', 1);
	ini_set('error_log', dirname(__FILE__).'/log.txt');
	error_reporting(E_ALL);
	include "database_connection.php";
	date_default_timezone_set("Asia/Calcutta");
	if(!isset($_SESSION['sauname'])){
		echo "<script type='text/javascript'>window.location.href = 'login.php';</script>";
	}
?>
<!DOCTYPE html>
<html>
	<head>
			<script>if (typeof module === 'object') {window.module = module; module = undefined;}</script>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script src="jquery/jquery-3.2.1.min.js"></script>
			<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
			<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
			<link href="style.css" rel="stylesheet">
			<link rel="stylesheet" href="jquery-confirm-master/css/jquery-confirm.css">
			<?php if($_SERVER['PHP_SELF'] == '/SA/settings.php') echo "<link rel='stylesheet' href='bootstrap-toggle/css/bootstrap-toggle.min.css'></script>"; ?>
			<?php if($_SERVER['PHP_SELF'] == '/SA/inventory.php') echo "<script src='babel/babel-1.6.19.min.js'></script>"; ?>
			<title>S.A Fashion Gallery</title>
			<script>if (window.module) module = window.module;</script>
	</head>
	<body>
		<nav class="navbar navbar-inverse">
			<div class="container-fluid">
				<div class="navbar-header">
				</div>
				<ul class="nav navbar-nav">
					<li><a href="index.php">Dashboard</a></li>
					<li><a href="products.php">Products</a></li>
					<li><a href="checkout.php">Sale</a></li>
					<li><a href="inventory.php">Inventory</a></li>
					<li><a href="update_bill.php">Modify Bill</a></li>
					<li><a href="transaction_rec.php">Transactions Record</a></li>
				</ul>
				<ul class="nav navbar-nav navbar-right">
					<li><a href="settings.php" class="settings" data-toggle="tooltip" title="Settings"><span class="fa fa-wrench"></span></a></li>
					<li><a class="search-icon" data-toggle="tooltip" title="Search"><span class="fa fa-search"></span></a></li>
					<li><a class="refresh" data-toggle="tooltip" title="Reload Application"><span class="fa fa-refresh"></span></a></li>
					<li><a href="backup.php" target="_blank" class="backup" data-toggle="tooltip" title="Data Backup"><i class="fa fa-hdd-o" aria-hidden="true"></i></a></li>
					<li><a class="logout" data-toggle="tooltip" title="Logout"><span class="glyphicon glyphicon-log-out"></span> Logout</a></li>
				</ul>
			</div>
		</nav>
		<?php $_SERVER['PHP_SELF']; ?>
		<div class="header-search-bar">
			<div class="form-group">
				<div class="input-group">
					<input type="text" class="form-control search-inp" placeholder="Search">
					<span class="input-group-btn">
						<select class="form-control search-type">
							<option value="p">Product</option>
							<option selected value="c">Customer</option>
						</select>
					</span>
					<div class="live-search-response"></div>
				</div>
			</div>
		</div>
		<script>
			$(document).ready(function(){
				$(".header-search-bar").hide();
				$(".refresh").click(function(){
					location.reload();
				});
				$(".search-icon").click(function(){
					$(".header-search-bar").toggle();
				});
				$(".logout").click(function(){
					$.confirm({
						title: 'Logout',
						content: 'Are you sure you want logout???',
						type: 'red',
						typeAnimated: true,
						buttons: {
							confirm: function () {
								window.location.href = 'logout.php';
							},
							cancel: function () {
							}
						}
					});
				});
				$(".search-inp").keyup(function(){
					if($(".search-type").val() == "p"){
						$.post("ajax-req-handler.php",
						{
							search : $('.search-inp').val(),
							key: "live-rec-search"
						},
						function( data ){
							$('.live-search-response').html( data );
						});
					}
					else{
						$.post("ajax-req-handler.php",
						{
							search : $('.search-inp').val(),
							key: "search-customer-record"
						},
						function( data ){
							$('.live-search-response').html( data );
						});
					}
				});
			});
		</script>
		<div class="container-fluid">
