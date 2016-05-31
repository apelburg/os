<?php

	include ("/var/www/admin/data/www/apelburg.ru/os/libs/mysqli.php");

	define("INVOICE_TBL","os__invoice_list"); // счета
	define("INVOICE_ROWS",'os__invoice_rows'); // строки позиций
	define("INVOICE_TTN",'os__invoice_TTN'); // строки ттн для таблицы счета
	define("INVOICE_PP",'os__invoice_PP'); // строки приходов по счетам
	define("INVOICE_COSTS",'os__invoice_costs');// счета от поставщиков
	define("INVOICE_COSTS_PAY",'os__invoice_costs_payment');// оплаты поставщикам
	define("INVOICE_COMMENTS","os__invoice_comments"); //

	include_once "/var/www/admin/data/www/apelburg.ru/libs/php/classes/aplStdClass.php";
	include_once "/var/www/admin/data/www/apelburg.ru/os/libs/php/classes/invoice.class.php";

	# тригер для крон

	Invoice::triger_check_and_closed_invoice_CRON($mysqli);
?>
	

	


	

	

	

	
	