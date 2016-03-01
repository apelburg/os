<?php

   
	if(isset($_GET['agreement_id']))
	{
	    include_once(ROOT."/libs/php/classes/agreement_class.php");
		
		$data = Agreement::fetch_agreement_content($agreement_id);
		$requisit_id = $data['client_requisit_id'];

	}
	 
 
	include_once(ROOT."/libs/php/classes/client_class.php");
	$addresses_arr = Client::get_addres($client_id);
	$addresses = '';
	foreach($addresses_arr as $data){
	     $adress  = ($data['city']!='')? $data['city']:'';
		 $adress .= ($data['street']!='')? ', '.$data['street']:'';
		 $adress .= ($data['house_number']!=0)? ', дом.'.$data['house_number']:'';
		 $adress .= ($data['korpus']!=0)? ', корп.'.$data['korpus']:'';
		 $adress .= ($data['office']!=0)? ', оф.'.$data['office']:'';
		 $adress .= ($data['office']!=0)? ', литер'.$data['liter']:'';
		 $adress .= ($data['office']!=0)? ', строение'.$data['bilding']:'';
		 
		 //$adress = ($adress!='')? $adress:'адрес не указан';
		 if($adress=='') continue;
	
	    $addresses .= "<div class='prepayment_row' style='margin:10px 0px 0px 10px;'><a href='?".addOrReplaceGetOnURL("section=short_description")."&address=".urlencode($adress)."'>".$adress."</a></div>";
	}
	
    include './skins/tpl/agreement/delivery_setting.tpl';
   
?>
