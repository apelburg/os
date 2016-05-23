<?php
    
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/agreement_class.php");
	
    $section = (isset($_GET['agreement_id']))? 'agreement_editor' : 'save_agreement';
	
	if(isset($_GET['agreement_id'])){
	    $agreement_content = Agreement::fetch_agreement_content($_GET['agreement_id']);
	    $top_managers_data = Agreement::our_firm_manegement_faces($agreement_content['our_requisit_id']);
	}
	else if(isset($_GET['our_firm_id'])){
	    $top_managers_data = Agreement::our_firm_manegement_faces($_GET['our_firm_id']);
		if(!$top_managers_data){
		    echo '<br><br>Fatal error - requisits empty<br><br>';
		    exit;
		}
	}
	else{
	    echo '<br><br>Fatal error - our our_requisit_id or our_firm_id wasnt recived<br><br>';
		exit;
	}
	
	if(count($top_managers_data)<1){
	    echo '<br><br>Fatal error - our our_requisit_id or our_firm_id wasnt recived<br><br>';
		exit;
	}
	if(count($top_managers_data)==1){
	    header('Location:?'.addOrReplaceGetOnURL('section='.$section,''));
		exit;
	}
	
	foreach($top_managers_data as $top_manager){
	     $top_managers[] = '<label><input type="radio" name="signator_id" value="'.$top_manager['id'].'">'.$top_manager['name'].'</label>';
	}

	include './skins/tpl/agreement/signator_choosing.tpl';
	
?>