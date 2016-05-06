<?php
//header('Content-type: text/html; charset=utf-8');
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

/*
окончательный импорт завершен!!!!
пользоваться файлом опасно ввиду возможной потери введённой в новую ОС информации
*/

//exit('окончательный импорт завершен!!!! пользоваться файлом опасно ввиду возможной потери введённой в новую ОС информации');



include_once('./libs/config.php');
include_once('../libs/mysqli.php');
// include_once('../libs/mysql.php');

// aplStdClass
include_once ROOT.'/../libs/php/classes/aplStdClass.php';

/**
* update
*/
class Update extends aplStdAJAXMethod
{
	
	public function __construct() {
		// подключение к базе
		$this->db();

//		$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;
//		$this->user_access = $this->get_user_access_Database_Int($this->user_id);

		// получаем информацию по пользователю
		$this->user = $this->aplStdUser();	

 		// слушаем POST массив
		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

		// слушаем GET массив
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		}


		## данные POST
		if(isset($_POST['AJAX'])){
			// получаем данные пользователя
			$User = $this->user;
			
//			$this->user_last_name = $User['last_name'];
//			$this->user_name = $User['name'];

			$this->_AJAX_($_POST['AJAX']);
		}


	}

	//////////////////
	//	AJAX
	/////////////////

		/**
		  *	удаление таблиц клиента
		  */
		protected function drop_old_client_tbl_AJAX(){
			return $this->returnF();
			// удаляем таблицу № 1
			$query = "DROP TABLE os__contact_information";
			$result = $this->mysqli->query($query);
			
			if($result){
				// сообщение
				$html = 'Таблица os__contact_information удалена';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Таблица os__contact_information НЕ была удалена';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}

			// удаляем таблицу № 2
			$query = "DROP TABLE os__addres_tbl";
			$result = $this->mysqli->query($query);;
			
			if($result){
				// сообщение
				$html = 'Таблица os__addres_tbl удалена';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Таблица os__addres_tbl НЕ была удалена';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}
		}	

		/**
		  *	создание новых таблиц клиента
		  */
		protected function create_client_tbl_AJAX(){
			return $this->returnF();
			//  № 1
			$query = "
				CREATE TABLE `os__contact_information` (
				  `id` int(11) AUTO_INCREMENT,
				  `parent_id` int(11) ,
				  `table` varchar(255) ,
				  `type` varchar(255) ,
				  `telephone_type` varchar(255) ,
				  `contact` varchar(255) ,
				  `dop_phone` varchar(255) ,
				  PRIMARY KEY (`id`)
				) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			$result = $this->mysqli->query($query);
			
			if($result){
				// сообщение
				$html = 'Таблица os__contact_information создана';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Таблица os__contact_information НЕ была создана';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}

			//  № 2
			$query = "
				CREATE TABLE `os__addres_tbl` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `parent_id` int(11) NOT NULL,
			  `table_name` varchar(255) NOT NULL,
			  `adress_type` varchar(255) NOT NULL,
			  `city` varchar(255) NOT NULL,
			  `street` varchar(255) NOT NULL,
			  `house_number` int(11) NOT NULL,
			  `korpus` int(11) NOT NULL,
			  `office` int(11) NOT NULL,
			  `liter` varchar(11) NOT NULL,
			  `bilding` varchar(11) NOT NULL,
			  `postal_code` int(11) NOT NULL,
			  `note` varchar(255) NOT NULL,
			  UNIQUE KEY `id` (`id`)
			)  ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";
			$result = $this->mysqli->query($query);
			
			if($result){
				// сообщение
				$html = 'Таблица os__addres_tbl создана';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Таблица os__addres_tbl НЕ была создана';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}
		}

		/**
		  *	копируем данные из таблицы CLINTS_TBL
		  */
		function copy_client_contact_info_AJAX(){
			return $this->returnF();
			//получаем данные из основной таблицы
			$query = "SELECT * FROM `".CLIENTS_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			/*
			echo '<pre>';
			print_r($arr);
			echo '<pre>';
			*/
			$i = 0;
			$r = 0;
			$num_str = 0;
			$query = $query2= $q = "";
			foreach($arr as $k=>$v){
				if(trim($v['email'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['phone'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['web_site'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','web_site','','".addslashes($v['web_site'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'CLIENTS_TBL','web_site','','".addslashes($v['web_site'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
			}

			// sleep(2);
			//echo $q."<br>";
			$result = $this->mysqli->multi_query($q);//сохраняем выборку из 1 таблицы
			

			// $result = $this->mysqli->query($q);
			
			if($result){
				// сообщение
				$html = "Из общей таблицы добавлено ".$num_str." строк<br>";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Данные не скопированы';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}
		}

		function returnF(){
			$html = "В целях безопасности данное действие запрещено";
			$this->responseClass->addMessage($html,'error_message');
		}

		/**
		  *	копируем адреса клиентов
		  */
		function copy_client_addres_AJAX(){
			return $this->returnF();
			//получаем данные из основной таблицы
			$query = "SELECT `id`,`addres`,`delivery_address` FROM `".CLIENTS_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			if($result){
				// сообщение
				$html = "Данные успешно получены";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}

			$i = 0;
			$query = "";
			foreach ($arr as $key => $value) {
				if($i == 0){
					if(trim($value['delivery_address']) != '' || trim($value['addres']) != ''){
						$query .= "INSERT INTO `os__addres_tbl` VALUES ('','".$value['id']."','CLIENTS_TBL','office','','".addslashes($value['addres'])."','','','','','','',''), 
																			  ('','".$value['id']."','CLIENTS_TBL','delivery','','".addslashes($value['delivery_address'])."','','','','','','','')";
						$i++;	
					}
					
				}else{
					if(trim($value['delivery_address']) != '' || trim($value['addres']) != ''){
						$query .=",('','".$value['id']."','CLIENTS_TBL','office','','".addslashes($value['addres'])."','','','','','','',''), ('','".$value['id']."','CLIENTS_TBL','delivery','','".addslashes($value['delivery_address'])."','','','','','','','')";
						$i++;
					}
				}			
			}
			$query .= ";";



			//импортируем
			// $result = $this->mysqli->query($query);
			$result = $this->mysqli->multi_query($query);
			if($result){
				// сообщение
				$html = "Копирование завершено";
				$html .= '<br>';
				$html .= 'Было скопировано '.$i.' строк';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}			
		}

		protected function copy_client_contact_info_contact_face_AJAX(){
			return $this->returnF();
			//получаем данные из основной таблицы
			$query = "SELECT * FROM `".CLIENT_CONT_FACES_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			/*
			echo '<pre>';
			print_r($arr);
			echo '<pre>';
			*/
			$i = 0;
			$r = 0;
			$num_str = 0;
			$query = $query2 = $q = "";
			foreach($arr as $v){
				if(trim($v['email'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'CLIENT_CONT_FACES_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['phone'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'CLIENTS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'CLIENT_CONT_FACES_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
			}

			//echo $q."<br>";
			$result = $this->mysqli->multi_query($q);//сохраняем выборку из 1 таблицы
			if($result){
				// сообщение
				$html = "Копирование завершено";
				$html .= '<br>';
				$html .= "Из таблицы ".CLIENT_CONT_FACES_TBL." добавлено ".$num_str." строк";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}	
			// echo "<br>";
		}




		/**
		  *	Скопировать данные (site, phone, email) из основной таблицы по поставщикам
		  */
		protected function copy_supplier_contact_info_AJAX(){
			//получаем данные из основной таблицы
			$query = "SELECT * FROM `".SUPPLIERS_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			/*
			echo '<pre>';
			print_r($arr);
			echo '<pre>';
			*/
			$i = 0;
			$r = 0;
			$num_str = 0;
			$query = $query2= $q = "";
			foreach($arr as $k=>$v){
				if(trim($v['email'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['phone'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['web_site'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','web_site','','".addslashes($v['web_site'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_TBL','web_site','','".addslashes($v['web_site'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
			}

			// sleep(2);
			//echo $q."<br>";
			$result = $this->mysqli->multi_query($q);//сохраняем выборку из 1 таблицы
			

			// $result = $this->mysqli->query($q);
			
			if($result){
				// сообщение
				$html = "Из общей таблицы добавлено ".$num_str." строк<br>";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = 'Данные не скопированы';
				$html .= '<br>';
				$html .= ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}
		}

		/**
		 * удаление информации по поставщикам из новой ОС
		 */
		protected function delete_supplier_contact_info_AJAX(){
			// (1) удаление адреса поставщиков
			$query = "SELECT * FROM os__addres_tbl where `table_name` = 'SUPPLIERS_TBL'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$arr = [];
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row['id'];
				}
			}

			$query = "DELETE FROM os__addres_tbl WHERE id IN ('".implode("','",$arr)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);


			// (2) удаляем данные (site, phone, email) по контактным лицам поставщиков
			$query = "SELECT * FROM  `os__contact_information` where `table` = 'SUPPLIERS_TBL' OR  `table` = 'SUPPLIERS_CONT_FACES_TBL'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			$arr = [];
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row['id'];
				}
			}
//			$this->responseClass->addSimpleWindow($query.''.$this->printArr($arr));

			$query = "DELETE FROM os__contact_information WHERE id IN ('".implode("','",$arr)."')";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
		}

		/**
		  *	Скопировать адреса поставщиков в новую структуру
		  */
		protected function copy_supplier_addres_AJAX(){
			//получаем данные из основной таблицы
			$query = "SELECT `id`,`addres` FROM `".SUPPLIERS_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			if($result){
				// сообщение
				$html = "Данные успешно получены";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}

			$i = 0;
			$query = "";
			foreach ($arr as $key => $value) {
				if($i == 0){
					if(trim($value['addres']) != ''){
						$query .= "INSERT INTO `os__addres_tbl` VALUES ('','".$value['id']."','SUPPLIERS_TBL','office','','".addslashes($value['addres'])."','','','','','','','')";
						$i++;	
					}
					
				}else{
					if(trim($value['addres']) != ''){
						$query .=",('','".$value['id']."','SUPPLIERS_TBL','office','','".addslashes($value['addres'])."','','','','','','','')";
						$i++;
					}
				}			
			}
			$query .= ";";



			//импортируем
			// $result = $this->mysqli->query($query);
			$result = $this->mysqli->multi_query($query);
			if($result){
				// сообщение
				$html = "Копирование завершено";
				$html .= '<br>';
				$html .= 'Было скопировано '.$i.' строк';
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}	
		}

		/**
		  *	Скопировать данные (site, phone, email) по контактным лицам поставщиков
		  */
		protected function copy_supplier_contact_info_contact_face_AJAX(){
			//получаем данные из основной таблицы
			$query = "SELECT * FROM `".SUPPLIERS_CONT_FACES_TBL."`";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			/*
			echo '<pre>';
			print_r($arr);
			echo '<pre>';
			*/
			$i = 0;
			$r = 0;
			$num_str = 0;
			$query = $query2 = $q = "";
			foreach($arr as $v){
				if(trim($v['email'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','email','','".$v['email']."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_CONT_FACES_TBL','email','','".$v['email']."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
				if(trim($v['phone'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','phone','work','".addslashes($v['phone'])."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_CONT_FACES_TBL','phone','work','".addslashes($v['phone'])."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}

				if(trim($v['email'])!=""){
					if($i==0){
						$q .= ($r==1)?';':'';
						$q .= "INSERT INTO `os__contact_information` VALUES ('',".$v['id'].",'SUPPLIERS_TBL','skype','','".$v['isq_skype']."','')";$i++;$num_str++;
					}else{
						$q .= ",('',".$v['id'].",'SUPPLIERS_CONT_FACES_TBL','email','','".$v['isq_skype']."','')";	$i++;$num_str++;
						if($i>500){$i=0;$r=1;}
					}
				}
			}

			//echo $q."<br>";
			$result = $this->mysqli->multi_query($q);//сохраняем выборку из 1 таблицы
			if($result){
				// сообщение
				$html = "Копирование завершено";
				$html .= '<br>';
				$html .= "Из таблицы ".CLIENT_CONT_FACES_TBL." добавлено ".$num_str." строк";
				$this->responseClass->addMessage($html,'system_message');
			}

			if($this->mysqli->errno){
				// сообщение
				$html = ' Error (' . $this->mysqli->errno . ') ' . $this->mysqli->error;
				$this->responseClass->addMessage($html,'error_message');
			}	
		}
}

// инициализируем класс Update
$UPDATE = new Update;


// перерасчет рейтинга клиентов
function rebuild_rate(){
	global $mysqli;
	$query = "SELECT * FROM `".CLIENTS_TBL."`";
	$result = $mysqli->query($query) or die($mysqli->error);
	if($result->num_rows > 0){
		while($row = $result->fetch_assoc()){
			$arr[] = $row;
		}
	}
	$i1=$i2=$i3=$i4= 1 ;
	$s1=$s2=$s3=$s3="";

	foreach ($arr as $key => $val) {
		switch ($val['rate']) {
			case '0':
				if($i1>1){$s1 .=",";}
				# code...
				$s1 .= "'".$val['id']."'";
				$i1++;
				break;
			case '1':
				if($i2>1){$s2.=",";}
				# code...
				$s2 .= "'".$val['id']."'";
				$i2++;
				break;
			case '2':
				if($i3>1){$s3.=",";}
				# code...
				$s3 .= "'".$val['id']."'";
				$i3++;
				break;
			case '3':
				if($i4>1){$s4.=",";}
				# code...
				$s4 .= "'".$val['id']."'";
				$i4++;
				break;			
			default:
				if($i1>1){$s1.=",";}
				# code...
				$s1 .= "'".$val['id']."'";
				$i1++;
				break;
		}
		


	}
	$q = "
		UPDATE `os__client_list` SET `rate` = '0' WHERE `id` IN (".$s1.");
		UPDATE `os__client_list` SET `rate` = '1' WHERE `id` IN (".$s4.");
		UPDATE `os__client_list` SET `rate` = '3' WHERE `id` IN (".$s3.");
		UPDATE `os__client_list` SET `rate` = '5' WHERE `id` IN (".$s2.");
		";
		// echo $query;
		$result = $mysqli->multi_query($q) or die($mysqli->error);//сохраняем выборку из 1 таблицы
		if($result){
			echo "Ребилд таблицы произведён успешно";
		}
}
?>

<!DOCTYPE html>
<html>
<head>
	
	<meta charset="UTF-8" />    
	<link href="./skins/css/styles.css" rel="stylesheet" type="text/css">
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
	<link href="./skins/css/styles_sample.css" rel="stylesheet" type="text/css">
	<script type="text/javascript" src="libs/js/jquery.1.10.2.min.js"></script>
	<script type="text/javascript" src="libs/js/jquery_ui/jquery-ui.min.js"></script>
	<script type="text/javascript" src="libs/js/classes/Base64Class.js"></script>
	<link href="libs/js/jquery_ui/jquery-ui.theme.css" rel="stylesheet" type="text/css">
	<link href="libs/js/jquery_ui/jquery-ui.structure.css" rel="stylesheet" type="text/css">
	<link href="skins/css/main.css" rel="stylesheet" type="text/css">

	
	<script type="text/javascript" src="<?=HOST;?>/libs/js/standard_response_handler.js"></script>
	<script type="text/javascript" src="update.js"></script>
	<title>Экспорт данных в новую ОС</title>
	<style type="text/css">
	#main{
		padding: 15px;
	}
	.command_name{
		padding: 5px 0;
	}
	.steep_contaner {
		margin-top: 15px;
	    padding: 15px;
	    border: 1px solid #B7B5B5;
	}

	</style>
</head>
<body>
<div id="apl-notification_center"></div>
<div id="main">
	
	<div class="steep_contaner">
		<div class="command_name">Удаление стырых таблиц по клиенту</div>
		<button id="drop_client_tbl" value="1">удалить</button>
	</div>	

	<div class="steep_contaner">
		<div class="command_name">Создание новых таблиц по клиенту</div>
		<button id="create_client_tbl" value="1">создать</button>
	</div>	
	<div class="steep_contaner">
		<div class="command_name"><strong>Импорт общей информации по клиенту</strong></div>
		
	</div>

	<div class="steep_contaner">
		<div class="command_name">Скопировать данные (site, phone, email) из основной таблицы по клиентам</div>
		<button id="copy_client_contact_info" value="1">скопировать</button>
	</div>	


	<div class="steep_contaner">
		<div class="command_name">Скопировать адреса клиентов в новую структуру</div>
		<button id="copy_client_addres" value="1">скопировать</button>
	</div>	
	<div class="steep_contaner">
		<div class="command_name"><strong>Импорт информации по контактным лицам</strong></div>		
	</div>

	<div class="steep_contaner">
		<div class="command_name">Скопировать данные (site, phone, email) по контактным лицам</div>
		<button id="copy_client_contact_info_contact_face" value="1">скопировать</button>
	</div>

	<div class="steep_contaner">
		<div class="command_name"><strong style="color:red">УДАЛЕНИЕ из новой ОС информации по поставщикам</strong></div>
		<button id="delete_supplier_contact_info" value="1">удалить</button>
	</div>

	<div class="steep_contaner">
		<div class="command_name"><strong>Импорт общей информации по поставщикам</strong></div>
	</div>

	<div class="steep_contaner">
		<div class="command_name">Скопировать данные (site, phone, email) из основной таблицы по поставщикам</div>
		<button id="copy_supplier_contact_info" value="1">скопировать</button>
	</div>	

	<div class="steep_contaner">
		<div class="command_name">Скопировать адреса поставщиков в новую структуру</div>
		<button id="copy_supplier_addres" value="1">скопировать</button>
	</div>	
	<div class="steep_contaner">
		<div class="command_name"><strong>Импорт информации по контактным лицам поставщиков</strong></div>		
	</div>

	<div class="steep_contaner">
		<div class="command_name">Скопировать данные (site, phone, email) по контактным лицам поставщиков</div>
		<button id="copy_supplier_contact_info_contact_face" value="1">скопировать</button>
	</div>	

</div>


<?php
	
	echo '<pre>';
	print_r($UPDATE);
	echo '</pre>';
?>
</body>
</html>