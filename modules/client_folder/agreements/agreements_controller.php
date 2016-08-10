<?php 
    include_once(ROOT."/libs/php/classes/client_class.php");
	
    if(isset($_GET['update_specification_short_description_ajax']))
	{
	    echo $_GET['id'].' '.$_GET['field_name'].' '.$_GET['field_val'];
		//update_specification_short_description($_GET['id'],$_GET['field_name'],$_GET['field_val']);
		exit;
    }
    unset($_SESSION['back_url']);
	
	$doc_type = (isset($_GET['doc_type']))?$_GET['doc_type']:'agreement';
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	if($doc_type=='agreement'){
		$agreements_data = fetch_all_client_agreements($client_id);
		
		/**/	
		
		$agreement_row = '';
		$specification_row = '';
		if($agreements_data['results_num'] > 0)
		{    
		
			 $option =''; 
			 while($agreement = mysql_fetch_assoc($agreements_data['result']))
			 {
				 //print_r($agreement);
				 //echo '<br>';
				 
				 
				 if(!isset($_GET['agreement_type']))
				 {
					 
					 if($agreement['type'] != 'long_term') continue;
	
					 $array_basic[$agreement['id']] = $agreement['basic'];
	
					 $date_arr = explode('-',$agreement['date']);
					 $date = $date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0];  		 
					
					 $option .= '<option value="'.$agreement['id'].'">'.fetch_our_requisites_nikename($agreement['our_requisit_id']).' - '.Client::fetch_client_requisites_nikename($agreement['client_requisit_id']).' - '.$date_arr[0].' г. '.(((bool)$agreement['basic'])?' [основной]':'').'</option>';
					 
					 $expire_date_arr = explode('-',$agreement['expire_date']);
					 $expire_date = $expire_date_arr[2].' '.$month_day_name_arr[(int)$expire_date_arr[1]].' '.$expire_date_arr[0];
					 
					 $basic_style_mark = ($agreement['basic'])? '<img src="../../skins/tpl/admin/order_manager/img/agreement_basic.png" align="left">':'<img src="../../skins/tpl/admin/order_manager/img/agreement_not_basic.png" align="left">';
					 $cup_agreement = (!isset($cup_agreement))? $basic_style_mark.'Договор: ДС №'.$agreement['agreement_num'] .' от '.$date :$cup_agreement;
					 $hidden = (!isset($hidden))?'':'hidden';//
					 $basic_style = ($agreement['basic'])?'bold':'100';
					 
					 $path = 'data/agreements/'.$client_id.'/'.$date_arr[0].'/long_term/'.$agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'].'/';
					
					 
					 
					 //$long_term_agreement_div = '<div style="display:'.$display_class.'">';
					 //$long_term_agreement_div .= '<table width="100%" class="agreement_list_tbl"><tr>';
					 $agreement_row .= '<tr agreement_id="'.$agreement['id'].'" '.$hidden.'>';
					 $agreement_row .= '<td style="border-right:none;" colspan="2">'.$basic_style_mark.' договор &nbsp;<a href="#" onclick="if(confirm(\'Договор будет установлен как основной\')){location = \'?page=agreement&section=set_as_basic&client_id='.$client_id.'&agreement_id='.$agreement['id'].'\'};return false;">основной</a></td><td style="border-left:none;">№ <agreement_num>'.$agreement['agreement_num'].'</agreement_num></td>';
					 $agreement_row .= '<td><agreement_num hidden>'.$basic_style_mark.'</agreement_num><agreement_num>'.$date.'</agreement_num></td><td colspan="3">долгосрочный; действителен до '.$expire_date.'</td>';
					 //$agreement_row .= '<td style="width:110px;font-weight:'.$basic_style.'">'.$basic_style_mark.'<a href="?page=agreement&section=set_as_basic&client_id='.$client_id.'&agreement_id='.$agreement['id'].'">основной</a></td>';
					 $agreement_row .= "<td style='border-left:none;border-right:none;'><a href='?page=agreement&section=agreement_editor&client_id=".$client_id."&agreement_id=".$agreement['id']."&agreement_type=".$agreement['type']."&open=empty&open=all&dateDataObj={\"doc_type\":\"spec\"}' target='_blank'>открыть</a></td>";
					 $agreement_row .= "<td style='border-left:none;border-right:none;'><a href='?page=agreement&section=agreement_editor&client_id=".$client_id."&agreement_id=".$agreement['id']."&agreement_type=".$agreement['type']."&open=all&dateDataObj={\"doc_type\":\"spec\"}' target='_blank'>открыть всё</a></td>";
					 $agreement_row .= '<td style="border-left:none;border-right:none;"><a href="?page=agreement&client_id='.$client_id.'&agreement_id='.$agreement['id'].'&section=delete_agreement"  onclick="if(confirm(\'договор будет удален\')) return true; return false;"><em>DEL</em></a></td>';
					 $agreement_row .= '</tr>';
					 $agreement_row .= '<tr class="line" agreement_id="'.$agreement['id'].'" '.$hidden.'><td colspan="10"></td></tr>';
					 
					 
					 ///////////////////////// specifications /////////////////////////
					 $specifications = fetch_specifications($client_id,$agreement['id'],'specification_num');
					 
					 
					 if($specifications)
					 { 
						 while($specification = mysql_fetch_assoc($specifications))
						 {
							 // echo '<pre>'; print_r($specification); echo '</pre>'; 
				             $type = ($specification['specification_type']=='date')?'дата':'р/д';
							
							 $date_arr = explode('-',$specification['date']);
							 $date = $date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0];
							 $specification_row .= '<tr agreement_id="'.$agreement['id'].'" '.$hidden.'>';
							 $specification_row .= '<td style="border-right:none;width:95px;">спецификация</td><td style="width:50px;">'.$type.'</td><td style="border-left:none;width:120px;cursor: pointer;" onclick="change_spec_num(this,\''.$path.'\','.$client_id.','.$agreement['id'].','.$specification['specification_num'].');">№ '.$specification['specification_num'].'</td>';
							 $specification_row .= '<td style="width:200px;">'.$date.'</td><td colspan="3" managed="text" bd_row_id="'.$specification['id'].'" bd_field="short_description" max_length="30">'.$specification['short_description'].'</td>';
							 $specification_row .= "<td style='width:80px;border-left:none;border-right:none;'><a href='?page=agreement&section=agreement_editor&client_id=".$client_id."&agreement_id=".$agreement['id']."&agreement_type=".$agreement['type']."&open=specification&specification_num=".$specification['specification_num']."&dateDataObj={\"doc_type\":\"spec\"}' target='_blank'>открыть</a></td>";
							 $specification_row .= "<td style='width:100px;border-left:none;border-right:none;'><a href='?page=agreement&section=specification_editor&client_id=".$client_id."&specification_num=".$specification['specification_num']."&agreement_id=".$agreement['id']."&dateDataObj={\"doc_type\":\"spec\"}'>редактировать</a></td>";
							 $specification_row .= '<td style="width:50px;border-left:none;border-right:none;"><a href="?page=agreement&client_id='.$client_id.'&agreement_id='.$agreement['id'].'&specification_num='.$specification['specification_num'].'&section=delete_specification" onclick="if(confirm(\'спецификация будет удалена\')) return true; return false;"><em>DEL</em></a></td>';
							 $specification_row .= '</tr>';
	
						 }
					 }
					 else
					 {
						$specification_row .= '<tr agreement_id="'.$agreement['id'].'" '.$hidden.'><td colspan="9">нет спецификаций</td></tr>'; 
					 }
					 
					 ///////////////// 
					 
				 
				 }
				 elseif(isset($_GET['agreement_type']) && $_GET['agreement_type'] == 'short_term')
				 {
					 if($agreement['type'] != 'short_term') continue;
					 
					 $array[$agreement['id']] = $agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'];
					 $array = array_unique($array);
					 $option ='';
					 foreach($array as $key => $item)
					 {
						 list($our_req_id,$client_req_id)= explode('_',$item);
						 $option .= '<option value="'.$our_req_id.'_'.$client_req_id.'">'.fetch_our_requisites_nikename($our_req_id).' - '.Client::fetch_client_requisites_nikename($client_req_id).'</option>';
					 } 
					   
					 $cup_agreement = '';
					 $hidden = (isset($last_pair) && $last_pair != $agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'])?'hidden':'';
					
					 //$long_term_agreement_div = '<div style="display:'.$display_class.'">';
					 //$long_term_agreement_div .= '<table width="100%" class="agreement_list_tbl"><tr>';
					 $agreement_row .= '<tr agreement_id="'.$agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'].'" '.$hidden.'>';
					 $agreement_row .= '<td style="border-right:none;width:60px;">договор</td><td style="border-left:none;width:70px;">№ '.$agreement['agreement_num'].'</td>';
					 $agreement_row .= '<td style="width:110px;">'.$agreement['date'].'</td><td colspan="4">'.$agreement['short_description'].'</td>';
					// $agreement_row .= '<td style="width:100px;border-left:none;border-right:none;">&nbsp;</td>';
					 $agreement_row .= '<td style="width:90px;none;border-right:none;"><a href="?page=agreement&section=agreement_editor&client_id='.$client_id.'&agreement_id='.$agreement['id'].'&agreement_type='.$agreement['type'].'" target="_blank">открыть</a></td>';
					 
					 $agreement_row .= '<td style="width:50px;border-left:none;border-right:none;"><a href="?page=agreement&client_id='.$client_id.'&agreement_id='.$agreement['id'].'&section=delete_agreement" onclick="if(confirm(\'договор будет удален\')) return true; return false;"><em>DEL</em></a></td>';
					 $agreement_row .= '</tr>';
					// $long_term_agreement_row .= '<tr class="line" agreement_id="'.$agreement['id'].'" '.$hidden.'><td colspan="9"></td></tr>';
					 
					 $last_pair = $agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'];
				 
				 }
			 }
			 $select = '<select onchange="agreement_section(this);" style="width:500px;">'.$option.'</select>';
			 $link = (!isset($_GET['agreement_type']))? '<a href="?'.addOrReplaceGetOnURL('agreement_type=short_term').'">краткосрочные</a>':'<a href="?'.addOrReplaceGetOnURL('','agreement_type').'">долгосрочные</a>';
			 $rows = '<tr class="cup_line_1"><td style="border-left:1px solid #BBBBBB;border-right:0px;width:145px;">Контрагенты:</td><td style="border-left:0px;" colspan="6">'.$select.'</td>';
			 //$rows .= '<td colspan="2" style="border-left:1px solid #BBBBBB;width:200px;text-align:center"><span id="cup_agreement">'.$cup_agreement.'</span></td>';
			 $rows .= '<td style="border-right:0px solid #BBBBBB;border-left:1px solid #BBBBBB;width:150px;text-align:right;padding-right:45px;"  colspan="3">'.$link.'</a></td>';
			 //rows .= <td style="width:50px;border-left:0px"></td>';
			 $rows .= '</tr>';
			 $rows .= '<tr class="cup_line_2"><td style="border-left:1px solid #BBBBBB;" colspan="3">документ / номер</td><td>дата документа</td><td colspan="2">подробнее</td><td></td><td colspan="3" align="center">действия</td></tr>';
			 $rows .= $agreement_row.$specification_row;
		
		}
		else
		{
			 $rows = '<tr class="cup_line_1"><td align="center">нет заключенных договоров</td></tr>';
		}
	}
	if($doc_type=='oferta'){
	    require_once(ROOT."/libs/php/classes/agreement_class.php");
	    $oferts_data = Agreement::fetch_all_client_oferts($client_id);
		
		if($oferts_data){
		    $rows = '<tr class=""><td colspan="11">&nbsp;</td></tr>
			        <tr class="cup_line_2"><td style="border-left:1px solid #BBBBBB;" colspan="4">документ / номер</td><td>дата документа</td><td colspan="2">подробнее</td><td></td><td colspan="3" align="center">действия</td></tr>
					<tr class=""><td colspan="11">&nbsp;</td></tr>';
		    while($data_row= $oferts_data->fetch_assoc()){
                 
				 
			     $date_arr = explode('-',substr($data_row['date_time'],0,10));
				 $date = $date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0];
				 
				 //echo '<pre>'; print_r($data_row); echo '</pre>'; 
				 $type = ($data_row['type']=='date')?'дата':'р/д';
				 
				 $rows .= '<tr>'; //agreement_id="'.$agreement['id'].'" '.$hidden.'
				 $rows .= '<td style="border-right:none;width:95px;">оферта</td><td style="width:50px;">'.$type.'</td><td style="width:8px;padding-right:0px;border-right:none;">№</td><td managed="text" bd_row_id="'.$data_row['id'].'" bd_field="num" style="width:120px;padding-left:4px;border-left:0px;">'.$data_row['num'].'</td>';
				 $rows .= '<td style="width:200px;">'.$date.'</td><td colspan="3" managed="text" bd_row_id="'.$data_row['id'].'" bd_field="short_description" max_length="30">'.$data_row['short_description'].'</td>';
				 $rows .= "<td style='width:100px;border-left:none;border-right:none;'><a href='?page=agreement&section=agreement_editor&client_id=".$client_id."&oferta_id=".$data_row['id']."&dateDataObj={\"doc_type\":\"oferta\"}'>открыть</a></td>";
				 $rows .= "<td style='width:100px;border-left:none;border-right:none;'><a href='?page=agreement&section=specification_editor&client_id=".$client_id."&oferta_id=".$data_row['id']."&dateDataObj={\"doc_type\":\"oferta\"}'>редактировать</a></td>";
				 
				 $rows .= '<td style="width:50px;border-left:none;border-right:none;"><a href="?page=agreement&client_id='.$client_id.'&oferta_id='.$data_row['id'].'&section=delete_oferta" onclick="if(confirm(\'оферта будет удалена\')) return true; return false;"><em>DEL</em></a></td>';
				 $rows .= '</tr>';
			}
		}
		else{	 
		
	        $rows = '<tr class="cup_line_1"><td align="center">нет заключенных оферт</td></tr>';
		}
	}
	
	$content  = '<table width="100%" id="agreement_list_tbl" class="agreement_list_tbl"  tbl="managed">';
	
	$content .= $rows.'</table>';
	
	$content .= '<script type="text/javascript" src="libs/js/tableDataManager.js"></script>
                 <script type="text/javascript" src="libs/js/geometry.js"></script>';
	if($doc_type=='agreement') $content .= '<script type="text/javascript">
                   tableDataManager.url =\'?page=agreement&update_specification_common_fields_ajax=1\';
                 </script>';
	if($doc_type=='oferta')	$content .= '<script type="text/javascript">
                   tableDataManager.url =\'?page=agreement&update_oferta_common_fields_ajax=1\';
                 </script>';	 

	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
?>
