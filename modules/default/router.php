<?php

// ** БЕЗОПАСНОСТЬ **
// проверяем выдан ли доступ на вход на эту страницу
// если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта
if(!@$ACCESS['default']['access']) exit($ACCESS_NOTICE);
// ** БЕЗОПАСНОСТЬ **

$notContentMessage = '<div style="margin-top:300px;text-align:center;">такого раздела не существует</div>';

if(isset($_GET['help'])){
   echo getHelp($_GET['help']);
   exit;
}

if(isset($_GET['show_calculator'])){
   include('./skins/tpl/calculators/calculators.tpl');
   exit;
}


/**
 * при инициализации модуля загрузки изображений Uploudify посылается лишний запрос в корень
 * т.к. с данного адреса далее по коду идёт редирект на кабинет в котором осущевствляется тяжёлый запрос - ставим исключение
 */
if(!isset($_GET) || count($_GET) == 0){
    exit( $notContentMessage );
}


/**
 * временное перенаправление, НЕ ПОДНИМАТЬ ВЫШЕ ВПЕРЕДИ СТОЯЩИХ ИНСТРУКЦИЙ
 */
header('Location:?'.addOrReplaceGetOnURL('page=cabinet&section=requests&subsection=no_worcked_men',''));

exit( $notContentMessage );


?>