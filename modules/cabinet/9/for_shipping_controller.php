<?php
// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
if(!@array_key_exists($section, $ACCESS['cabinet']['section']) ){
	echo $ACCESS_NOTICE;
	return;
};
// ** БЕЗОПАСНОСТЬ **


echo 'раздел '.$_GET["section"].'<br>';
echo 'подраздел '.$_GET["subsection"].'<br>';
echo 'тестим текст в разделе ';