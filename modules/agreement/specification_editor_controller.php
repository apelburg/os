<?php 


    // ПРЕДПОЛАГАЕМ ЧТО ЕСЛИ НЕ БЫЛ ПЕРЕДАН ПАРАМЕТР  $_GET['dateDataObj'] ТО ЭТО ССЫЛКА НА СПЕЦИФИКАЦИЮ
	// НАДО ПЕРЕДЕЛАТЬ ССЫЛКИ В СПИСКЕ
	if(isset($_GET['dateDataObj'])) $dateDataObj = json_decode($_GET['dateDataObj']);
	else $dateDataObj->doc_type = 'spec';


	if(!isset($_SESSION['back_url'])) $_SESSION['back_url'] = $_SERVER['HTTP_REFERER'];
	
    if(isset($_GET['agregate_doc_rows']))
	{
	    if($_GET['agregate_doc_rows']=='spec'){
			agregate_specification_rows($_GET['data']);
			header('Location:?page=agreement&section=specification_editor&client_id='.$client_id.'&specification_num='.$specification_num.'&agreement_id='.$agreement_id.'&dateDataObj={"doc_type":"spec"}'); 
			exit;
		}
		if($_GET['agregate_doc_rows']=='oferta'){
		    include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/agreement_class.php");
		    Agreement::agregate_oferta_rows($_GET['data']);
			
			header('Location:?page=agreement&section=specification_editor&client_id='.$client_id.'&oferta_id='.$_GET['oferta_id'].'&dateDataObj={"doc_type":"oferta"}'); 
			exit;
		}
		
		//print_r($_GET['data']
		
    }
	
	if($dateDataObj->doc_type=='spec')
	{
	
		$result = fetch_specification($client_id,$agreement_id,$specification_num);
		
		if($result)
		{
			$tpl = './skins/tpl/agreement/specification_edit_row.tpl';
			$fd = fopen($tpl,'r');
			$tpl = fread($fd,filesize($tpl));
			fclose($fd);
			
			ob_start();
			$row_num = 0;
			$itogo=0;
			while($row = mysql_fetch_assoc($result))
			{
				
			
				$row['price'] = ($row['discount'] != 0)? round((float)(($row['price']/100)*(100 + $row['discount'])),2) : (float)$row['price'];	
				$row['summ'] = round($row['quantity']*$row['price'],2);	 
				$itogo += $row['summ'];	
				eval('?>'.$tpl.'<?php ');  
				
			}
			
			$rows = ob_get_contents();
			ob_get_clean();
			
			include './skins/tpl/agreement/specification_edit_tbl.tpl';
		}
		else $rows = 'ошибка получения данных specification_editor_controller.php';
    }
    if($dateDataObj->doc_type=='oferta')
	{
	    include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/agreement_class.php");
		$oferta_data_arr =  Agreement::fetch_oferta_data($_GET['oferta_id']);

		if(count($oferta_data_arr)>0)
		{
			
			$tpl = './skins/tpl/agreement/specification_edit_row.tpl';
			$fd = fopen($tpl,'r');
			$tpl = fread($fd,filesize($tpl));
			fclose($fd);
			
			ob_start();
			$row_num = 0;
			$itogo=0;
			foreach($oferta_data_arr as $row)
			{
				$row['price'] = ($row['discount'] != 0)? round((float)(($row['price']/100)*(100 + $row['discount'])),2) : (float)$row['price'];	
				$row['summ'] = round($row['quantity']*$row['price'],2);	 
				$itogo += $row['summ'];	
				eval('?>'.$tpl.'<?php ');  	
			}
			
			$rows = ob_get_contents();
			ob_get_clean();
			
			include './skins/tpl/agreement/specification_edit_tbl.tpl';
		}
		else $rows = 'ошибка получения данных specification_editor_controller.php';
	}

?>
