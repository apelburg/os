<?php
//вывод таблицы запроса
$query="
SELECT `s`.`id` AS `id_n` , `s`. * , `m`.`name` AS `name2` , `m`.`nickname` AS `nickname` , `b`. * , `cl`.`company` , `supp`.`nickName` , `supp`.`fullName`
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
$result = mysql_query($query,$db);   //запрос по выбранным фирмам фирмам для формирования заголовков;
if(!$result)exit(mysql_error());
$suplier=1; // счетчик поставщиков для вывода вкладок
$w=1; //счетчик для вывода вкладок

if(mysql_num_rows($result) > 0){
	while($item = mysql_fetch_assoc($result)){
    		//print_r($item);
            $mass[]= array( $item['nickName'] => array( $item['name'],  $item['art'], $item['price']));
    		if($suplier!=$item['nickName']){
            	$print_html .= '<li><a href="#tabs-'.$w.'">'.$item['nickName'].'</a></li>'. PHP_EOL;;	
                $w++;	
			}
            $suplier=$item['nickName'];
	}
} 



$w=1; //счетчик вкладок
$suplier1=1; //счетчик поставщиков
 
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
        if ($suplier1==1){
        	if(isset($phone) && $phone!=""){$phone=$phone.", ";}
        	$i=1; // счетчик количества товаров в каждой вкладке            
            $print_html .= '<div id="tabs-'.$w.'">'. PHP_EOL.'<div class="list_print">'. PHP_EOL;
            $print_html .= '<div style="text-align:center; margin-bottom:20px; font-weight:bold"><span>НАКЛАДНАЯ /ОБРАЗЦЫ/</span></div>';
            $table1 .= '<table class="list_samples">
            	  	<tr>
                        <td width="4%" style="border:none"></td>
                        <td width="22%">Дата составления заявки </td>
                    	<td width="58%">'.date("d-m-Y",time()).'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>Название фирмы</td>
                    	<td align="center" ><img src="logo.jpg" title="ApelBurg"  /></td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>Контактное лицо</td>
                    	<td>'.$user_name.' '.$user_last_name.'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>Телефон</td>
                    	<td>'.$phone.'(812) 438-00-55</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                  </table><br/>';
            $table2 .= '<table class="list_samples">';
            $table2 .= '<thead><tr><td width="4%">№</td><td width="22%">АРТИКУЛ</td><td  width="58%">НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td><td>КОЛ-ВО</td><td>ЦЕНА</td></tr></thead>';
            $table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
            $table2 .='<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            $table2 .='<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            $table2 .='<td align="center">1</td>'. PHP_EOL;
            $table2 .='<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            $table2 .= '</tr>'.PHP_EOL;
            $suplier1=$arr;
            $w++;
            $i++;
        }else if($suplier1==$arr){
        	$table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
            $table2 .='<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            $table2 .='<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            $table2 .='<td align="center">1</td>'. PHP_EOL;
            $table2 .='<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            $table2 .= '</tr>'.PHP_EOL;
        	$suplier1=$arr;
            $i++;
        }else if($suplier1!=$arr && $suplier1!=1){    
            $table2 .= '</table>'.PHP_EOL.'</div>'.PHP_EOL.'</div>'.PHP_EOL;
            $i=1; // счетчик количества товаров в каждой вкладке
            $table2 .= '<div id="tabs-'.$w.'">'. PHP_EOL.'<div class="list_print"><table class="list_samples">';
            $table2 .= '<thead><tr><td width="4%">№</td><td>АРТИКУЛ</td><td>НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td><td>КОЛ-ВО</td><td>ЦЕНА</td></tr></thead>';
            $table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
            $table2 .='<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            $table2 .='<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            $table2 .='<td align="center">1</td>'. PHP_EOL;
            $table2 .='<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            $table2 .= '</tr>'.PHP_EOL;
        	$suplier1=$arr;
            $w++;
            $i++;
        }
           
               
    }
     
}
 $table2 .= '</table>'.PHP_EOL;
 $print_html .= '<div style="padding:10px 0 10px 0">-------------------------------------------------------------------------------------------------</div>';
 $print_html .= '<div style="text-align:center"><h4>РАСПИСКА</h4></div>';
 $print_html .= '<div> Я,__________________________________получил от представителя фирмы</div>';
 $print_html .= '<div style="margin-top:10px;">_____________________________________________________________________</div>';
 $print_html .= '<div style="text-align:center"><span style="font-size:10px;">(название фирмы, Ф.И.О.)</span></div>';
 $print_html .= '<div> сумму в размере _______________________________________________________</div>';
 $print_html .= '<div style="margin-top:10px; margin-bottom:100px">Срок возврата образцов ______________________</div>';
 
 $print_html .= '</div></div>';

echo $table1.'<br>';
echo $table2;
?>