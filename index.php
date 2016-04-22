<?php 
	// te
    header('Content-type: text/html; charset=utf-8');
	// ini_set('error_reporting', E_ALL);
	// при переходе на ановую версию php - вылазят сообщения об устаревших методах mysql
	// поэтому DEPRECATED пока что отключаем 
	ini_set('error_reporting', E_ALL ^ E_DEPRECATED);
	ini_set('display_errors', 1);
	ini_set('display_startup_errors', 1);
	set_time_limit(0);

    include_once('../libs/mysql.php');
	include_once('../libs/mysqli.php');
    include_once('libs/config.php');
	include_once('libs/lock.php');
	include_once('libs/variables.php');

	// aplStdClass
	include_once ROOT.'/../libs/php/classes/aplStdClass.php';
	include_once('libs/php/classes/clientSearchClass.php');
	new clientSearch;
	include_once('libs/access_installer.php');


	include_once('libs/php/classes/mail_class.php');
	include_once('libs/php/common.php');
    
    include_once('libs/autorization.php');
	

	
	// галлерея
	include_once ROOT.'/libs/php/classes/rt_KpGallery.class.php';
	new rtKpGallery;

	

    // ** БЕЗОПАСНОСТЬ **
	// если нет массива $ACCESS (права доступа) прерываем работу скирпта 
	if(!isset($ACCESS)) exit('доступ отсутсвует');

	//if(!($user_status == 1 || (isset($_SESSION['access']['come_back_in_own_profile']) && mysql_result(select_manager_data($_SESSION['access']['come_back_in_own_profile']),0,'access') == 1))) exit;

	
	ob_start();	
	//print_r($_SESSION);
    switch($page){
	
	  
	   
	   	case 'cabinet':
	   include_once 'modules/cabinet/router.php';
	   break;

	   case 'clients':
	   include_once 'modules/clients/router.php';
	   break;
	   
	   case 'suppliers':
	   include_once 'modules/suppliers/router.php';
	   break;
	   
	   case 'samples':
	   include_once 'modules/samples/router.php';
	   break;
	   
	   case '_test_rt':
	   include_once 'modules/_test_rt/router.php';
	   break;
	   
	   case 'client_folder':
	   include_once 'modules/client_folder/router.php';
	   break;

	   case 'option':
	   include_once 'modules/option/router.php';
	   break;
	   
	   case 'admin':
	   include_once 'modules/admin/router.php';
	   break;
	   
	   case 'agreement':
	   include_once 'modules/agreement/router.php';
	   break;
	   
	   case 'planner':
	   include_once 'modules/planner/router.php';
	   break; 
	 
	   default: 
	   include_once 'modules/default/router.php';
	   break;
	
	}
	$content = ob_get_contents();
	ob_get_clean();

	include_once'./skins/tpl/index.tpl';

?>
