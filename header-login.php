<?php
	session_start();
	include "database_connection.php";
	date_default_timezone_set("Asia/Calcutta");
?>
<!DOCTYPE html>
<html>
	<head>
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<script src="jquery/jquery-3.2.1.min.js"></script>
			<link rel="stylesheet" href="font-awesome-4.7.0/css/font-awesome.min.css">
			<link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
			<link href="style.css" rel="stylesheet">
			<link rel="stylesheet" href="jquery-confirm-master/css/jquery-confirm.css">
			<script src="jquery-confirm-master/js/jquery-confirm.min.js"></script>
			<script src="jQuery-Print/jQuery.print.js"></script>
			<script src="bootstrap/js/bootstrap.min.js"></script>
			<script src="Chart.min.js"></script>
			<title>S.A Fashion Gallery</title>
	</head>
	<body class="body" onload="document.getElementById('id01').style.display='block'">
		<div class="container-fluid">