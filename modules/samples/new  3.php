<?php
$subject = 'Запрос наличия образцов';
$db= mysql_connect ("localhost","root","");
if(!$db) exit(mysql_error());
mysql_select_db ("apelburg_base",$db);
/*изменяем кодировку для корретного вывода PDF*/
mysql_query('SET NAMES cp1251');
mysql_query('SET CHARACTER SET cp1251');
mysql_query('SET COLLATION_CONNECTION="cp1251_general_ci"');

?>






include('libs/config.php');
include('libs/lock.php');
include('libs/php/common.php');
include('libs/autorization.php');
include('libs/variables.php');

//вывод таблицы запроса
include_once('libs/php/pdf/fpdf.php');
$query="
SELECT `s`.`id` AS `id_n` , `s`. * , `m`.`name` AS `name2` , `m`.`nickname` AS `nickname` , `b`. * , `cl`.`company` , `supp`.`nickName` , `supp`.`fullName`, `supp`.`id` AS `nickName_id`
FROM `samples` AS `s`
INNER JOIN order_manager__manager_list AS m ON s.manager_id = m.id
INNER JOIN base AS b ON s.tovar_id = b.id
INNER JOIN order_manager__client_list AS cl ON s.client_id = cl.id
INNER JOIN order_manager__supplier_list AS supp ON s.supplier_id = supp.id
WHERE s.id IN (
";
$i=1;
foreach ($_POST as $key => $value){
if(substr($key,0,2)=="c_"){
if($i==1){
$query.= substr($key,2)." ";
}else{
$query.= ",".substr($key,2)." ";
}
$i++;
}
}

$query.=") ORDER BY `supp`.`nickName` ASC";

//$print_html .= $query;
$result = mysql_query($query,$db); //запрос по выбранным фирмам фирмам для формирования заголовков;
if(!$result)exit(mysql_error());
?>

Онлайн сервис

$(function() {
var tabs = $( "#tabs" ).tabs();
tabs.find( ".ui-tabs-nav" ).sortable({
axis: "x",
stop: function() {
tabs.tabs( "refresh" );
}
});
});

';
$nickName[0]='';
$nickName_id[0]='';
if(mysql_num_rows($result) > 0){
while($item = mysql_fetch_assoc($result)){
//print_r($item);
$mass[]= array( $item['nickName'] => array( $item['name'], $item['art'], $item['price'], $item['id_n']));
if($supplier!=$item['nickName']){
$nickName[] = $item['nickName'];
$nickName_id[]=$item[nickName_id];
echo ''.$item['nickName'].' [#tabs-'.$w.']'. PHP_EOL;;
$w++;
}
$supplier=$item['nickName'];
}
}
echo ''. PHP_EOL;
echo '
'. PHP_EOL;

#############################################
########## / ВЫВОД ВКЛАДОК ##########
#############################################



#############################################
###### СОЗДАН�Е PDF ФАЙЛОВ ########
#############################################


$w=1; //счетчик вкладок
$supplier1=1; //счетчик поставщиков

foreach($mass as $key => $type){
foreach($mass[$key] as $arr => $name){
if ($supplier1==1){//выполняется при первом проходе(создает шапку документа)
//if(isset($phone) && $phone!=""){$phone=$phone.", ";}
$i=1; // счетчик количества товаров в каждой вкладке
/*******************************/

/*начало шапки*/
$pdf=new FPDF();
$pdf->AddFont('ArialMT','','arial.php');
$pdf->AddFont('Arial-BoldMT','','arialbd.php');
$pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
$pdf->AddPage();
$pdf->SetFont('Arial-BoldMT','',14); // задаем шрифт и его размер
$reportName="НАКЛАДНАЯ /ОБРАЗЦЫ /";
$pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
$pdf->SetFont('Arial-BoldMT','',8); // задаем шрифт и его размер 