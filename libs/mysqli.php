<?php 

    $mysqli = @new mysqli('localhost','php_3477686','3477686','apelburg_base');
	$mysqli->set_charset('utf8');
	
	// if version of PHP lower then PHP 5.2.9 and 5.3.0
	/*if (mysqli_connect_error()) {
		die('Connect Error (' . mysqli_connect_errno() . ') '
				. mysqli_connect_error());
	}
	*/
	if ($mysqli->connect_error) {
		die('Connect Error (' . $mysqli->connect_errno . ') '
				. $mysqli->connect_error);
	}
	
    /*
	// подготовленный запрос
	$query = "SELECT*FROM `".BASE_TBL."` WHERE `size` =?  AND `art`=? ORDER BY ?";
	 
	$stmt = $mysqli->prepare($query) or die($mysqli->error);
	$size = 'small';
	$art = '37Z53218';
	$order ='id';
	
	$stmt->bind_param('sss',$size,$art,$order) or die($mysqli->error); // если тип параметра число - идентификатор i,если тип double идентификатор d 
	$stmt->execute() or die($mysqli->error);
	$result = $stmt->get_result();
	$stmt->close();
	
	if($result->num_rows > 0){
	   while($row = $result->fetch_assoc()){
	      print_r($row);
	   }
	}
	
	*/
	
	
	/*
	// простой запрос
	$query = "SELECT*FROM `".BASE_TBL."` WHERE `size` ='small'  AND `art`='37Z53218' ORDER BY id";
	$result = $mysqli->query($query) or die($mysqli->error);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			print_r($row);
		}
	}
	
	*/


?>