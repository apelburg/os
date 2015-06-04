<?php
	//$page = !empty($_GET['page'])? ((strpos($_GET['page'],'/'))? substr($_GET['page'],0,strpos($_GET['page'],'/')):$_GET['page']) : 'main';
	$page = !empty($_GET['page'])? $_GET['page'] : FALSE ;
	$section = !empty($_GET['section'])? $_GET['section'] : FALSE ;
	$subsection = !empty($_GET['subsection'])? $_GET['subsection'] : FALSE ;
	
	$client_id = !empty($_GET['client_id'])? (int)$_GET['client_id'] : FALSE ;
	$num_page = !empty($_GET['num_page'])? (int)$_GET['num_page'] : 1 ;
	$query_num = !empty($_GET['query_num'])? $_GET['query_num'] : FALSE ;
	$quick_bar_tbl =  $quick_button = $view_button = '';

?>