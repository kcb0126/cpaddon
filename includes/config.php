<?php
	$db_host = 'localhost';
	$db_user = 'majjana_cpusr';
	$db_pass = 'uFh}aI5?h}Lw';
	$db_name = 'majjana_cpaddon';
	
	$mysqli = new mysqli($db_host, $db_user, $db_pass,$db_name);
	
	/* check connection */
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
	
	/* change character set to utf8 */
	if (!$mysqli->set_charset("utf8")) {
	    printf("Error loading character set utf8: %s\n", $mysqli->error);
	}
	
	function get_rows($table_and_query) {
		global $mysqli;
		$total = $mysqli->query("SELECT * FROM $table_and_query");
		$total = $total->num_rows;
		return $total;
	}
	
	function get_user_details($user_name) {
		global $mysqli;
		$log_user = $mysqli->query("SELECT * FROM tbl_user WHERE user_name='$user_name'");
		$detail = $log_user->fetch_object();
		return $detail;
	}

	function get_addondet($addID) {
		global $mysqli;
		$getaddon = $mysqli->query("SELECT * FROM `tbl_addondom` WHERE `id`='$addID'");
		$addonDet = $getaddon->fetch_object();
		return $addonDet;
	}
	
	function get_file($fileID) {
		global $mysqli;
		$getFile= $mysqli->query("SELECT * FROM `tbl_files` WHERE `id`='$fileID'");
		$fileDet = $getFile->fetch_object();
		return $fileDet;
	}
?>