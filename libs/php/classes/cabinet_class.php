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

		 // выбираем данные о доп услугах
		public function get_dop_uslugi($dop_row_id){//на вход подаётся id строки из `os__rt_dop_data` 
			global $mysqli;
			$query = "SELECT * FROM `os__rt_dop_uslugi` WHERE `dop_row_id` = '".$dop_row_id."'";
			$result = $mysqli->query($query) or die($mysqli->error);
			$arr = array();
			if($result->num_rows > 0){
				while($row = $result->fetch_assoc()){
					$arr[] = $row;
				}
			}
			return $arr;
		}

		public function show_number_query($key){
		$i = 6 - count($key);
		$str = '';
		for ($t=0; $t < $i ; $t++) { 
			$str .='0';		}
		return $str.$key;
	}


   	}