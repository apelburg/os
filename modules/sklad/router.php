<?php
   
    // ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['suppliers']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS[$_GET['page']]['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

    $quick_button = '<div class="quick_button_div"  id="quick_button_div" style="background:none"></div>';


    /**
     * подключаем класс Счета
     */
    include_once './modules/invoice/invoice.class.php';
    $Invoice = new Invoice();

    /**
     * блок поиска
     */
    include_once './skins/tpl/common/quick_bar.tpl';

    /**
     * блок	краткой информации по клиенту
     */
    if(isset($_GET['client_id']) && $_GET['client_id']!=""){
        include_once './libs/php/classes/client_class.php';
        $Client = new Client;
        $Client->get_client__information((int)$_GET['client_id'],'invoice');
    }

    /**
     * общий шаблон
     */
    include'./skins/tpl/sklad/show.tpl';
	

	

	


	

	

	

	
	