<?php
	class clientSearch  extends aplStdAJAXMethod{

		function __construct(){
			
			$this->user_id = isset($_SESSION['access']['user_id'])?$_SESSION['access']['user_id']:0;

			$this->user_access = $this->get_user_access_Database_Int($this->user_id);
		


		if(isset($_POST['AJAX'])){
			$this->_AJAX_($_POST['AJAX']);
		}

			## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
		if(isset($_GET['AJAX'])){
			$this->_AJAX_($_GET['AJAX']);
		
		}
	}

		/**
		  *	 client search
		  *
		  *	@author  	Alexey Kapitonov
		  *	@version 	00:56 23.01.2016
		  */
		protected function shearch_client_autocomlete_AJAX(){
					global $mysqli;
					$query="SELECT * FROM `".CLIENTS_TBL."`  WHERE `company` LIKE '%".$_POST['search']."%'";
					$result = $mysqli->query($query)or die($mysqli->error);
					$response = array(); 

					$i=0;
					if($result->num_rows > 0){
						while($row = $result->fetch_assoc()){
							// $response[] = $row['company'];
							$response[$i]['label'] = $row['company'];
							$response[$i]['value'] = $row['company'];
							$response[$i]['href'] = $_SERVER['REQUEST_URI'].'&client_id='.$row['id'];
							$response[$i++]['desc'] = $row['id'];
						}
					}					
					echo json_encode($response);
					exit;
				}
				
		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
		private function get_user_access_Database_Int($id){
			global $mysqli;
			$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $mysqli->query($query) or die($mysqli->error);				
			$int = 0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$int = (int)$row['access'];
				}
			}
			//echo $query;
			return $int;
		}
	}

?>