<?php
	class clientSearch  extends aplStdAJAXMethod{

		function __construct(){
			$this->db();
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

			$query="SELECT * FROM `".CLIENTS_TBL."`  WHERE `company` LIKE ?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$search = '%'.$_POST['search'].'%';
			$stmt->bind_param('s', $search) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();

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
		/**
		 *	 supplier search
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	00:56 23.01.2016
		 */
		protected function shearch_supplier_autocomlete_AJAX(){
			$query="SELECT * FROM `".SUPPLIERS_TBL."`  WHERE `nickName` LIKE ?";

			$stmt = $this->mysqli->prepare($query) or die($this->mysqli->error);
			$search = '%'.$_POST['search'].'%';
			$stmt->bind_param('s', $search) or die($this->mysqli->error);
			$stmt->execute() or die($this->mysqli->error);
			$result = $stmt->get_result();
			$stmt->close();
			$response = array();

			$i=0;
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					// $response[] = $row['company'];
					$response[$i]['label'] = $row['nickName'];
					$response[$i]['value'] = $row['nickName'];
					$response[$i]['href'] = $_SERVER['REQUEST_URI'].'&supplier_id='.$row['id'];
					$response[$i++]['desc'] = $row['id'];
				}
			}
			echo json_encode($response);
			exit;
		}
				
		// запрашивает из базы допуски пользователя
		// необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
		private function get_user_access_Database_Int($id){
			$query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);
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