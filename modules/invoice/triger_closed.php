<?php

//echo __DIR__;
//echo __DIR__.'/../../../libs/mysqli.php';
//exit;
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
	include_once __DIR__."/../../../libs/mysqli.php";

	define("INVOICE_TBL","os__invoice_list"); // счета
	define("INVOICE_ROWS",'os__invoice_rows'); // строки позиций
	define("INVOICE_TTN",'os__invoice_TTN'); // строки ттн для таблицы счета
	define("INVOICE_PP",'os__invoice_PP'); // строки приходов по счетам
	define("INVOICE_COSTS",'os__invoice_costs');// счета от поставщиков
	define("INVOICE_COSTS_PAY",'os__invoice_costs_payment');// оплаты поставщикам
	define("INVOICE_COMMENTS","os__invoice_comments"); //

	define("MANAGERS_TBL","os__manager_list"); // таблица менеджеров



	include_once __DIR__."/../../../libs/php/classes/aplStdClass.php";

	include_once __DIR__."/../../libs/php/classes/invoice.class.php";

	# тригер для крон

	$InvoiceNotify = new InvoiceNotify();
	$InvoiceNotify->check_and_closed_invoice_CRON();

	exit('end script');
?>
	

	


	

	

	

	
	