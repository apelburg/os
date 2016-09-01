<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['client_folder']['section']['rt']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	save_way_back(array('page=client_folder','section=rt_position','section=agreement_editor','section=agreements','section=business_offers','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	

//	include ROOT.'/libs/php/classes/rt_position_no_catalog_class.php';
//	$Position_no_catalog = new Position_no_catalog;
	
	include ROOT.'/libs/php/classes/rt_class.php';
	new RT;


	
	// класс работы с формами
	include './libs/php/classes/os_form_class.php';


	

	/*
	вызов формы планируется из РТ
	*/
    // инициализация класса формы
	$FORM = new Forms();

	$quick_button = '<div class="quick_button_div" style="background:none"><a href="#" id="create_new_position" style="display: block;" class="button add">Добавить</a></div>';
	

	$query_num = (!empty($_GET['query_num']))? $_GET['query_num']:FALSE;
	
	if(isset($_POST['set_discount'])){
	     //print_r($_POST['form_data'])."<br>";
	     set_discount($_POST['form_data']);
		 header('Location:'.$_SERVER['HTTP_REFERER']);
	     exit;
    }
	if(isset($_GET['set_svetofor_status'])){
	     RT::change_all_svetofors(json_decode($_GET['ids']),$_GET['set_svetofor_status']);
		 header('Location:?'.addOrReplaceGetOnURL('','set_svetofor_status&ids'));
		 exit;
    }
	if(isset($_GET['set_order_deadline'])){
	     RT::set_order_deadline($_GET['ids'],$_GET['date'],$_GET['time']);
		 header('Location:?'.addOrReplaceGetOnURL('','set_order_deadline&ids&date&time'));
		 exit;
    }
	////////////////////////  AJAX  //////////////////////// 
	
	if(isset($_GET['setCalcualtorLevel'])){
	     // print_r($_GET);
	     require_once(ROOT."/libs/php/classes/rt_class.php");
		 echo RT::setCalcualtorLevel($_GET['query_num'],$_GET['setCalcualtorLevel']);
		 exit;
	}
	
	if(isset($_POST['getSizesForArticle'])){
	     require_once(ROOT."/libs/php/classes/rt_class.php");
		 echo RT::getSizesForArticle($_POST['pos_id']);
		 exit;
	}
	
	if(isset($_GET['getSpecificationsDates'])){
	     require_once(ROOT."/libs/php/classes/agreement_class.php");
		 echo Agreement::getSpecificationsDates(json_decode($_GET['getSpecificationsDates']));
		 exit;
	}
	
	
	if(isset($_GET['save_rt_changes'])){
	     //print_r(json_decode($_GET['save_rt_changes']));
		 RT::save_rt_changes(json_decode($_GET['save_rt_changes']));
		 exit;
	}
	if(isset($_GET['change_quantity'])){
		 // echo $_GET['quantity'];
		 
		 // проверяем есть ли размеры у позиции если есть дальше не идем и отдаем оповещение
		 if(isset($_GET['source']) && $_GET['source']=='rt'){
			 if(RT::checkPosAboutSizes($_GET['id'])==true){
				 echo '{"warning":"size_exists"}';
				 exit;
			 }
		 }
		 
		 RT::change_quantity($_GET['quantity'],$_GET['id'],$_GET['source']);
		 exit;
	}
	if(isset($_GET['expel_value_from_calculation'])){
	     //print_r(json_decode($_GET['expel_value_from_calculation']));
		 RT::expel_value_from_calculation($_GET['id'],$_GET['expel_value_from_calculation']);
		 exit;
	}
	if(isset($_GET['change_svetofor'])){
	     $idsArr = (isset($_GET['idsArr']))? json_decode($_GET['idsArr']):false;
		 RT::change_svetofor(array($_GET['id']),$_GET['change_svetofor'],$idsArr);
		 exit;
	}
	if(isset($_GET['make_com_offer'])){
		 include_once(ROOT."/libs/php/classes/com_pred_class.php");
		 
		 echo  Com_pred::save_to_tbl($_GET['make_com_offer']);

		 /* старый вариант создания коммерческого предложения
		 echo make_com_offer($id_arr,(int)$_GET['stock'],$_GET['order_num']/ *string* /,$_GET['client_manager_id']/ *string* /,(int)$_GET['conrtol_num']);
		 */
		 exit;
	}
	if(isset($_GET['sendToSnab'])){
		 
		 echo  RT::sendToSnab(json_decode($_GET['sendToSnab']));
		 exit;
	}
	

	/*if(isset($_GET['makeSpecAndPreorder'])){		 
		 // RT::make_specification($_GET['make_order']);
		 echo $_GET['make_order'];
		 exit;
		 
		 
		 RT::make_order($_GET['make_order']);
		 exit;
	}
	// создание предзаказа
	if(isset($_GET['make_order'])){
		 //RT::make_specification($_GET['make_order']);
		 RT::make_order($_GET['make_order']);
		 exit;
	}*/
	if(isset($_GET['set_masterBtn_status'])){
		 RT::set_masterBtn_status(json_decode($_GET['set_masterBtn_status']));
		 exit;
	}
	if(isset($_GET['save_copied_rows_to_buffer'])){
		 echo RT::save_copied_rows_to_buffer($_GET['save_copied_rows_to_buffer']);
		 exit;
	}
	if(isset($_GET['insert_copied_rows'])){
	     $place_id = (isset($_GET['place_id']))? $_GET['place_id']: FALSE;
		 echo RT::insert_copied_rows($_GET['query_num'],$place_id);
		 exit;
	}
	if(isset($_GET['deleting'])){
	     // перед выполнением процесса удаления необходимо проверить нет ли среди удаляемых расчетов расчеты входящие в объединенные тиражи
		 // если такие есть отправить вопрос о подтверждении удаления
		 // если подтверждение получено идем дальше:
		 // нам надо вырезать все данные об удаляемых нанесениях из данных объединенных тиражей в которые они входят
		 
		 $idsArr = json_decode($_GET['deleting'],true);
		 // print_r($idsArr);
		 // exit;
	     // проверяем какие типы калькуляторов используются в расчетах и не входитят ли услуги расчета в объединеный тираж
		if(!isset($_GET['ignore_calculators_checking'])){
			 $warnings =  array();
		     foreach($idsArr as $id){
			     $result = RT::check_on_united_calculations($id);
				 if($result) $warnings[] = $id;
			 }
		     if(count($warnings)>0){
			     echo json_encode(array("warning"=>array("united_calculations"=>$warnings)));
				 exit;
			 }
		 }

		 if($_GET['type']== 'rows') echo RT::delete_rows(json_decode($_GET['deleting']),@$_GET['query_num']);
		 if($_GET['type']== 'prints' || $_GET['type']== 'uslugi' || $_GET['type']== 'printsAndUslugi' )  echo RT::deletePrintsAndUslugi(json_decode($_GET['deleting']), $_GET['type']);
		 
		 exit;
	}
	if(isset($_GET['fetch_dop_uslugi_for_row'])){

		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::fetch_dop_uslugi_for_row($_GET['fetch_dop_uslugi_for_row']);
		
		exit;
	}
	if(isset($_GET['fetch_data_for_dop_uslugi_row'])){

		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::fetch_data_for_dop_uslugi_row($_GET['fetch_data_for_dop_uslugi_row']);
		
		exit;
	}
	if(isset($_GET['grab_calculator_data'])){
		//print_r(json_decode($_GET['grab_calculator_data']));
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::grab_data(json_decode($_GET['grab_calculator_data']));
		//print_r($out_put);
		exit;
	}
	if(isset($_GET['grab_dop_info'])){
	
	    include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::grab_dop_info();
		echo $out_put;
		exit;
	}
	if(isset($_GET['save_calculator_result'])){
		// print_r(json_decode($_GET['details']));//
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		rtCalculators::save_calculations_result_router($_GET['details']);
		exit;
	}
	if(isset($_GET['attach_calculation'])){
		// print_r(json_decode($_GET['details']));//
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		rtCalculators::attach_calculation($_GET['data']);
		exit;
	}
	if(isset($_GET['detach_calculation'])){
		// print_r(json_decode($_GET['details']));//
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		rtCalculators::detach_calculation($_GET['data']);
		exit;
	}
	if(isset($_GET['delete_prints_for_row'])){
		//echo  $_GET['usluga_id'].' - '. $_GET['delete_prints_for_row'];
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		$usluga_id = (isset($_GET['usluga_id']))? $_GET['usluga_id'] : FALSE;
		$all = (isset($_GET['all']))? $_GET['all'] : FALSE;
		rtCalculators::delete_prints_for_row($_GET['delete_prints_for_row'],$usluga_id,$all);
		exit;
	}
    if(isset($_GET['change_quantity_and_calculators'])){
		// echo $_GET['quantity'];
		
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//  СЮДА ПРОГРАММА подадает когда в РТ меняется тираж в ячейке содержащей расчет с прикрепленными к нему услугами типа print   //
		/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		//                                                       Порядок действия 
		// Сначала выясняем какие типы калькуляторов используются в расчетах, если есть "ручной" или "Дежурная услуга" отправляем 
		// предупреждение в ОС, также вместе с этим проверяем не входит ли тираж в объединенный расчет
		// Если на стороне пользователя (в ОС) предупреждение было принято сюда приходит повторный запрос с соответсвующей меткой 
		// и мы идем дальше.
		// "автоматические" калькуляторы пересчитываются в любом случае, а в калькуляторы "ручной" и "Дежурная услуга" добавляется
		// метка need_confirmation, цены при этом в них не меняются
		// если в "автоматическом" калькуляторе выясняется что тираж превыщает максимальный возможный, тип калькулятора меняется на 
		// "ручной" а извещение об том что есть превышение максимального тиража используется на стороне клиента для выведения 
		// окна информирующего пользователя о том что было превышение и калькулятор переведен в "ручной" тип
		// объединенные тиражи - если поле united_calculations в таблице RT_DOP_USLUGI содержит список других полей, при условии 
		// что этот калькулятор автоматический, записать в эти поля новые значения прайсов и X индекса, а также записать в них значение 
		// need_confirmation в случае если тип калькуляторов "ручной" и "Дежурная услуга", если калькулятор был автомачитеский и 
		// тираж был объединенный и общее количество превысило максимальный тираж перевести калькулятор в ручной и записать всем
		// связанным расчетам значение need_confirmation
		
		
	    // проверяем есть ли размеры у позиции если есть дальше не идем и отдаем оповещение
		if(isset($_GET['source']) && $_GET['source']=='rt'){
			if(RT::checkPosAboutSizes($_GET['id'])==true){
				echo '{"warning":"size_exists"}';
				exit;
			}
		}
		
		// проверяем какие типы калькуляторов используются в расчетах и не входитят ли услуги расчета в объединеный тираж
		if(!isset($_GET['ignore_calculators_checking'])){
		   $out_put = RT::check_calculators_types_by_id($_GET['id'],$_GET['quantity']);
		   if($out_put){
				 echo $out_put;
				 exit;
			}
		}
		
		
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		echo rtCalculators::change_quantity_and_calculators($_GET['quantity'],$_GET['id'],$_GET['print'],$_GET['extra'],$_GET['source']);
		exit;
	}
	if(isset($_GET['distribute_print'])){
		include_once(ROOT."/libs/php/classes/rt_calculators_class.php");
		
		rtCalculators::distribute_print($_GET['details']);
		exit;
	}
	if(isset($_GET['svetofor_display_relay'])){
	    // echo $_GET['ids'];
		echo RT::svetofor_display_relay($_GET['svetofor_display_relay'],$_GET['ids']);
		exit;
	}

	if(isset($_GET['set_cont_face'])){

		RT::set_cont_face($_GET['set_cont_face'],$_GET['query_num']);
		exit;
	}
	
   	if(isset($_GET['show_good_preview'])){
        echo RT::show_good_preview($_GET['art_id'],$_GET['main_row_id']);
		exit;
	}

	if(isset($_POST['AJAX'])){
        include_once './libs/php/classes/suppliers_api.php';
        new SuppliersApi();

		if($_POST['AJAX']=='edit_query_theme'){
			RT::save_theme($_POST['query_num'],$_POST['theme']);
			echo '{"response":"OK"}';
			exit;
		}
		if($_POST['AJAX']=='update_new_sort_rt'){
			RT::update_new_sort_rt_AJAX();
			exit;
		}
		if($_POST['AJAX']=='change_main_rows_color'){
			global $mysqli;
			$query ="UPDATE `".RT_MAIN_ROWS."` SET 
				`rt_row_color` = '".$_POST['val']."'";
			$query .= " WHERE `id` = '".(int)$_POST['row_id']."'";
			$result = $mysqli->query($query) or die($mysqli->error);
		}

		if($_POST['AJAX']=='getDataForSpecWindow'){
			global $mysqli;
			
			$out_put = array();
			$names = array();



            // клиентские реквизиты
			require_once(ROOT."/libs/php/classes/client_class.php");
            $data_arr = Client::requisites($_GET['client_id']);
            $out_put['client_requisites'] = array();
            if(count($data_arr) > 0){
		        foreach($data_arr as $requisites){
					$out_put['client_requisites'][] = array('id'=>$requisites['id'],'name'=>$requisites['company']);
					$names['client_requisites'][$requisites['id']] = $requisites['company'];
				}
			}
			else{
			
			    $out_put['client_requisites'][] = array('id'=>0,'name'=>'реквизиты не найдены');
			}
			
            // наша фирма ответственные лица
			$alldata = fetch_our_firms_data();

			if($alldata['results_num']>0){

				$out_put['our_firms'] = array();
				while($item = mysql_fetch_assoc($alldata['result'])){
					//print_r($item);
		            $managment = array();
					$query = "SELECT*FROM `".OUR_FIRMS_MANAGEMENT_TBL."` WHERE `requisites_id` = '".$item['id']."'";
				    $result = $mysqli->query($query) or die($mysqli->error);
					while($row = $result->fetch_assoc()){
					    //print_r($row);
					    if(trim($row['name'])!='') $managment[] = array('id'=>$row['id'],'name'=>$row['name']);
					}

					$out_put['our_firms'][] = array('id'=>$item['id'],'name'=>$item['company'],'managment'=>$managment);
					$names['our_firms'][$item['id']] = $item['company'];
				}
			}	    



			if($_POST['document_type'] == "spec"){
				// заключенные договоры
				$out_put['existing_agreements'] = array();

			    $long_term_agreements = fetch_client_agreements_by_type('long_term',$_GET['client_id']);
 
				$spec_num = 0;
			    if($long_term_agreements['results_num'] > 0){
					
					while($row =mysql_fetch_assoc($long_term_agreements['result'])){
						$our_comp = (isset($names['our_firms'][$row['our_requisit_id']]))?$names['our_firms'][$row['our_requisit_id']]:$row['our_comp_full_name'];
						$client_comp = (isset($names['client_requisites'][$row['client_requisit_id']]))? $names['client_requisites'][$row['client_requisit_id']]:$row['client_comp_full_name'];

						$out_put['existing_agreements'][] = array(
							                                'agreement_id' => $row['id'],
							                                'our_comp' => $our_comp/*$row['our_comp_full_name']*/,
							                                'our_requisit_id' => $row['our_requisit_id'],
							                                'client_comp' =>$client_comp/*$row['client_comp_full_name']*/,
							                                'client_requisit_id' => $row['client_requisit_id'],
							                                'date' => $row['date'],
							                                'agreement_num' => $row['agreement_num'],
							                                'basic' => $row['basic']);
					}							 
			    }				
			}
			/*if($_POST['document_type'] == "oferta"){
				
			}*/
            
            // клиентский адрес доставки 
// os__clients_requisites
        
			$addresses_arr = Client::get_addres($_GET['client_id']);
			$out_put['addresses'] = array();

			foreach($addresses_arr as $data){
			    $address  = ($data['city']!='')? $data['city']:'';
				$address .= ($data['street']!='')? ', '.$data['street']:'';
				$address .= ($data['house_number']!=0)? ', дом.'.$data['house_number']:'';
				$address .= ($data['korpus']!=0)? ', корп.'.$data['korpus']:'';
				$address .= ($data['office']!=0)? ', оф.'.$data['office']:'';
				$address .= ($data['office']!=0)? ', литер'.$data['liter']:'';
				$address .= ($data['office']!=0)? ', строение'.$data['bilding']:'';
				 
				if($address=='') continue;
			    $out_put['addresses'][] = array('id'=>$data['id'],'str'=>$address);
			}

            // минимальная дата для календаря
			$out_put['min_allowed_date'] = substr(goOnSomeWorkingDays(date("Y-m-d H:i:s"),3,'+'),0,10);
			// print_r($out_put);
				echo json_encode($out_put);
				exit;
		}

	}
	/////////////////////  END  AJAX  ////////////////////// 
	
	
	$cont_face_data = RT::fetch_query_client_face($query_num);
	//print_r($cont_face_data);

	$cont_face = '<div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,\'clientManagerMenu\');">Контактное лицо: '.(($cont_face_data['id']==0)?'не установлено':$cont_face_data['details']['last_name'].' '.$cont_face_data['details']['name'].' '.$cont_face_data['details']['surname']).'</div>';
	
	$create_time = RT::fetch_query_create_time($query_num);
	$query_related_data = RT::fetch_query_related_data($query_num);
	$theme = $query_related_data['theme'];
	$query_status = $query_related_data['status'];
	$manager_id = $query_related_data['manager_id'];
	$calculator_level = ($query_related_data['calculator_level']!='')?$query_related_data['calculator_level']:'full';
	$CALCULATOR_LEVELS = array('full'=>"Конечники",'ra'=>"Рекламщики");
	$calculator_level_ru = $CALCULATOR_LEVELS[$calculator_level];
	$block_page_elements = ($_SESSION['access']['access']!= 1 && $query_status!='in_work')?true:false;
	$theme_block = '<input id="query_theme_input" class="query_theme" query_num="'.$query_num.'" type="text" value="'.(($theme=='')?'Введите тему':htmlspecialchars($theme,ENT_QUOTES)).'" onclick="fff(this,\'Введите тему\');">';	

	// шаблон поиска
	include ROOT.'/skins/tpl/common/quick_bar.tpl';

	// планка клиента
	include_once './libs/php/classes/client_class.php';
	$Client = new Client();
    $Client->get_client__information($_GET['client_id']);

	include ROOT.'/skins/tpl/client_folder/rt/options_bar.tpl';

  	include 'controller.php';
	// шаблон страницы
	include ROOT.'/skins/tpl/client_folder/rt/show.tpl';


?>