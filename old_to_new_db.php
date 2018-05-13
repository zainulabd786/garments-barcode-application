<?php include("database_connection.php"); 
	$sql1 = "INSERT INTO stock(stid) VALUES(?)";
	$sql2 = "DELETE FROM stock WHERE stid = ?;";
	$b = "b";
	$a = "a";
	$stmt1 = $conn->prepare($sql1);
	$stmt2 = $conn->prepare($sql2);
	try{
		$conn->query('BEGIN;');
		if($stmt1 == false || $stmt2 == false || $stmt1->bind_param("i", $b) == false || $stmt2->bind_param("i", $a) == false || $stmt1->execute() == false || $stmt2->execute() == false){
			throw new Exception($conn->error);
		}
		else{
			echo "successful";
		}
		$conn->query('COMMIT;');
	}
	catch(Exception $e){
		$conn->query("ROLLBACK;");
		echo $e->getMessage();
	}
	function submit_deposit_form(){
		global $wpdb;
		$slip_no = $_POST['slip'];
		$sid = $_POST['studentId'];
		$cid = $_POST['classId'];
		$from = $_POST['fromDate'];
		$to = $_POST['toDate'];
		$admission_fees = $_POST['admissionFees'];
		$tution_fees = $_POST['tutionFees'];
		$transport_chg = $_POST['transportChg'];
		$annual_chg = $_POST['annualChg'];
		$recreation_chg = $_POST['recreationChg'];
		$total_amount = $admission_fees+$tution_fees+$transport_chg+$annual_chg+$recreation_chg;
		$current_date_time = date("Y-m-d H:i:s");
		$fees_type = "";
		$rec_table = $wpdb->prefix."wpsp_fees_receipts";
		$record_table = $wpdb->prefix."wpsp_fees_payment_record";
		$status_table = $wpdb->prefix."wpsp_fees_status";
		$sql_slip_data = array(
				'slip_no' => $slip_no,
				'sid' => $sid,
				'cid' => $cid,
				'from' => $from,
				'to' => $to,
				'adm' => $admission_fees,
				'ttn' => $tution_fees,
				'trans' => $transport_chg,
				'ann' => $annual_chg,
				'rec' => $recreation_chg
		);
		$sql_record_data = array(
				'tid' => $tid,
				'date_time' => $current_date_time,
				'sid' => $sid,
				'from' => $from,
				'to' => $to,
				'amount' => $total_amount,
				'fees_type' => $fees_type,
		);
		$sql_status_update = "UPDATE $status_table SET admission_fees=admission_fees-'$admission_fees', tution_fees=tution_fees-'$tution_fees', transport_chg=transport_chg-'$transport_chg', annual_chg=annual_chg-'$annual_chg', recreation_chg=recreation_chg-'$recreation_chg' WHERE sid = '$sid' ";
		if(!empty($admission_fees)) $fees_type .= "adm";
		if(!empty($tution_fees)) $fees_type .= "/ttn";
		if(!empty($transport_chg)) $fees_type .= "/trn";
		if(!empty($annual_chg)) $fees_type .= "/ann";
		if(!empty($recreation_chg)) $fees_type .= "/rec";

		try{
			$wpdb->query("BEGIN;");

			if( $wpdb->insert($rec_table, $sql_slip_data) && $wpdb->insert($record_table, $sql_record_data) && $wpdb->query($sql_status_update) ){
				echo "success";
			}
			else{
				throw new Exception($db->print_error());
			}

			$wpdb->query("COMMIT;");
		}
		catch(Exception $e){
			$wpdb->query("ROLLBACK;");
			echo "error";
		}

		wp_die();
	}
?>
