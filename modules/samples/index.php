<?php
	$db= mysql_connect ("localhost","root","");
	if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	/*изменяем кодировку для корретного вывода PDF*/
	mysql_query('SET NAMES cp1251');          
    mysql_query('SET CHARACTER SET cp1251');  
    mysql_query('SET COLLATION_CONNECTION="cp1251_general_ci"');
require('pdf/fpdf.php');
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
/*$i=1;
foreach ($_POST as $key => $value){
    if(substr($key,0,2)=="c_"){
    	if($i==1){
	    $query.= substr($key,2)." ";
        }else{
        $query.= ",".substr($key,2)." ";
        }
        $i++;
    }
}*/

$query.="3,4,6,7) ORDER BY `supp`.`nickName` ASC";

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
            $print_html .= 'НАКЛАДНАЯ /ОБРАЗЦЫ/';
            $table1 .= '<table>'.PHP_EOL.'
            	  	<tr>
                        <td>Дата составления заявки </td>
                    	<td>'.date("d-m-Y",time()).'</td>
                  	</tr>
                    <tr>
                        <td>Название фирмы</td>
                    	<td><img src="logo.jpg" title="ApelBurg"  /></td>
                  	</tr>
                    <tr>
                        <td>Контактное лицо</td>
                    	<td>'.$user_name.' '.$user_last_name.'</td>
                  	</tr>
                    <tr>
                        <td>Телефон</td>
                    	<td>'.$phone.'(812) 438-00-55</td>
                  	</tr>
                  </table><br/>';
            $table2 .= '<table>'.PHP_EOL;
            $table2 .= '<tr>'.PHP_EOL.'<td width="4%">№</td>'.PHP_EOL.'<td width="22%">АРТИКУЛ</td>'.PHP_EOL.'<td  width="58%">НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td>'.PHP_EOL.'<td>КОЛ-ВО</td>'.PHP_EOL.'<td>ЦЕНА</td></tr>';
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
            $table2 .= '</table>'.PHP_EOL;
            $i=1; // счетчик количества товаров в каждой вкладке
            $table2 .= '<table >';
            $table2 .= '<tr><td width="4%">№</td><td>АРТИКУЛ</td><td>НАИМЕНОВНИЕ, ЦВЕТ, РАЗМЕР</td><td>КОЛ-ВО</td><td>ЦЕНА</td></tr>';
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
	



$pdf=new FPDF();
$pdf->AddFont('ArialMT','','arial.php');
$pdf->AddFont('Arial-BoldMT','','arialbd.php');
$pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
$pdf->AddPage();
$pdf->SetFont('Arial-BoldMT','',14); // задаем шрифт и его размер
$reportName="НАКЛАДНАЯ /ОБРАЗЦЫ /";
$pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
$pdf->SetFont('Arial-BoldMT','',8); // задаем шрифт и его размер

$pdf->Cell(10,20,'',0,1,'l');
$pdf->ln(0);
$pdf->Cell(10,5,'',0,0,'l');

$pdf->SetTextColor(214,3,0); // красный
$pdf->Cell(50,5,'Дата составления заявки',1,0,'l');
$pdf->SetTextColor(22,50,255); // синий
$pdf->Cell(90,5,date("d-m-Y",time()),1,0,'l');
$pdf->ln(5);
$pdf->Cell(10,30,'',0,0,'l');

$pdf->SetTextColor(214,3,0); // красный
$pdf->Cell(50,25,'Название фирмы',1,0,'l');
$pdf->Image('logo.jpg',80,37,'70','');
$pdf->Cell(90,25,'',1,0,'l');

$pdf->ln(20);
$pdf->Cell(10,5,'',0,0,'l');
$pdf->Cell(50,5,'Контактное лицо',1,0,'l');
$pdf->SetFont('ArialMT','',8); // задаем шрифт и его размер
$pdf->SetTextColor(0,0,0); // чёрный
$pdf->Cell(90,5,$user_name.' '.$user_last_name,1,0,'l');
$pdf->SetFont('Arial-BoldMT','',8); // задаем шрифт и его размер
$pdf->ln(5);
$pdf->Cell(10,5,'',0,0,'l');
$pdf->SetTextColor(214,3,0); // красный
$pdf->Cell(50,5,'Телефон',1,0,'l');
$pdf->SetTextColor(22,50,255); // синий
$pdf->Cell(90,5,''.$phone.'(812) 438-00-55',1,0,'l');
$pdf->ln(5);
$pdf->SetTextColor(0,0,0); // чёрный
$pdf->SetFont('ArialMT','',8); // задаем шрифт и его размер
//конец 1 таблицы
//начало 2 таблицы

$w=1; //счетчик вкладок
$suplier1=1; //счетчик поставщиков
 
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
        if ($suplier1==1){
        	if(isset($phone) && $phone!=""){$phone=$phone.", ";}
        	$i=1; // счетчик количества товаров в каждой вкладке
			$pdf->ln(5);
			$pdf->SetFont('Arial-BoldMT','',8); // задаем шрифт и его размер
			$pdf->SetFillColor(209,204,244); 
			$pdf->Cell(10,5,'№',1,0,'C',1);
			$pdf->Cell(50,5,'АРТИКУЛ',1,0,'C',1);
			$pdf->Cell(90,5,'НАИМЕНОВАНИЕ, ЦВЕТ, РАЗМЕР',1,0,'C',1);
			$pdf->Cell(20,5,'КОЛ-ВО',1,0,'C',1);
			$pdf->Cell(20,5,'ЦЕНА',1,0,'C',1);
			$pdf->SetFont('ArialMT','',8); // задаем шрифт и его размер
			
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'C');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
			
            $suplier1=$arr;
            $w++;
            $i++;
        }else if($suplier1==$arr){
        	
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'C');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
			
        	$suplier1=$arr;
            $i++;
        }else if($suplier1!=$arr && $suplier1!=1){    
            $table2 .= '</table>'.PHP_EOL;
            $i=1; // счетчик количества товаров в каждой вкладке
            $pdf->ln(20);
			$pdf->Cell(10,5,'№',1,0,'C');
			$pdf->Cell(50,5,'АРТИКУЛ',1,0,'C');
			$pdf->Cell(90,5,'НАИМЕНОВАНИЕ, ЦВЕТ, РАЗМЕР',1,0,'C');
			$pdf->Cell(20,5,'КОЛ-ВО',1,0,'C');
			$pdf->Cell(20,5,'ЦЕНА',1,0,'C');
			
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'C');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
        	$suplier1=$arr;
			
            $w++;
            $i++;
        }
           
               
    }
     
}
$pdf->ln(10);
$pdf->SetFont('Arial-BoldMT','',12); // задаем шрифт и его размер
$pdf->Cell(0,5,'-------------------------------------------------------------------------------------------------------------------------------------',0,0,'C');
$pdf->ln(10);
$pdf->Cell(0, 15, 'РАСПИСКА', 0,1, 'C' );
$pdf->Cell(0,5,'Я,__________________________________получил от представителя фирмы',0,0,'L');
$pdf->ln(10);
$pdf->Cell(0,5,'_______________________________________________________________________________',0,0,'C');
$pdf->ln(10);
$pdf->Cell(0,5,'сумму в размере_____________________________________________________________',0,0,'L');
$pdf->ln(10);
$pdf->Cell(0,5,'срок возврата образцов_______________________________________',0,0,'L');
$pdf->Output(date('d-m-Y_G.i.s',time()).'.pdf');
$pdf->Output();
//echo $table2;

?>
