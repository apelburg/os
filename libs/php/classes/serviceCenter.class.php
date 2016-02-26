<?php
	class ServiceCenter  extends aplStdAJAXMethod{

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
		 *	возвращает окно
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:10 12.02.2016
		 */
		protected function get_service_center_AJAX(){
			// проверка на наличие номера запроса
			if(!isset($_GET['query_num']) || $_GET['query_num'] ==''){
				$this->responseClass->addMessage('Системе необходимо находиться внутри запроса.');
				return;
			}

			$this->responseClass->options['width'] = '100%';
			$this->responseClass->options['height'] = '100%';
			$this->responseClass->options['title'] = 'Центр услуг';
			$this->responseClass->options['html'] = base64_encode( $this->get_window_content() );
			$this->responseClass->options['myFunc'] = 'show_SC';
			// $this->responseClass->addResponseFunction('show_SC',$options);	  
		}

		// возвращает контент для окна
		private function get_window_content(){




			$html = '<div id="js-service-center">';
				ob_start();
				// echo '<pre>';
				// print_r($_SESSION);
				// echo '<pre>';
				include_once ROOT.'/skins/tpl/client_folder/service_center/show.tpl';
				$html .= ob_get_contents();
				ob_get_clean();
			$html .= '</div>';
			return $html;
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

		/**
		 * 	 рандомный цвет
		 *
		 *	 @author  	Alexey Kapitonov
		 *	 @version 	 	 
		 */
		public function rand_color() {
		    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
		}
	}

?>