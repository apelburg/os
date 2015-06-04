<?php
    class Cabinet{
    	function __consturct(){
		}


		// подсчет суммы доп услуг или печати
		// на вход подаётся результат работы get_dop_uslugi_print_type() 
		// или get_dop_uslugi_no_print_type
		public function calc_summ_dop_uslug($arr,$tir){
			$summ = 0;

			foreach ($arr as $key => $value) {
				//echo $value['dop_row_id'].'  |  '.$value['glob_type'].'  |  '.$value['type'].'  |  '.$value['for_how'].' - ';
				if($value['for_how']=="for_one"){
					//echo ''.$value['price_out'].' * '.$tir.' + '.$summ.' = '.($summ+$value['price_out']*$tir).'<br>';
					$summ += ($value['price_out']*$tir);
					
				}else{
					//echo ''.$value['price_out'].' + '.$summ.'= '.($summ+$value['price_out']).'<br>';
					$summ += $value['price_out'];

					
				}
				
			}
			// echo $summ.'<br>';
			return $summ;
		}

		// выбираем данные о стоимости печати 
		//на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_print_type($arr){
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']=='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}
		public function select_global_status($real_val){

			global $GLOBAL_STATUS_ORDER;
			$status_arr = $GLOBAL_STATUS_ORDER;
			//$html = '<select><option value="">...</option>';
			$html = '<select>';
			foreach ($status_arr as $key => $value) {
				$is_checked = ($real_val==$value)?'selected="selected"':'';
				$html .= ' <option '.$is_checked.'>'.$value.'</option>';
			}	
			$html .= '</select>';
			return $html;
		}
		public function select_status($rights_int/*права для определения списка статуса*/,$real_val){
			global $STATUS_LIST;
			$status_arr = $STATUS_LIST[$rights_int];
			$html = '<select><option value="">...</option>';
			foreach ($status_arr as $key => $value) {
				$is_checked = ($real_val==$value)?'selected="selected"':'';
				$html .= ' <option '.$is_checked.'>'.$value.'</option>';
			}	
			$html .= '</select>';
			return $html;
		}
		public function get_gen_status($variable,$type){
			$start_status = $variable[0]['status_'.$type];

			foreach ($variable as $key => $value) {
				if($start_status!=$value['status_'.$type] ){
					$start_status = '';
				}
			}
			return $start_status;
		}

		// выбираем данные о стоимости доп услуг не относящихся к печати
		// на вход подаётся массив из get_dop_uslugi($dop_row_id); 
		public function get_dop_uslugi_no_print_type($arr){
			
			
			$arr_new = array();
			foreach ($arr as $key => $val) {
				if($val['glob_type']!='print'){
					$arr_new[] = $val;
				}
			}
			return $arr_new;
		}

		// выбираем данные о доп услугах для запроса
		public function get_query_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			global $mysqli;
			$query = "SELECT `".RT_DOP_USLUGI."`.*,`os__our_uslugi`.`name` FROM `".RT_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".RT_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".RT_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}
		// выбираем данные о доп услугах для заказа
		public function get_order_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			global $mysqli;

			$query = "SELECT `".CAB_DOP_USLUGI."`.*,`os__our_uslugi`.`name` 
			FROM `".CAB_DOP_USLUGI."` 
			LEFT JOIN  `os__our_uslugi` ON  `os__our_uslugi`.`id` = `".CAB_DOP_USLUGI."`.`uslugi_id` 
			WHERE `".CAB_DOP_USLUGI."`.`dop_row_id` = '".$dop_row_id."'";

			//$query = "SELECT * FROM `".CAB_DOP_USLUGI."` WHERE `dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			//echo $query;
			return $arr;
		}

		static function show_order_num($key){
		$i = 6 - strlen($key);
		// echo $i.'    */';
		$str = '';
		for ($t=0; $t < $i ; $t++) { 
			$str .='0';		}
		return $str.$key;
	}


   	}