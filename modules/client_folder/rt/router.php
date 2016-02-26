<?php

    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['client_folder']['section']['rt']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['client_folder']['section']['rt']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **
	
	save_way_back(array('page=client_folder','section=rt_position','section=agreement_editor','section=agreements','section=business_offers','section=planner'),'?page=cabinet&client_id='.$client_id);
	$quick_button_back = get_link_back();
	

	include ROOT.'/libs/php/classes/rt_position_no_catalog_class.php';
	$Position_no_catalog = new Position_no_catalog;
	
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
	     require_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_class.php");
		 echo RT::setCalcualtorLevel($_GET['query_num'],$_GET['setCalcualtorLevel']);
		 exit;
	}
	
	if(isset($_POST['getSizesForArticle'])){
	     require_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_class.php");
		 echo RT::getSizesForArticle($_POST['pos_id']);
		 exit;
	}
	
	if(isset($_GET['getSpecificationsDates'])){
	     require_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/agreement_class.php");
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
		 include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/com_pred_class.php");
		 
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
		 if($_GET['type']== 'rows') echo RT::delete_rows(json_decode($_GET['deleting']),@$_GET['query_num']);
		 if($_GET['type']== 'prints' || $_GET['type']== 'uslugi' || $_GET['type']== 'printsAndUslugi' )  echo RT::deletePrintsAndUslugi(json_decode($_GET['deleting']), $_GET['type']);
		 
		 exit;
	}
	if(isset($_GET['fetch_dop_uslugi_for_row'])){

		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::fetch_dop_uslugi_for_row($_GET['fetch_dop_uslugi_for_row']);
		
		exit;
	}
	if(isset($_GET['fetch_data_for_dop_uslugi_row'])){

		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::fetch_data_for_dop_uslugi_row($_GET['fetch_data_for_dop_uslugi_row']);
		
		exit;
	}
	if(isset($_GET['grab_calculator_data'])){
		//print_r(json_decode($_GET['grab_calculator_data']));
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::grab_data(json_decode($_GET['grab_calculator_data']));
		//print_r($out_put);
		exit;
	}
	if(isset($_GET['grab_dop_info'])){
	
	    include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		$out_put = rtCalculators::grab_dop_info();
		echo $out_put;
		exit;
	}
	if(isset($_GET['save_calculator_result'])){
		print_r(json_decode($_GET['details']));//
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		rtCalculators::save_calculatoins_result(json_decode($_GET['details']));
		exit;
	}
	if(isset($_GET['delete_prints_for_row'])){
		//echo  $_GET['usluga_id'].' - '. $_GET['delete_prints_for_row'];
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		$usluga_id = (isset($_GET['usluga_id']))? $_GET['usluga_id'] : FALSE;
		$all = (isset($_GET['all']))? $_GET['all'] : FALSE;
		rtCalculators::delete_prints_for_row($_GET['delete_prints_for_row'],$usluga_id,$all);
		exit;
	}
    if(isset($_GET['change_quantity_and_calculators'])){
		// echo $_GET['quantity'];
		
	    // проверяем есть ли размеры у позиции если есть дальше не идем и отдаем оповещение
		if(isset($_GET['source']) && $_GET['source']=='rt'){
			if(RT::checkPosAboutSizes($_GET['id'])==true){
				echo '{"warning":"size_exists"}';
				exit;
			}
		}
		
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
		echo rtCalculators::change_quantity_and_calculators($_GET['quantity'],$_GET['id'],$_GET['print'],$_GET['extra'],$_GET['source']);
		exit;
	}
	if(isset($_GET['distribute_print'])){
		include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/rt_calculators_class.php");
		
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
   	

		if(isset($_POST['AJAX'])){				
		    if($_POST['AJAX']=='edit_query_theme'){
		        RT::save_theme($_POST['query_num'],$_POST['theme']);
				echo '{"response":"OK"}';
				exit;
			}
			if($_POST['AJAX']=='update_new_sort_rt'){
		        RT::update_new_sort_rt_AJAX();
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
	Client::get_client__information($_GET['client_id']);
	
	include ROOT.'/skins/tpl/client_folder/rt/options_bar.tpl';

  	include 'controller.php';
	// шаблон страницы
	include ROOT.'/skins/tpl/client_folder/rt/show.tpl';


?>