<?php
    
	// ** БЕЗОПАСНОСТЬ **
	// проверяем выдан ли доступ на вход на эту страницу
	// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	if(!@$ACCESS['samples']['access']) exit($ACCESS_NOTICE);
	// ** БЕЗОПАСНОСТЬ **

include 'functions_samples.php';
?>
<div id="sample">
<div id="sample_menu_header">
    <ul>
    	<li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=='start')echo 'id="checked"'; ?> >
    		<a href="?page=samples&sample_page=start" title="требуется">Требуется</a>
		</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="request")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=request" title="Запрос наличия">Запрос наличия</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="ordered")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=ordered" title="Заказанные">Заказанные</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="received")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=received" title="Полученные">Полученные</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="client_hand")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=client_hand&sort=client" title="На руках у клиента">На руках у клиента</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="return")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=return" title="Возврат">Возврат</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="history")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=history" title="История">История</a>
    	</li>
        <li <?php if(isset($_GET['sample_page']) && $_GET['sample_page']=="all manager")echo 'id="checked"'; ?>>
			<a href="?page=samples&sample_page=all manager" title="Все менеджеры">Все менеджеры</a>
    	</li>
    </ul>
</div>
<?php
		switch ($_GET['sample_page']) {
			case 'start':
			include'skins/tpl/sample/show.tpl';//требуются
		break;
			case 'request':
			include'skins/tpl/sample/request.tpl';//требуются
		break; //запрос
			case 'ordered':
			include 'skins/tpl/sample/ordered.tpl';//требуются
		break; // заказанные
			case 'received':
			include'skins/tpl/sample/received.tpl';
		break; //полученные
			case 'client_hand':
			include'skins/tpl/sample/client_hand.tpl';
		break; //на руках у клиента
			case 'return':
			include'skins/tpl/sample/return.tpl';
		break; //возврат
			case 'history':
			include'skins/tpl/sample/history.tpl';
		break; //история
			case 'all manager':
			include'skins/tpl/sample/history.tpl';
		break; //Все менеджеры
			case 'sample_request':
			include'skins/tpl/sample/sample_request.tpl';//
			
		break; //запрос образцов
		
		default:
			echo "такого раздела не 222существует";
		}; 
	
?>