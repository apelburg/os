<!-- ************** request   **************** -->
<?php

include_once('libs/php/pdf/fpdf.php');
foreach ($_POST as $key => $value){
if(substr($key,0,2)=="c_"){
$yes=1;
}}

$query = "UPDATE `samples` SET `stage` = '1' WHERE `samples`.`id` IN (
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
$query.=")";
if(!isset($yes)){echo 'Запрос пуст'; exit;}

$result = mysql_query($query,$db);
if(!$result)exit(mysql_error());

echo "<br/>";
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
//echo $query;
$result = mysql_query($query,$db);   //запрос по выбранным фирмам фирмам для формирования заголовков;
if(!$result)exit(mysql_error());
$suplier=1; // счетчик поставщиков для вывода вкладок
$w=1; //счетчик для вывода вкладок
echo PHP_EOL.'<div id="tabs">
	<ul>
    <script>
	$(function() {
		var tabs = $( "#tabs" ).tabs();
		tabs.find( ".ui-tabs-nav" ).sortable({
			axis: "x",
			stop: function() {
				tabs.tabs( "refresh" );
			}
		});
	});
	</script>
    
    ';
if(mysql_num_rows($result) > 0){
	while($item = mysql_fetch_assoc($result)){
    		//print_r($item);
            $mass[]= array( $item['nickName'] => array( $item['name'],  $item['art'], $item['price']));
    		if($suplier!=$item['nickName']){
            	echo '<li id="li_'.$w.'"><a href="#tabs-'.$w.'">'.$item['nickName'].'</a></li>'. PHP_EOL;;	
                $w++;	
			}
            $suplier=$item['nickName'];
	}
} 
echo '</ul>'. PHP_EOL;;
echo '<br/>'. PHP_EOL;;
//print_r($mass);
echo '<!--    ВЫВОД ДАННЫХ -->'. PHP_EOL;;
$w=1; //счетчик вкладок
$suplier1=1; //счетчик поставщиков



 print_r($item);
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
    
        if ($suplier1==1){
        	if(isset($phone) && $phone!=""){$phone=$phone.", ";}
        	$i=1; // счетчик количества товаров в каждой вкладке  
            $s=$w-1; 
            
            /*********************************************************************************************
            ********************************************************************************************
            ******************************************************************************************/
            $pdf = new FPDF();
            $pdf->AddFont('ArialMT','','arial.php');
            $pdf->AddFont('Arial-BoldMT','','arialbd.php');
            $pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
            $pdf->AddPage();
            $pdf->SetFont('Arial-BoldMT','',14); // задаем шрифт и его размер
            $reportName="НАКЛАДНАЯ /ОБРАЗЦЫ /".$w;
            iconv ('utf-8', 'windows-1251', $reportName); 
            $pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
            $pdf->Close();
            $pdf->Output($w.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
            /*****************************************
            **************************************           
            ******************************************/         
            echo '<div id="tabs-'.$w.'">'. PHP_EOL.'<div class="list_print">'. PHP_EOL;
            echo '<div style="text-align:center; margin-bottom:20px; font-weight:bold"><span>НАКЛАДНАЯ /ОБРАЗЦЫ/</span></div>';
            echo '<table class="list_samples">
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
                    	<td align="center" ><img src="skins/images/img_design/header_logo.jpg" title="ApelBurg"  /></td>
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
            echo '<table class="list_samples">';
            echo '<thead><tr><td width="4%">№</td><td width="22%">АРТИКУЛ</td><td  width="58%">НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td><td>КОЛ-ВО</td><td>ЦЕНА</td></tr></thead>';
            echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
            echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
            $suplier1=$arr;
            $w++;
            $i++;
        }else if($suplier1==$arr){
        	echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
            echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
        	$suplier1=$arr;
            $i++;
        }else if($suplier1!=$arr && $suplier1!=1){   
        echo '</table>'.PHP_EOL;
         echo '<div style="padding:10px 0 10px 0">-------------------------------------------------------------------------------------------------</div>';
			echo '<div style="text-align:center"><h4>РАСПИСКА</h4></div>';
            echo '<div> Я,__________________________________получил от представителя фирмы</div>';
            echo '<div style="margin-top:10px;">_____________________________________________________________________</div>';
            echo '<div style="text-align:center"><span style="font-size:10px;">(название фирмы, Ф.И.О.)</span></div>';
            echo '<div> сумму в размере _______________________________________________________</div>';
            echo '<div style="margin-top:10px; margin-bottom:100px">Срок возврата образцов ______________________</div>';
            echo '</table>'.PHP_EOL.'</div>';
            echo '<div style="background:#CCCCCC; margin-top:20px; border-radius:5px;">
	<div style="width:60%; padding-top:30px; margin:0 0 0 20%; height:60px;">
    <div style=" width:50%; float:left; margin: 0 0 0 20%">
    	<input type="checkbox" name="save_pdf" /> отправить PDF накладную 
    </div>
    <div style="float:left; text-align:center"><input type="submit" onclick="document.getElementById(\'li_'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$w.'\').style.display=\'block\';document.getElementById(\'li_'.$w.'\').className += \' ui-tabs-active ui-state-active\';" style="background-image: url(\'skins/images/img_design/bg_button_menu.gif\'); border:none; width:100px; height:30px; font-size:16px; color:#fff;" value="ДА" name="yes_'.$s.'" /></div>
</div></div>';
            echo PHP_EOL.'</div>'.PHP_EOL;
            $i=1; // счетчик количества товаров в каждой вкладке
             /*********************************************************************************************
            ********************************************************************************************
            ******************************************************************************************/
            $pdf = new FPDF();
            $pdf->AddFont('ArialMT','','arial.php');
            $pdf->AddFont('Arial-BoldMT','','arialbd.php');
            $pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
            $pdf->AddPage();
            $pdf->SetFont('Arial-BoldMT','',14); // задаем шрифт и его размер
            $reportName="НАКЛАДНАЯ /ОБРАЗЦЫ /".$w;
            $pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
            $pdf->Close();
            $pdf->Output($w.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
            /*****************************************
            **************************************           
            ******************************************/        
            echo '<div id="tabs-'.$w.'">'. PHP_EOL.'<div class="list_print">'. PHP_EOL;
            echo '<div style="text-align:center; margin-bottom:20px; font-weight:bold"><span>НАКЛАДНАЯ /ОБРАЗЦЫ/</span></div>';
            echo '<table class="list_samples">
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
                    	<td align="center" ><img src="skins/images/img_design/header_logo.jpg" title="ApelBurg"  /></td>
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
            echo PHP_EOL.'<table class="list_samples">';
            echo '<thead><tr><td width="4%">№</td><td>АРТИКУЛ</td><td>НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td><td>КОЛ-ВО</td><td>ЦЕНА</td></tr></thead>';
            echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //вывод номера строки товара
             echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
        	$suplier1=$arr;
            
            
            $w++;
            $i++;
        }
           
               
    }
     
}
 echo '</table>'.PHP_EOL;
 echo '<div style="padding:10px 0 10px 0">-------------------------------------------------------------------------------------------------</div>';
 echo '<div style="text-align:center"><h4>РАСПИСКА</h4></div>';
 echo '<div> Я,__________________________________получил от представителя фирмы</div>';
 echo '<div style="margin-top:10px;">_____________________________________________________________________</div>';
 echo '<div style="text-align:center"><span style="font-size:10px;">(название фирмы, Ф.И.О.)</span></div>';
 echo '<div> сумму в размере _______________________________________________________</div>';
 echo '<div style="margin-top:10px; margin-bottom:100px">Срок возврата образцов ______________________</div>';
 
 echo '</div>';
 $s=$w-1;
 echo '<div style="background:#CCCCCC; margin-top:20px; border-radius:5px;">
	<div style="width:60%; padding-top:30px; margin:0 0 0 20%; height:60px;">
    <div style=" width:50%; float:left; margin: 0 0 0 20%">
    	<input type="checkbox" name="save_pdf" /> отправить PDF накладную 
    </div>
    <div style="float:left; text-align:center"><input type="submit" onclick="document.getElementById(\'li_1\').style.display=\'none\';document.getElementById(\'tabs-1\').style.display=\'none\';document.getElementById(\'tabs-2\').style.display=\'block\';document.getElementById(\'li_2\').className += \' ui-tabs-active ui-state-active\';" style="background-image: url(\'skins/images/img_design/bg_button_menu.gif\'); border:none; width:100px; height:30px; font-size:16px; color:#fff;" value="ДА" name="yes_'.$s.'" /></div>
</div></div>';
/*онклик на кнопке ДА в последней видимой вкладке должен отрабатывать редирект в следующий раздел*/
echo '</div>';

?>



  <!-- ***************/ request   ****************** -->