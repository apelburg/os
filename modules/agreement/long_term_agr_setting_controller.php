<?php

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // client_requisites
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	$dateDataObj = json_decode($_GET['dateDataObj']);
	
	// класс работы с менеджерами
	require_once(ROOT."/libs/php/classes/client_class.php");
	$data_arr = Client::requisites($client_id);
	
	$client_requisites=''; 
	
	if(count($data_arr) > 0)
	{
        foreach($data_arr as $requisites)
		{
		    if(!$requisit_id) $checked1 = (!isset($checked1))? 'checked' :'';
			else $checked1 = ($requisites['id'] == $requisit_id )? 'checked' :'';
			
			$style = ($requisites['id'] == $requisit_id )? 'background:#9EC38B':'';

			$client_requisites .= '<div class="row" style="'.$style.'"><label class="areement_set_label"><input type="radio" name="requisit_id" value="'.$requisites['id'].'" '.$checked1.'>'.$requisites['company'].'</label></div>';	
			
		}
		
	}
	else{
	
	    $client_requisites = '<div>реквизиты еще не созданы</div>';
	}
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// our_firms
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$our_firms ='';
	
	$data_arr = fetch_our_firms_data();
	//print_r($data_arr);
	if(!$data_arr['results_num'])
	{
	    $our_firms = $data_arr['result'];
	} 
	else if($data_arr['results_num'] > 0)
	{
        $result = $data_arr['result'];
	    while($item = mysql_fetch_assoc($result))
		{
		    $checked2 = (!isset($checked2))? 'checked' :'';
			$our_firms .= '<div class="row"><label class="areement_set_label"><input type="radio" name="our_firm_id" value="'.$item['id'].'" '.$checked2.'>'.$item['company'].'</label></div>';
		}
	}
	else{   $our_firms .= 'реквизиты еще не созданы'; }
	
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// varying part
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	/*$varying_part = '<td>
                    <div class="caption">Тип документа</div>
                    <label class="areement_set_label"><input type="radio" name="agreement_type" value="long_term" checked><label>долгосрочный договор</label><br>
                    <label class="areement_set_label"><input type="radio" name="1" value=""><label>спецификация</label><br>
                    </td>
                    <td style="text-align:center; vertical-align:middle;">
					    '.GETtoINPUT($_SERVER['QUERY_STRING'],array(''),array('section')).'<button type="submit" name="section" value="save_agreement">Создать</button>
                    </td>';
	*/	
    if($dateDataObj->doc_type=='spec'){
	         $varying_part = '<td style="vertical-align:top;padding-top:10px;padding-bottom:25px;" colspan="2">
							 <div class="row"><label class="areement_set_label"><input type="checkbox" onclick="show_hide_div_and_checkbox(this,\'existent_agreement_data\');" id="agreement_exists" name="agreement_exists"/>договор уже существует</label></div>
							 <div id="existent_agreement_data" style="display:none;">
							 <div class="row"><label class="areement_set_label"><input type="checkbox" onclick="show_hide_div_and_checkbox_II(this,\'existent_client_agreement_num_div\',\'existent_agreement_num_div\');" id="client_agreement" name="existent_agreement_client_agreement"> это клиентский договор</label></div>
							 <div id="existent_client_agreement_num_div" style="display:none;">
							 номер договора CL&nbsp;<input type="text" id="existent_client_agreement_num" name="existent_client_agreement_num" value="0001"><br /></div>
							 <div id="existent_agreement_num_div" style="display:block;">
							 номер договора &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="text" id="existent_agreement_num" name="existent_agreement_num" value="1/01'.date('y').'"><br /></div>
							 дата подписания : &nbsp;<input type="text" id="existent_agreement_date" name="existent_agreement_date" value="'.date('d.m.Y').'" /><br />
							 дата истечения : &nbsp;&nbsp;&nbsp;<input type="text" id="existent_agreement_expire_date" name="existent_agreement_expire_date" value="'.date('31.12.Y').'" /><br />
							 
							 спецификация №: &nbsp;<input type="text" id="existent_agreement_spec_num" name="existent_agreement_spec_num" value="1" autocomplete="off" />
							 </div>
						</td>
					 </tr>
					 <tr> 
						<td style="vertical-align:top;padding-top:10px;padding-bottom:25px;">
							 <div class="add_requisits_link"><a href="?page=clients&section=client_folder&subsection=client_card_table&client_id='.$client_id.'">добавить реквизиты</a></div>
						</td>
						<td style="text-align:right; vertical-align:top;padding-top:20px;padding-bottom:25px;">
							'.GETtoINPUT($_SERVER['QUERY_STRING'],array(''),array('section')).'<button class="button" type="submit" name="section" onclick="return validate_fieds(this);" value="prepayment">Далее</button>
						</td>';	
	}
	if($dateDataObj->doc_type=='oferta'){
	    $varying_part = '<tr> 
						<td style="vertical-align:top;padding-top:10px;padding-bottom:25px;">
							 <div class="add_requisits_link"><a href="?page=clients&section=client_folder&subsection=client_card_table&client_id='.$client_id.'">добавить реквизиты</a></div>
						</td>
						<td style="text-align:right; vertical-align:top;padding-top:20px;padding-bottom:25px;">
							'.GETtoINPUT($_SERVER['QUERY_STRING'],array(''),array('section')).'<button class="button" type="submit" name="section" onclick="return validate_fieds(this);" value="prepayment">Далее</button>
						</td>';	
	}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	// caption
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$caption = '<div class="caption" style="margin:0px:text-align:center">Создать документ между следующими юр.лицами:</div>';
	
	
	include('./skins/tpl/agreement/agr_setting.tpl');
?>
