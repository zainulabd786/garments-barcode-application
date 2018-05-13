<?php
	$servername = "localhost";
	$user = "zainulabd786";
	$pass = "123456";
	$dbname = "safg";
	$conn = new mysqli($servername, $user, $pass, $dbname);
	if($conn->connect_error){
		die("connection failed!".$conn->connect_error);
	}

	/*class database extends SQLite3{
    	function __construct(){
			$db_file = "data/safg.sqlite3";
    		$this->open($db_file);
    	}
    }
    $conn = new database();
    $conn->busyTimeout(5000);*/
    function num_rows( $result ){
    	/*$nrows = 0;
		$result->reset();
		while ($result->fetchArray())
		    $nrows++;
		$result->reset();*/
		$nrows = $result->num_rows;
		return $nrows;
    }
?>
