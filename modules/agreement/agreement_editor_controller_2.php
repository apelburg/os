<?php
    // echo '<br><br><br><br><br><br><br><br><br><br><br><br>'.'1';
    // переделывать
	//$client_firm_acting_manegement_face = get_client_requisites_acting_manegement_face($agreement['client_requisit_id']);
    include_once(ROOT."/libs/php/classes/agreement_class.php");
	include_once(ROOT."/libs/php/classes/client_class.php");
	
    if(!isset($_GET['our_firm_id']))
	{
	   //echo 'не определен параметр: our_firm_id';
	   //exit;
    }
	if(!isset($_GET['requisit_id']))
	{
	    //echo 'не определен параметр: requisit_id';
		//exit;
    }
	
	if(isset($_POST['upload_file']))
	{
	    function error_note($note){
		    echo $note;
		    echo '<br>';
		    echo '<a href="'.$_SERVER['HTTP_REFERER'].'">назад</a>';
		    exit;
		   
		}
		
		function set_file_name_in_db($id,$file_name){
		    global $db;
			
			$query = "UPDATE `".GENERATED_AGREEMENTS_TBL."` SET `file_name` = '".$file_name."' WHERE `id` ='".$id."'"; 
		    mysql_query($query,$db) or die (mysql_error());
		   
		}

		if($_FILES['file']['error']){
		    if($_FILES['file']['error'] ==4) error_note('вы не выбрали файл');
		    else error_note('ошибка загрузки файла '.$_FILES['file']['error'].', скорее всего размер файла превышает 2 мегабайта');
	    }
		
		$agreement_num = $_POST['agreement_num'];
		$path = $_POST['path'];
		$file_extention = substr($_FILES['file']['name'],strrpos($_FILES['file']['name'],'.'));
		//echo $_FILES['file']['size'];//$file_extention;
		
		/*echo '<pre>';
		print_r($_FILES);
		echo '</pre>';
		exit;*/
		$allowable_extentions = array('.jpg','.png','.jpeg','.gif','.xls','.xlsx','.docx','.pdf');
		
		if(!in_array(strtolower($file_extention),$allowable_extentions)) error_note('недопустимый формат файла');
		    
		$file_name = str_replace(array('/','-'),'_',$agreement_num).$file_extention;
		//echo $path.$file_name;
		
		
		if(copy($_FILES['file']['tmp_name'],$path.$file_name)) 
		{
		    set_file_name_in_db($_POST['id'],$file_name);
			
			header('Location:'.$_SERVER['HTTP_REFERER']);
		}
		else
		{
		    error_note("ошибка загрузки");
		}
		
		
		
		exit;

    }
	
	
    unset($_SESSION['back_url']);
	
	
	
	
	if($agreement_id)
	{
	    if((isset($_GET['agreement_type']) && $_GET['agreement_type'] == 'long_term') && isset($_SESSION['data_for_specification']) && !isset($_GET['open']))
		{  
		//echo '<br><br><br><br><br><br><br><br><br><br><br><br>'.'3';
		    /*
			if(!isset($_GET['short_description']))
			{
				include '../../skins/tpl/admin/order_manager/agreement/specification_short_description_setting.tpl';
				exit;
			}
			*/
			$agreement = fetch_agreement_content($agreement_id);
			$our_firm_acting_manegement_face = our_firm_acting_manegement_face($agreement['our_requisit_id']);
			$client_firm_acting_manegement_face = get_client_requisites_acting_manegement_face($agreement['client_requisit_id']);
			//echo '<pre>'; print_r($client_firm_acting_manegement_face); echo '</pre>';
			//$client_firm_acting_manegement_face = array('position' => 'position','position_in_padeg' => 'position_in_padeg','name' => 'name','name_in_padeg' => 'name_in_padeg','basic_doc' =>'basic_doc');
            $spec_num = (!empty($_GET['existent_agreement_spec_num']))? $_GET['existent_agreement_spec_num']: false;
			
			//echo $_SESSION['data_for_specification'];
			//exit;
			//exit; 
			include_once(ROOT."/libs/php/classes/agreement_class.php");
	        $specification_num = Agreement::add_items_for_specification($spec_num,$_SESSION['data_for_specification'],$client_id,$agreement_id,$agreement['date'],$our_firm_acting_manegement_face,$client_firm_acting_manegement_face,$_GET['date'],$_GET['short_description'],urldecode($_GET['address']),$_GET['prepayment']);
	
			 
			//unset($_SESSION['data_for_specification']);  
			//  
			header('Location:?'.addOrReplaceGetOnURL('open=specification&specification_num='.$specification_num,'short_description&conrtol_num')); 
			exit;    
		}
		
		$agreement = fetch_agreement_content($agreement_id);
		
		$date_arr = explode('-',$agreement['date']);
	    $agreement_date =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		$agreement_year_folder = $date_arr[0];
		
	    //print_r($form_data);
		$date_arr = explode('-',$agreement['expire_date']);
	    $agreement_expire_date =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		
		$our_firm = fetch_our_certain_firm_data($agreement['our_requisit_id']);
	    // $client_firm = get_client_requisites($agreement['client_requisit_id']);
	    // echo '<pre>'; print_r($client_firm); echo '</pre>';
		
		$client_firm =  Client::fetch_requisites($agreement['client_requisit_id']);
		// echo '<pre>'; print_r($agreement); echo '</pre>';
		// echo '<pre>'.$agreement['client_requisit_id']; print_r($client_firm); echo '</pre>';
		// exit; 
		
		if(isset($_GET['open']))
		{
		
		    if($_GET['open'] == 'all')
			{
				$specifications =  Agreement::fetch_specifications($client_id,$agreement_id);
				while($row = $specifications->fetch_assoc())
				{
					if(!isset($specifications_arr[$row['specification_num']])) $specifications_arr[$row['specification_num']]= array();
					array_push($specifications_arr[$row['specification_num']],$row);
				
				}
			}
			else if($_GET['open'] == 'empty')
			{
				$specifications_arr = array();
			}
			else if($_GET['open'] == 'specification')
			{
			    $specification =  Agreement::fetch_specification($client_id,$agreement_id,(int)$_GET['specification_num']);
				while($row = $specification->fetch_assoc())
				{
					$specifications_arr[$row['specification_num']][] = $row;				
				}

			}
			
			/* 
			echo '<pre>';
            print_r($specifications_arr);
			echo '</pre>';
			*/
			$specifications = '';
			$table ='';
			foreach($specifications_arr as $key => $val)
			{
			  
				$itogo=0;
				$nds=0;
				$table .= '<table id="spec_tbl" class="spec_tbl"><tr class="bold_font"><td>№</td><td>Наименование и<br>описание продукции</td><td>Кол-во продукции</td><td colspan="2">стоимость за штуку</td><td colspan="2">Общая стоимость</td>';
				
				foreach($val as $key2 => $val2)
				{
				    $table .= '</tr><tr><td class="num">'.($key2+1).'</td><td class="name">'.$val[$key2]['name'].'</td><td class="quantity">'.$val[$key2]['quantity'].'</td><td class="price">'.$val[$key2]['price'].'</td><td class="currensy">р.</td><td class="price">'.$val[$key2]['summ'].'</td><td class="currensy">р.</td>';
					$itogo += (float)$val[$key2]['summ'];
					$nds += round(($val[$key2]['summ']/118*18), 2);
				}
				$table .= '</tr>';
				$table .= '<tr class="bold_font"><td colspan="5">Итого с НДС</td><td class="price">'.number_format($itogo,"2",".",'').'</td><td class="currensy">р.</td></tr>';
				$table .= '<tr class="bold_font"><td colspan="5">Из них НДС (18%)</td><td class="price">'.number_format($nds,"2",".",'').'</td><td class="currensy">р.</td></tr>';
				$table .= '</table>';
				
				
				$date_arr = explode('-',$val[0]['date']);
				$specification_date =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
				

				list($first_part,$second_part) = explode('-',number_format($itogo,2,'-',''));
				$for_pay = num_word_transfer($first_part);
				$for_pay = strtr($for_pay,$desjatichn_word_transfer_arr);
			
				list($first_part_nds,$second_part_nds) = explode('-',number_format($nds,2,'-',''));
				//$for_pay_nds = num_word_transfer($first_part_nds);
				//$for_pay_nds = strtr($for_pay_nds,$desjatichn_word_transfer_arr);
				
				$for_pay = '('.$for_pay.' рублей '.$second_part.' коп.), в т.ч. НДС 18% '.$first_part_nds.' руб. '.$second_part_nds.' коп.';
				$for_pay = strtr($for_pay,$change_word_ending_arr_I);
				$for_pay = strtr($for_pay,$change_word_ending_arr_II);
				$for_pay = strtr($for_pay,$change_word_ending_arr_III);
				$for_pay = strtr($for_pay,$change_word_ending_arr_IV);

				
				$our_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'our_requisit_id','coll'=>'id','val'=>$agreement_id));
	$client_requisit_id = fetchOneValFromGeneratedAgreementTbl(array('retrieve'=>'client_requisit_id','coll'=>'id','val'=>$agreement_id));
				
				$file_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.$client_id.'/'.$agreement_year_folder.'/'.$_GET['agreement_type'].'/'.$agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'].'/specifications/'.$key.'.tpl';
				
				$fd = fopen($file_name,"r");
				$content = fread($fd,filesize($file_name));
				fclose($fd);
				//$content = strip_tags($content,'<p><a><span><table><tr><td><tbody><div><strong><br /><br>');
		 
				
				
				
                 
                
                $specification_num = '<?php echo $key; ?>';
				$agreement_num = '<?php echo $agreement[\'agreement_num\']; ?>';
				$agreementDate = '<?php echo $agreement_date; ?>';
				$specificationDate = '<?php echo $specification_date; ?>';
				$specification_table = '<?php echo $table; ?>';
				
				
				$production_term = '<span class="field_for_fill" managed="text" bd_row_id="<?php echo $specifications_arr[$key][0][\'id\']; ?>" bd_field="item_production_term" file_link="1"><?php echo $specifications_arr[$key][0][\'item_production_term\']; ?>&nbsp;</span>';
				
				$prepayment_term = '<?php include ($_SERVER[\'DOCUMENT_ROOT\'].\'/os/modules/agreement/agreements_templates/\'.$specifications_arr[$key][0][\'prepayment\'].\'_prepaiment_conditions.tpl\'); ?>';
				
				$delivery_term = '<span class="field_for_fill" managed="text" bd_row_id="<?php echo $specifications_arr[$key][0][\'id\']; ?>" bd_field="makets_delivery_term" file_link="1"><?php echo $specifications_arr[$key][0][\'makets_delivery_term\']; ?>&nbsp;</span>';
				
				if($specifications_arr[$key][0]['id'] < 2755){ // не удалять для совместимости ( предыдущий вариант обработки адреса доставки )
				    $delivery_adderss = '<span class="field_for_fill" managed="text" bd_row_id="<?php echo $specifications_arr[$key][0][\'id\']; ?>" bd_field="address" file_link="1"><?php echo $specifications_arr[$key][0][\'address\']; ?>&nbsp;</span>';
				}
				else{
					$delivery_adderss_tpl_path = ($specifications_arr[$key][0]['address'] == 'samo_vivoz')? ROOT.'/modules/agreement/agreements_templates/samo_vivoz.tpl':ROOT.'/modules/agreement/agreements_templates/nasha_dostavka.tpl';
					$fd = fopen($delivery_adderss_tpl_path,'rb');
					$delivery_adderss_string = fread($fd,filesize($delivery_adderss_tpl_path));
					fclose($fd);
					$delivery_adderss = str_replace('[DELIVERY_ADDRESS]',$specifications_arr[$key][0]['address'],$delivery_adderss_string );
				}

				
				$our_comp_full_name = '<?php echo $agreement[\'our_comp_full_name\']; ?>';
				$client_comp_full_name = '<?php echo $agreement[\'client_comp_full_name\']; ?>';
				$for_pay_summ = '<?php echo number_format($itogo,"2",".",""); ?>';
				$for_pay_text = '<?php echo $for_pay; ?>';
				
				 
				$client_firm_legal_address = '<?php echo $client_firm[\'legal_address\']; ?>';
				$client_firm_postal_address = '<?php echo $client_firm[\'postal_address\']; ?>';
				$client_firm_inn = '<?php echo $client_firm[\'inn\']; ?>';
				$client_firm_ogrn = '<?php echo $client_firm[\'ogrn\']; ?>';
				$client_firm_kpp = '<?php echo $client_firm[\'kpp\']; ?>';
				$client_firm_r_account = '<?php echo $client_firm[\'r_account\']; ?>';
				$client_firm_bank = '<?php echo $client_firm[\'bank\']; ?>';
			    $client_firm_bik = '<?php echo $client_firm[\'bik\']; ?>';
				$client_firm_cor_account = '<?php echo $client_firm[\'cor_account\']; ?>';
				$client_firm_director = '<?php echo $client_firm[\'director\']; ?>';
		
				 
				$our_firm_legal_address = '<?php echo $our_firm[\'legal_address\']; ?>';
				$our_firm_postal_address = '<?php echo $our_firm[\'postal_address\']; ?>';
				$our_firm_inn = '<?php echo $our_firm[\'inn\']; ?>';
				$our_firm_ogrn = '<?php echo $our_firm[\'ogrn\']; ?>';
				$our_firm_kpp = '<?php echo $our_firm[\'kpp\']; ?>';
				$our_firm_r_account = '<?php echo $our_firm[\'r_account\']; ?>';
				$our_firm_bank = '<?php echo $our_firm[\'bank\']; ?>';
				$our_firm_bik = '<?php echo $our_firm[\'bik\']; ?>';
				$our_firm_cor_account = '<?php echo $our_firm[\'cor_account\']; ?>';
				
				//$our_director = '<?php echo $specifications_arr[$key][0][\'our_chief\']; ? >';
				$our_director_data = $specifications_arr[$key][0]['our_chief'];;
                $our_director_data = explode(' ',$our_director_data);
                $short_our_director = $our_director_data[0];
                if(count($our_director_data)>1)for($i = 1 ; $i < count($our_director_data);$i++){  $short_our_director .= ' '.mb_substr($our_director_data[$i],0,1, 'UTF-8').'.';}
                $our_director = '<?php echo $short_our_director; ?>';
				
				
				
				
				$our_director_in_padeg = '<?php echo $specifications_arr[$key][0][\'our_chief_in_padeg\']; ?>';
                $our_contface_position =  '<?php echo $specifications_arr[$key][0][\'our_chief_position\']; ?>';
				$our_contface_position_in_padeg =  '<?php echo $specifications_arr[$key][0][\'our_chief_position_in_padeg\']; ?>';
                $our_basic_doc =  '<?php echo $specifications_arr[$key][0][\'our_basic_doc\']; ?>';
				
				//$client_director = '< ? php echo $specifications_arr[$key][0][\'client_chief\']; ? >';
				
				
				
				$client_director_data = $specifications_arr[$key][0]['client_chief'];;
                $client_director_data = explode(' ',$client_director_data);
                $short_client_director = $client_director_data[0];
                if(count($client_director_data)>1)for($i = 1 ; $i < count($client_director_data);$i++){  $short_client_director .= ' '.mb_substr($client_director_data[$i],0,1, 'UTF-8').'.';}
                $client_director = '<?php echo $short_client_director; ?>';

				$client_director_in_padeg = '<?php echo $specifications_arr[$key][0][\'client_chief_in_padeg\']; ?>';
                $client_contface_position =  '<?php echo $specifications_arr[$key][0][\'client_chief_position\']; ?>';
				$client_contface_position_in_padeg =  '<?php echo $specifications_arr[$key][0][\'client_chief_position_in_padeg\']; ?>';
                $client_basic_doc =  '<?php echo $specifications_arr[$key][0][\'client_basic_doc\']; ?>';

			    
               
				$content = str_replace('[SPECIFICATION_NUM]',$specification_num,$content );
				$content = str_replace('[SPECIFICATION_DATE]',$specificationDate,$content );
				$content = str_replace('[AGREEMENT_NUM]',$agreement_num,$content );
				$content = str_replace('[AGREEMENT_DATE]',$agreementDate,$content );
				$content = str_replace('[PRODUCTION_TERM]',$production_term,$content );
				$content = str_replace('[PREPAMENT_TERM]',$prepayment_term,$content );
				$content = str_replace('[DELIVERY_TERM]',$delivery_term,$content );
				$content = str_replace('[DELIVERY_ADDRESS]',$delivery_adderss,$content );
				$content = str_replace('[FOR_PAY]',$for_pay_summ,$content );
				$content = str_replace('[FOR_PAY_TEXT]',$for_pay_text,$content );
				
				$content = str_replace('[SPECIFICATION_TABLE]',$specification_table,$content );


                $content = str_replace('[OUR_DIRECTOR]',$our_director,$content );
				$content = str_replace('[OUR_DIRECTOR_IN_PADEG]',$our_director_in_padeg,$content );
		        $content = str_replace('[OUR_DIRECTOR_POSITION]',$our_contface_position,$content );
				$content = str_replace('[OUR_DIRECTOR_POSITION_IN_PADEG]',$our_contface_position_in_padeg,$content );
			    $content = str_replace('[OUR_BASIC_DOC]',$our_basic_doc ,$content );
				
				$content = str_replace('[CLIENT_DIRECTOR]',$client_director,$content );
				$content = str_replace('[CLIENT_DIRECTOR_IN_PADEG]',$client_director_in_padeg,$content );
		        $content = str_replace('[CLIENT_DIRECTOR_POSITION]',$client_contface_position,$content );
				$content = str_replace('[CLIENT_DIRECTOR_POSITION_IN_PADEG]',$client_contface_position_in_padeg,$content );
			    $content = str_replace('[CLIENT_BASIC_DOC]',$client_basic_doc ,$content );
	
				
				
				$content = str_replace('[CLIENT_DIRECTOR]',$client_director,$content );
				 
			    $content = str_replace('[OUR_COMP_FULL_NAME]',$our_comp_full_name,$content );
			    $content = str_replace('[CLIENT_COMP_FULL_NAME]',$client_comp_full_name,$content );
				 
				
				$content = str_replace('[OUR_COMP_LEGAL_ADDRESS]',$our_firm_legal_address,$content );
				$content = str_replace('[OUR_COMP_POSTAL_ADDRESS]',$our_firm_postal_address,$content );
				$content = str_replace('[OUR_COMP_INN]',$our_firm_inn,$content );
				$content = str_replace('[OUR_COMP_OGRN]',$our_firm_ogrn,$content ); 

				$content = str_replace('[OUR_COMP_KPP]',$our_firm_kpp,$content );
				$content = str_replace('[OUR_COMP_R_ACCOUNT]',$our_firm_r_account,$content );
				$content = str_replace('[OUR_COMP_BANK]',$our_firm_bank,$content );
				$content = str_replace('[OUR_COMP_BIK]',$our_firm_bik,$content );
				$content = str_replace('[OUR_COMP_COR_ACCOUNT]',$our_firm_cor_account,$content );
				 
				$content = str_replace('[CLIENT_COMP_LEGAL_ADDRESS]',$client_firm_legal_address,$content );
				$content = str_replace('[CLIENT_COMP_POSTAL_ADDRESS]',$client_firm_postal_address,$content );
				$content = str_replace('[CLIENT_COMP_INN]',$client_firm_inn,$content );
				$content = str_replace('[CLIENT_COMP_OGRN]',$client_firm_ogrn,$content );
				
				$content = str_replace('[CLIENT_COMP_KPP]',$client_firm_kpp,$content );
				$content = str_replace('[CLIENT_COMP_R_ACCOUNT]',$client_firm_r_account,$content );
				$content = str_replace('[CLIENT_COMP_BANK]',$client_firm_bank,$content );
				$content = str_replace('[CLIENT_COMP_BIK]',$client_firm_bik,$content );
				$content = str_replace('[CLIENT_COMP_COR_ACCOUNT]',$client_firm_cor_account,$content );
						
				
				
				ob_start();
								
			          eval('?>'.$content.'<?php ');
				    // include('agreement/agreements_templates/specification.tpl');
				
				$specifications .= ob_get_contents();
				ob_get_clean();
				
				$table ='';

			}
			
			
		}
		else
		{
			$specifications = '';
			unset($_SESSION['data_for_specification']);
		}

	}
	else
	{
		
	}
	
     ob_start();
			 
	if(isset($_GET['requisit_id']) || (isset($_GET['open']) && $_GET['open']!= 'specification')){

	   if((boolean)$agreement['standart'] && !(boolean)$agreement['existent']){
		
	     $file_name = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.$client_id.'/'.$agreement_year_folder.'/'.$_GET['agreement_type'].'/'.$agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'].'/agreement.tpl';
	     $fd = fopen($file_name,"r");
	     $content = fread($fd,filesize($file_name));
	     fclose($fd);
		 $content = strip_tags($content,'<p><span><table><tr><td><tbody><div><strong><br /><br>');
		 
		 /*
	     $our_firm_full_name = '<span class="field_for_fill" managed="text" bd_row_id="<?php echo $agreement[\'id\']; ?>" bd_field="our_comp_full_name" file_link="0"><?php echo $agreement[\'our_comp_full_name\']; ?></span>';	
		 $client_firm_full_name = '<span class="field_for_fill" managed="text" bd_row_id="<?php echo $agreement[\'id\']; ?>" bd_field="client_comp_full_name" file_link="0"><?php echo $agreement[\'client_comp_full_name\']; ?>&nbsp;</span>';
		 
		 */
		 
		 $date_arr = explode('-',$agreement['date']);
	     $agreement['date'] =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		 $date_arr = explode('-',$agreement['expire_date']);
	     $agreement['expire_date'] =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		
		
		 $agreement_num = '<strong><?php echo $agreement[\'agreement_num\']; ?></strong>';
		 $agreement_date = '<?php echo $agreement[\'date\']; ?>';
		 $agreement_expire_date = '<?php echo $agreement[\'expire_date\']; ?>';

		
		 
		 $our_comp_full_name = '<?php echo $agreement[\'our_comp_full_name\']; ?>';
		 $client_comp_full_name = '<?php echo $agreement[\'client_comp_full_name\']; ?>';
		 
		 $client_firm_legal_address = '<?php echo $client_firm[\'legal_address\']; ?>';
		 $client_firm_postal_address = '<?php echo $client_firm[\'postal_address\']; ?>';
		 $client_firm_inn = '<?php echo $client_firm[\'inn\']; ?>';
		 $client_firm_ogrn = '<?php echo $client_firm[\'ogrn\']; ?>';
		 $client_firm_kpp = '<?php echo $client_firm[\'kpp\']; ?>';
		 $client_firm_r_account = '<?php echo $client_firm[\'r_account\']; ?>';
		 $client_firm_bank = '<?php echo $client_firm[\'bank\']; ?>';
		 $client_firm_bik = '<?php echo $client_firm[\'bik\']; ?>';
		 $client_firm_cor_account = '<?php echo $client_firm[\'cor_account\']; ?>';
		 $client_firm_chief = '<?php echo $client_firm[\'chief\']; ?>';

		 
		 $our_firm_legal_address = '<?php echo $our_firm[\'legal_address\']; ?>';
		 $our_firm_postal_address = '<?php echo $our_firm[\'postal_address\']; ?>';
		 $our_firm_inn = '<?php echo $our_firm[\'inn\']; ?>';
		 $our_firm_ogrn = '<?php echo $our_firm[\'ogrn\']; ?>';
		 $our_firm_kpp = '<?php echo $our_firm[\'kpp\']; ?>';
		 $our_firm_r_account = '<?php echo $our_firm[\'r_account\']; ?>';
		 $our_firm_bank = '<?php echo $our_firm[\'bank\']; ?>';
		 $our_firm_bik = '<?php echo $our_firm[\'bik\']; ?>';
		 $our_firm_cor_account = '<?php echo $our_firm[\'cor_account\']; ?>';
		 $our_firm_chief = '<?php echo $our_firm[\'chief\']; ?>';
		 
	    // $our_chief = '<?php echo $agreement[\'our_chief\']; ? >';
		 $our_director_data = $agreement['our_chief'];
		 $our_director_data = explode(' ',$our_director_data);
		 $short_our_director = $our_director_data[0];
		 if(count($our_director_data)>1)for($i = 1 ; $i < count($our_director_data);$i++){  $short_our_director .= ' '.mb_substr($our_director_data[$i],0,1, 'UTF-8').'.';}
		 $our_chief = '<?php echo $short_our_director; ?>';
		 
		 
		 $our_chief_in_padeg = '<?php echo $agreement[\'our_chief_in_padeg\']; ?>';
         $our_chief_position =  '<?php echo $agreement[\'our_chief_position\']; ?>';
		 $our_chief_position_in_padeg =  '<?php echo $agreement[\'our_chief_position_in_padeg\']; ?>';
         $our_basic_doc =  '<?php echo $agreement[\'our_basic_doc\']; ?>';

         //$client_chief = '<?php echo $agreement[\'client_chief\']; ? >';
		 $client_director_data = $agreement['client_chief'];;
		 $client_director_data = explode(' ',$client_director_data);
		 $short_client_director = $client_director_data[0];
		 if(count($client_director_data)>1)for($i = 1 ; $i < count($client_director_data);$i++){  $short_client_director .= ' '.mb_substr($client_director_data[$i],0,1, 'UTF-8').'.';}
		 $client_chief = '<?php echo $short_client_director; ?>';
		 
		 
		 $client_chief_in_padeg = '<?php echo $agreement[\'client_chief_in_padeg\']; ?>';
         $client_chief_position =  '<?php echo $agreement[\'client_chief_position\']; ?>';
		 $client_chief_position_in_padeg =  '<?php echo $agreement[\'client_chief_position_in_padeg\']; ?>';
         $client_basic_doc = '<?php echo $agreement[\'client_basic_doc\']; ?>';

         
        
		 $content = str_replace('[AGREEMENT_NUM]',$agreement_num,$content );
		 $content = str_replace('[AGREEMENT_DATE]',$agreement_date,$content );
		 $content = str_replace('[AGREEMENT_EXPIRE_DATE]',$agreement_expire_date,$content );
		 
		 $content = str_replace('[OUR_DIRECTOR]',$our_chief,$content );
		 $content = str_replace('[OUR_DIRECTOR_IN_PADEG]',$our_chief_in_padeg,$content );
		 $content = str_replace('[OUR_DIRECTOR_POSITION]',$our_chief_position,$content );
		 $content = str_replace('[OUR_DIRECTOR_POSITION_IN_PADEG]',$our_chief_position_in_padeg,$content );
         $content = str_replace('[OUR_BASIC_DOC]',$our_basic_doc ,$content ); 
		 $content = str_replace('[OUR_COMP_FULL_NAME]',$our_comp_full_name,$content );
		 
		 
		 $content = str_replace('[OUR_COMP_LEGAL_ADDRESS]',$our_firm_legal_address,$content );
		 $content = str_replace('[OUR_COMP_POSTAL_ADDRESS]',$our_firm_postal_address,$content );
		 $content = str_replace('[OUR_COMP_INN]',$our_firm_inn,$content );
		 $content = str_replace('[OUR_COMP_OGRN]',$our_firm_ogrn,$content );
		 $content = str_replace('[OUR_COMP_KPP]',$our_firm_kpp,$content );
		 $content = str_replace('[OUR_COMP_R_ACCOUNT]',$our_firm_r_account,$content );
		 $content = str_replace('[OUR_COMP_BANK]',$our_firm_bank,$content );
		 $content = str_replace('[OUR_COMP_BIK]',$our_firm_bik,$content );
		 $content = str_replace('[OUR_COMP_COR_ACCOUNT]',$our_firm_cor_account,$content );

		 
		 $content = str_replace('[CLIENT_DIRECTOR]',$client_chief,$content );
		 $content = str_replace('[CLIENT_DIRECTOR_IN_PADEG]',$client_chief_in_padeg,$content );
		 $content = str_replace('[CLIENT_DIRECTOR_POSITION]',$client_chief_position,$content );
		 $content = str_replace('[CLIENT_DIRECTOR_POSITION_IN_PADEG]',$client_chief_position_in_padeg,$content );
         $content = str_replace('[CLIENT_BASIC_DOC]',$client_basic_doc ,$content );
         $content = str_replace('[CLIENT_COMP_FULL_NAME]',$client_comp_full_name,$content );
		 
		 $content = str_replace('[CLIENT_COMP_LEGAL_ADDRESS]',$client_firm_legal_address,$content );
		 $content = str_replace('[CLIENT_COMP_POSTAL_ADDRESS]',$client_firm_postal_address,$content );
		 $content = str_replace('[CLIENT_COMP_INN]',$client_firm_inn,$content );
		 $content = str_replace('[CLIENT_COMP_OGRN]',$client_firm_ogrn,$content );
		 $content = str_replace('[CLIENT_COMP_KPP]',$client_firm_kpp,$content );
		 $content = str_replace('[CLIENT_COMP_R_ACCOUNT]',$client_firm_r_account,$content );
		 $content = str_replace('[CLIENT_COMP_BANK]',$client_firm_bank,$content );
		 $content = str_replace('[CLIENT_COMP_BIK]',$client_firm_bik,$content );
		 $content = str_replace('[CLIENT_COMP_COR_ACCOUNT]',$client_firm_cor_account,$content );	 
		 
		 
	     eval('?>'.$content.'<?php ');
		 
	  }
	  else if((boolean)$agreement['standart'] && (boolean)$agreement['existent']){
	     echo '<div class="agreement_tpl" style="margin:100px 0px 0px 220px;"><div style="margin-top:40px;"><span style="font-size:18px;">бумажный договоp № '.$agreement['agreement_num'].'</span>, дата создания  '.$agreement['date'].', действителен до '.$agreement['expire_date'].' </div></div><br /><br />';
		 
	  }
	  else
	  {
	  
	      $date_arr = explode('-',$agreement['date']);
	      $agreement['date'] =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		  $date_arr = explode('-',$agreement['expire_date']);
	      $agreement['expire_date'] =$date_arr[2].' '.$month_day_name_arr[(int)$date_arr[1]].' '.$date_arr[0] .' г.';
		 
		 
		  $content = '<div style="margin-top:40px;"><span style="font-size:18px;">клиентский договор № '.$agreement['agreement_num'].'</span>, дата создания  '.$agreement['date'].', действителен до '.$agreement['expire_date'].' </div>';
		  
		  $path = 'data/agreements/'.$client_id.'/'.$agreement_year_folder.'/'.$_GET['agreement_type'].'/'.$agreement['our_requisit_id'].'_'.$agreement['client_requisit_id'].'/'; 
		  
		  if($agreement['file_name'] != ''){
		      $content .=  '<div style="margin-top:40px;height:100px;width:550px;float:left;">файл договора - '.$agreement['file_name'];
			  $content .=  '&nbsp;&nbsp;&nbsp;<a href="'.$path.str_replace(array('/','-'),'_',$agreement['file_name']).'">скачать файл</a></div>';
			  $content .=  '<div style="margin-top:40px;">загрузить новый файл</div>';
			  
		  }
		  else 
		  {
		      $content .=  '<div style="margin-top:40px;height:100px;width:550px;float:left;">файл договора не загружен</div>';
			  $content .=  '<div style="margin-top:40px;">загрузить файл</div></div>';
			  
			  
		  }
		  
		 $content .=  '<div style="color:#888;font-size:10px;">*максимально допустимый размер 2 мегабайта</div>';
		// $content .=  '<div class="clear_div"></div>';

		 
				
		  $tpl_name = './skins/tpl/agreement/upload.tpl';
		  $fd = fopen($tpl_name,'r');
		  $tpl = fread($fd,filesize($tpl_name));
		  fclose($fd);
		  
		  ob_start(); 	
		  
		     eval(' ?>'.$tpl.'<?php ');	
		  
		  $content .= ob_get_contents();
	      ob_get_clean();
		  
		  	
		  echo '<div class="agreement_tpl">'.$content.'</div><div class="clear_div"></div><br /><br />';	
		  
	  }
		 // echo($content);
	}
	$agreement_content = ob_get_contents();
	ob_get_clean();
	
	include('./skins/tpl/agreement/agreement.tpl');
   

?>