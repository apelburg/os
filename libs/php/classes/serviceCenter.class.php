<?php
	class ServiceCenter  extends aplStdAJAXMethod{
		private $Query;
		private $first_default = true;

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

		/**
		 *	удвляет услугу
		 *
		 *	@author  	Alexey Kapitonov
		 *	@version 	16:50 01.03.2016
		 */
		protected function services_del_AJAX(){
			$this->responseClass->addMessage('Удаление услуги');
		}

		// возвращает контент для окна
		private function get_window_content(){
			// собираем объект
			$this->get_object_vars();


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

		// во
		private function variants_print_Html(){
			$position_num = 1;
			$color_arr = array('rgba(79, 154, 48, 0.2)','rgba(79, 142, 13, 0.37)');
			$color = $color_arr[1];$old_color = '';

			foreach ($this->Query['positions'] as $position) {
				
				if(count($position['variants'])> 1){
					if($old_color == $color){
						foreach ($color_arr as $color_1) {
							if($old_color != $color_1){
								$color = $color_1;		
							}
						}
					}

					$color_style = 'style="background-color:'.$color.'"';	
				}else{
					$color_style = '';
				}
				
				$variant_num = 1;
				foreach ($position['variants'] as $variant) {
					if($this->first_default){
						if($variant_num == 1){
							echo '<tr data-dop_row_id="'.$variant['id'].'" id="default_var" class="tr_checked">';		
						}
						
					}else{
						echo '<tr data-dop_row_id="'.$variant['id'].'"  '.(($variant['id'] == (int)$_POST['row_id'])?'id="default_var" class="tr_checked"':'').'>';
					}	

						foreach ($variant['services'] as $key => $value) {
							$variant['services'][$key]['print_details'] = json_decode($variant['services'][$key]['print_details'],'true');	
						}
						 
						echo '<td class="js-variant_services_json"><div>'.json_encode($variant['services']).'</div></td>';
						echo '<td>';
							echo '<div class="js-psevdo_checkbox"></div>';
						echo '</td>';
						echo '<td>'.$position_num.'.'.$variant_num.'</td>';
						echo '<td><span class="marcker_led"  '.$color_style.'>&nbsp;</span></td>';
						echo '<td>в'.$variant_num.'</td>';
						echo '<td>'.$position['art'].'</td>';
						echo '<td><span class="service">'.count($variant['services']).'</span></td>';
						echo '<td>'.$position['name'].'</td>';
						echo '<td>'.$variant['quantity'].' шт</td>';
						$my_variant = $variant;
						unset($my_variant['services']);
						echo '<td class="js-variant_info"><div>'.json_encode($my_variant).'</div></td>';
					echo '</tr>';
					$variant_num++;
				}
				$position_num++;

				$old_color = $color;
			}
		}

		// собираем объект
		private function get_object_vars(){
			// получаем строку запроса
			$this->Query = $this->get_query( (int)$_GET['query_num'] );

			// получаем строки позиций
			$this->Query['positions'] = $this->get_positions( (int)$_GET['query_num'] );

			// выбираем id позиций
			$i = 0; $id_s = '';
			foreach ($this->Query['positions'] as $positions) {
				$id_s .= (($i>0)?',':'')."'".$positions['id']."'"; $i++;
			}
			// добавляет варианты в объект
			$this->get_variants_and_services($id_s);

		}
		// возвращает строки вариантов
		private function get_variants_and_services($id_s){
			$query = "SELECT * FROM `".RT_DOP_DATA."` WHERE `row_id` IN (".$id_s.");";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					if($this->first_default == true && $row['id'] == $_POST['row_id']){
						$this->first_default = false;
					}
					$this->Query['positions'][$row['row_id']]['variants'][$row['id']] = $row;
					// получаем услуги к варианту (в целях экономии ресрсов получаем сдесь)
					$this->Query['positions'][$row['row_id']]['variants'][$row['id']]['services'] = $this->get_services($row['id']);
				}
			}	
			return;
		}

		// возвращает прикрепленные услуги
		private function get_services($id){
			$query = "SELECT `".RT_DOP_USLUGI."`.*, IFNULL(`".RT_DOP_USLUGI."`.`other_name`, `".OUR_USLUGI_LIST."`.`name`) AS `service_name` FROM `".RT_DOP_USLUGI."` ";
			$query .= " LEFT JOIN `".OUR_USLUGI_LIST."` ON `".RT_DOP_USLUGI."`.`uslugi_id` = `".OUR_USLUGI_LIST."`.`id`";
			$query .= " WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$id."';";
			
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}
		// возвращает строку запроса
		private function get_query($query_num = 0){
			$query = "SELECT * FROM `".RT_LIST."` WHERE `query_num` = '".$query_num."';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					return $row;
				}
			}	
			return array();
		}
		// возвращает строки позиций
		private function get_positions($id){
			$query = "SELECT * FROM `".RT_MAIN_ROWS."` WHERE `query_num` = '".$id."';";
			$result = $this->mysqli->query($query) or die($this->mysqli->error);	
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[$row['id']] = $row;
				}
			}	
			return $arr;
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