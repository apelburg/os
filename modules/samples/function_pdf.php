<?php
 //echo strlen('��������� ������� ����������, ������, ��������');//46
 
	include('../../libs/lock.php');
	$subject = '������ �������';
	//$db = mysql_connect('localhost','php','1234');
	$db= mysql_connect ("localhost","php_3477686","3477686");
	if(!$db) exit(mysql_error());
	//mysql_select_db('apelburg',$db);
	mysql_select_db ("apelburg_base",$db);
	//echo $db;
	mysql_query('SET NAMES cp1251');          
    mysql_query('SET CHARACTER SET cp1251');  
    mysql_query('SET COLLATION_CONNECTION="cp1251_general_ci"');
	include('../../libs/config.php');
	include('../../libs/php/common.php');
    include('../../libs/autorization.php');
	include('../../libs/variables.php');
//	echo '<pre>';
//print_r ($_SESSION);
//echo '</pre>';	
echo "��������! �� ������ ������ ��� ���������� ��������� �� ����� kapitonoval2012@gmail.com? ����� �������� ������ ����� ���������� ��������������� ������ 45,71,104 � ����� modules/samples/send_mail.php";
$email = $_SESSION['access']['email'];
//����� ������� �������
include_once('pdf/fpdf.php');
$query="
SELECT `s`.`id` AS `id_n` , `s`. *, `m`.`phone` AS `phoneMy`,  `m`.`email` AS `emailMy`, `m`.`name` AS `name2` , `m`.`nickname` AS `nickname` , `b`. * , `cl`.`company` , `supp`.`nickName` , `supp`.`fullName`, `supp`.`id` AS `nickName_id`
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
$result = mysql_query($query,$db);   //������ �� ��������� ������ ������ ��� ������������ ����������;
if(!$result)exit(mysql_error());
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Refresh" content="600"> <!-- 10*60 -->
<meta http-equiv="Content-Type" content="text/html; charset=windows-1251">
<link href="../../skins/css/styles.css" rel="stylesheet" type="text/css">
<link href="../../skins/css/styles_sample.css" rel="stylesheet" type="text/css">
<link href="../../skins/css/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="../../favicon.ico" type="image/x-icon">
<!--<script type="text/javascript" src="../../libs/js/common.js"></script>-->
<script type="text/javascript" src="../../libs/js/jquery-1.10.2.js"></script>
<script type="text/javascript" src="../../libs/js/jquery.form.js"></script>
<script type="text/javascript" src="../../libs/js/jquery-ui-1.10.4.custom.js"></script>
<script type="text/javascript" src="../../libs/js/upWindowMenu.js"></script>
<script type="text/javascript" src="../../libs/js/tableDataManager.js"></script>
<title>������ ������</title>

</head>

<body>

<?php
#############################################
##########     ����� �������       ##########
#############################################

$supplier=1; // ������� ����������� ��� ������ �������
$w=1; //������� ��� ������ �������
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
	$nickName[0]='';
	$nickName_id[0]='';
if(mysql_num_rows($result) > 0){
	while($item = mysql_fetch_assoc($result)){
    		//print_r($item);
			
            $mass[]= array( $item['nickName'] => array( $item['name'],  $item['art'], $item['price'], $item['id_n']));
    		if($supplier!=$item['nickName']){
				$nickName[] = $item['nickName'];
				$nickName_id[]=$item[nickName_id];
            	echo '<li id="li_'.$w.'"><a href="#tabs-'.$w.'">'.$item['nickName'].'</a></li>'. PHP_EOL;;	
                $w++;	
			}
            $supplier=$item['nickName'];
			$phone = $item['phoneMy'];
			//$email = $item['emailMy'];
	}
} 
echo '</ul>'. PHP_EOL;
echo '<br/>'. PHP_EOL;

#############################################
##########  /  ����� �������       ##########
#############################################



#############################################
######     �������� PDF ������       ########
#############################################


$w=1; //������� �������
$supplier1=1; //������� �����������
 
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
         if ($supplier1==1){//����������� ��� ������ �������(������� ����� ���������)
        	//if(isset($phone) && $phone!=""){$phone=$phone.", ";}
        	$i=1; // ������� ���������� ������� � ������ �������			
            /*******************************/
			
			/*������ �����*/
			$pdf=new FPDF();
			$pdf->AddFont('ArialMT','','arial.php');
			$pdf->AddFont('Arial-BoldMT','','arialbd.php');
			$pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
			$pdf->AddPage();
			$pdf->SetFont('Arial-BoldMT','',14); // ������ ����� � ��� ������
			$reportName="��������� /������� /";
			$pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			
			$pdf->Cell(10,20,'',0,1,'l');
			$pdf->ln(0);
			$pdf->Cell(10,5,'',0,0,'l');
			
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,5,'���� ����������� ������',1,0,'l');
			$pdf->SetTextColor(22,50,255); // �����
			$pdf->Cell(90,5,date("d-m-Y",time()),1,0,'l');
			$pdf->ln(5);
			$pdf->Cell(10,30,'',0,0,'l');
			
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,25,'�������� �����',1,0,'l');
			$pdf->Image('logo.jpg',80,37,'70','');
			$pdf->Cell(90,25,'',1,0,'l');
			
			$pdf->ln(20);
			$pdf->Cell(10,5,'',0,0,'l');
			$pdf->Cell(50,5,'���������� ����',1,0,'l');
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			$pdf->SetTextColor(0,0,0); // ������
			$pdf->Cell(90,5,$user_name.' '.$user_last_name,1,0,'l');
			$pdf->SetFont('Arial-BoldMT','',8);  // ������ ����� � ��� ������
			$pdf->ln(5);
			$pdf->Cell(10,5,'',0,0,'l');
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,5,'�������',1,0,'l');
			$pdf->SetTextColor(22,50,255); // �����
			$pdf->Cell(90,5,''.$phone.', (812) 438-00-55',1,0,'l');
			/* ����� �����*/
			/* ������ ����� �������� �������*/
			$pdf->ln(10);
			$pdf->SetTextColor(0,0,0); // ������
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			$pdf->SetFillColor(209,204,244); 
			$pdf->Cell(10,5,'�',1,0,'C',1);
			$pdf->Cell(50,5,'�������',1,0,'C',1);
			$pdf->Cell(90,5,'������������, ����, ������',1,0,'C',1);
			$pdf->Cell(20,5,'���-��',1,0,'C',1);
			$pdf->Cell(20,5,'����',1,0,'C',1);
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			/* ����� ����� �������� �������*/
			
			/* ���� �������� ������� */
			$n = 46;
			$text = $mass[$key][$arr][0];
			
			$replace = array("&laquo;", "&raquo;");			
			$text = str_replace($replace, "", $text);
			$text = preg_replace('/([,.;]+)\s*/', '$1 ', $text);
			$text = preg_replace('/([[:punct:]]+)\s*/', '$1 ', $text);
			$text = str_replace('  ',' ', $text);
			
			$newtext = wordwrap($text, $n, "/link/", true);
			$str_arr = explode("/link/", $newtext);
			$num_str = sizeof($str_arr);//���������� �����
			$height_cell_str = $num_str*3+6;//������ ����� � ������
			
			
			$pdf->ln(5);
			$pdf->Cell(10,$height_cell_str,$i,1,0,'C');
			$pdf->Cell(50,$height_cell_str,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,3,'','T','1','L');
			foreach($str_arr as $item => $cost) {
				$pdf->SetX(70);
				$pdf->Cell(90,3,$cost,0,'1','L');	
			}
			$pdf->SetX(70);
			$pdf->Cell(90,3,'','B','1','L');
			$pdf->SetXY(160,75);
			$pdf->Cell(20,$height_cell_str,'1',1,0,'C');
			$pdf->Cell(20,$height_cell_str,$mass[$key][$arr][2],1,0,'C');
			$pdf->ln($height_cell_str);
			$margin_top=$height_cell_str;
			/* /���� �������� ������� */
			
			/*******************************/
            $supplier1=$arr;
            $w++;
            $i++;
        }else if($supplier1!=$arr && $supplier1!=1){//���������� ��� ����� ����������.... �� ���� ����� ����� ������� ������ ���� � ������� �����(�������� ����� � ������ �����)
            $i=1; // ������� ���������� ������� � ������ �������
            /*******************************/
			$q=$w-1;
			/* ������ ������ �����*/
			$pdf->ln(10);
			$pdf->SetFont('Arial-BoldMT','',12); // ������ ����� � ��� ������
			$pdf->Cell(0,5,'-------------------------------------------------------------------------------------------------------------------------------------',0,0,'C');
			$pdf->ln(10);
			$pdf->Cell(0, 15, '��������', 0,1, 'C' );
			$pdf->Cell(0,5,'�,__________________________________������� �� ������������� �����',0,0,'L');
			$pdf->ln(10);
			$pdf->Cell(0,5,'_______________________________________________________________________________',0,0,'C');
			$pdf->ln(10);
			$pdf->Cell(0,5,'����� � �������_____________________________________________________________',0,0,'L');
			$pdf->ln(10);
			$pdf->Cell(0,5,'���� �������� ��������_______________________________________',0,0,'L');
			$pdf->Close;
			$pdf_name[1]='file_pdf/'.$q.'_'.date('d-m-Y_G.i.s',time()).'.pdf';
			$pdf->Output('file_pdf/'.$q.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
			/* ����� ������ ����� */
			
			
			/*������ �����*/
			$pdf=new FPDF();
			$pdf->AddFont('ArialMT','','arial.php');
			$pdf->AddFont('Arial-BoldMT','','arialbd.php');
			$pdf->AddFont('Arial-BoldItalicMT','','arialbi.php');
			$pdf->AddPage();
			$pdf->SetFont('Arial-BoldMT','',14); // ������ ����� � ��� ������
			$reportName="��������� /������� /";
			$pdf->Cell( 0, 15, $reportName, 0, 0, 'C' );
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			
			$pdf->Cell(10,20,'',0,1,'l');
			$pdf->ln(0);
			$pdf->Cell(10,5,'',0,0,'l');
			
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,5,'���� ����������� ������',1,0,'l');
			$pdf->SetTextColor(22,50,255); // �����
			$pdf->Cell(90,5,date("d-m-Y",time()),1,0,'l');
			$pdf->ln(5);
			$pdf->Cell(10,30,'',0,0,'l');
			
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,25,'�������� �����',1,0,'l');
			$pdf->Image('logo.jpg',80,37,'70','');
			$pdf->Cell(90,25,'',1,0,'l');
			
			$pdf->ln(20);
			$pdf->Cell(10,5,'',0,0,'l');
			$pdf->Cell(50,5,'���������� ����',1,0,'l');
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			$pdf->SetTextColor(0,0,0); // ������
			$pdf->Cell(90,5,$user_name.' '.$user_last_name,1,0,'l');
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			$pdf->ln(5);
			$pdf->Cell(10,5,'',0,0,'l');
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,5,'�������',1,0,'l');
			$pdf->SetTextColor(22,50,255); // �����
			$pdf->Cell(90,5,''.$phone.', (812) 438-00-55',1,0,'l');
			/* ����� �����*/
			/* ������ ����� �������� �������*/
			$pdf->ln(10);
			$pdf->SetTextColor(0,0,0); // ������
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			$pdf->SetFillColor(209,204,244); 
			$pdf->Cell(10,5,'�',1,0,'C',1);
			$pdf->Cell(50,5,'�������',1,0,'C',1);
			$pdf->Cell(90,5,'������������, ����, ������',1,0,'C',1);
			$pdf->Cell(20,5,'���-��',1,0,'C',1);
			$pdf->Cell(20,5,'����',1,0,'C',1);
			$pdf->SetFont('ArialMT','',8); // ������ ����� � ��� ������
			/* ����� ����� �������� �������*/
			
			/* ���� �������� ������� */
			$n = 46;
			$text = $mass[$key][$arr][0];
			
			$replace = array("&laquo;", "&raquo;");			
			$text = str_replace($replace, "", $text);
			$text = preg_replace('/([,.;]+)\s*/', '$1 ', $text);
			$text = preg_replace('/([[:punct:]]+)\s*/', '$1 ', $text);
			$text = str_replace('  ',' ', $text);
			
			$newtext = wordwrap($text, $n, "/link/", true);
			$str_arr = explode("/link/", $newtext);
			$num_str = sizeof($str_arr);//���������� �����
			$height_cell_str = $num_str*3+6;//������ ����� � ������
			
			
			$pdf->ln(5);
			$pdf->Cell(10,$height_cell_str,$i,1,0,'C');
			$pdf->Cell(50,$height_cell_str,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,3,'','T','1','L');
			foreach($str_arr as $item => $cost) {
				$pdf->SetX(70);
				$pdf->Cell(90,3,$cost,0,'1','L');	
			}
			$pdf->SetX(70);
			$pdf->Cell(90,3,'','B','1','L');
			$pdf->SetXY(160,75);
			$pdf->Cell(20,$height_cell_str,'1',1,0,'C');
			$pdf->Cell(20,$height_cell_str,$mass[$key][$arr][2],1,0,'C');
			$pdf->ln($height_cell_str);
			$margin_top=$height_cell_str;
			/* /���� �������� ������� */
			
			/*******************************/
        	$supplier1=$arr;			
            $w++;
            $i++;
        }else if($supplier1==$arr){// ����������� ��� ������������ ������ ������ � ���� �� ���������� (���� ���������)
            /*******************************/
							
			/* ���� �������� ������� */
			$n = 46;
			$text = $mass[$key][$arr][0];
			
			$replace = array("&laquo;", "&raquo;");			
			$text = str_replace($replace, "", $text);
			$text = preg_replace('/([,.;]+)\s*/', '$1 ', $text);
			$text = preg_replace('/([[:punct:]]+)\s*/', '$1 ', $text);
			$text = str_replace('  ',' ', $text);
			
			$newtext = wordwrap($text, $n, "/link/", true);
			
			$str_arr = explode("/link/", $newtext);
			$num_str = sizeof($str_arr);//���������� �����
			$height_cell_str = $num_str*3+6;//������ ����� � ������
			$pdf->Cell(10,$height_cell_str,$i,1,0,'C');
			$pdf->Cell(50,$height_cell_str,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,3,'','T','1','L');
			foreach($str_arr as $item => $cost) {
				$pdf->SetX(70);
				$pdf->Cell(90,3,$cost,0,'1','L');	
			}			
			$pdf->SetX(70);
			$pdf->Cell(90,3,'','B','1','L');
			$pdf->SetXY(160,75+$margin_top);
			$pdf->Cell(20,$height_cell_str,'1',1,0,'C');
			$pdf->Cell(20,$height_cell_str,$mass[$key][$arr][2],1,0,'C');
			$pdf->ln($height_cell_str);			
			$margin_top+=$height_cell_str;
			/* /���� �������� ������� */
			
			
			
			/*******************************/  
        	$supplier1=$arr;
            $i++;
        }
           
               
    }
     
}
$q=$w-1;
/* ������ ������ �����*/
	$pdf->ln(10);
	$pdf->SetFont('Arial-BoldMT','',12); // ������ ����� � ��� ������
	$pdf->Cell(0,5,'-------------------------------------------------------------------------------------------------------------------------------------',0,0,'C');
	$pdf->ln(10);
	$pdf->Cell(0, 15, '��������', 0,1, 'C' );
	$pdf->Cell(0,5,'�,__________________________________������� �� ������������� �����',0,0,'L');
	$pdf->ln(10);
	$pdf->Cell(0,5,'_______________________________________________________________________________',0,0,'C');
	$pdf->ln(10);
	$pdf->Cell(0,5,'����� � �������_____________________________________________________________',0,0,'L');
	$pdf->ln(10);
	$pdf->Cell(0,5,'���� �������� ��������_______________________________________',0,0,'L');
	$pdf->Close;
	if($pdf_name[1]!=''){
	$pdf_name[]='file_pdf/'.$q.'_'.date('d-m-Y_G.i.s',time()).'.pdf';}
	else{$pdf_name[1]='file_pdf/'.$q.'_'.date('d-m-Y_G.i.s',time()).'.pdf';}
	$pdf->Output('file_pdf/'.$q.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
	/* ����� ������ ����� */

	echo PHP_EOL.'<!-- ';
	print_r($pdf_name); //�������� ��������� ������
	echo ' -->';
#############################################
######     /  �������� PDF ������     #######
#############################################	

/*************************** ������ ������ ������� ********************/
$query="
SELECT `relate_supp`. * 
FROM `order_manager__relate_supplier_cont_faces` AS `relate_supp`
WHERE relate_supp.supplier_id IN (
";
$number=1;
foreach($nickName_id as $key3 => $type){
	if($key3!=0){
		if($number>1){$query.= ', ';}
		$query.= $type;
		$number++;
	}
}		

$query.=") ORDER BY `relate_supp`.`supplier_id` ASC";
//echo $query;
$result = mysql_query($query,$db); 
if(!$result)exit(mysql_error());
if(mysql_num_rows($result) > 0){
	while($item = mysql_fetch_assoc($result)){
		if($item['email']!=''){
			$email_sup[]= array( $item['supplier_id'] =>  array($item['email'],$item['name'],  $item['position']));
		}
	}
} 			

/**********************     /    ������ ������ ������� ********************/

#############################################
######         ����� ������           #######
#############################################

echo '<!--    ����� ������ -->'. PHP_EOL;

$w=1; //������� �������
$supplier1=1; //������� �����������
//print_r($item);
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
    
        if ($supplier1==1){
        	$i=1; // ������� ���������� ������� � ������ �������  
			echo PHP_EOL.'<!-- ������ ����� '.$w.'-->'.PHP_EOL;
			echo "<script type=\"text/javascript\">".PHP_EOL."
					// ������� �������� ����� ���������".PHP_EOL."
					$(document).ready(function() {".PHP_EOL."
						$(\"#form_".$w."\").ajaxForm({".PHP_EOL."
						success: function(msg){
							alert('������ �".$w." ����������!');".PHP_EOL."
							//alert(msg);
						},
						error: function(msg) {
							//alert('error! '+msg);
							}
						});".PHP_EOL."
					});".PHP_EOL."
				</script>";
			echo PHP_EOL.' <form method="post" action="./send_mail.php" id="form_'.$w.'" name="form_'.$w.'">'.PHP_EOL;
			echo '<input type="hidden" name="number" value="'.$w.'">'.PHP_EOL;
			echo '<input type="hidden" name="subject" value="'.$subject.'">'.PHP_EOL;
			echo '<input type="hidden" name="file_pdf" value="'.$pdf_name[$w].'">'.PHP_EOL;
			echo '<input type="hidden" name="email_manager" value="'.$email.'">'.PHP_EOL;
			echo '<input type="hidden" name="name_manager" value="'.$user_name.' '.$user_last_name.'">'.PHP_EOL;
			echo '<input type="hidden" name="id_name" value="';
			$numer=1;
			foreach($mass as $key => $type){
				foreach($mass[$key] as $arr2 => $name){
						/* ���� �������� ������� */
						if ($arr2==$nickName[$w]){
						if($numer>1){echo ', ';}
						echo $mass[$key][$arr2][3];
						$numer++;
						}
						/* /���� �������� ������� */
				}
			}
			echo '">'.PHP_EOL;
            echo '<div id="tabs-'.$w.'">'.PHP_EOL;
			echo '<div style=" width:66%;margin:0 auto; font-size:12px; padding: 10px 0 40px 0;">'.PHP_EOL;
			/*************************** ����� ������ ������� ********************/
			
			echo PHP_EOL.'<span style="width:70px;float:left;"><strong>����</strong></span><SELECT name="supplier_email" style="width:50%">'.PHP_EOL;
			echo '<OPTION value="0">...</OPTION>'.PHP_EOL;
			foreach($email_sup as $key3 => $type){
				foreach($email_sup[$key3] as $arr4 => $name){
					if($arr4==$nickName_id[$w])
					echo '<OPTION value="'.$email_sup[$key3][$nickName_id[$w]][0].'">'.$email_sup[$key3][$nickName_id[$w]][0].' --- '.$email_sup[$key3][$nickName_id[$w]][1].'   '.$email_sup[$key3][$nickName_id[$w]][2].'</OPTION>'.PHP_EOL;
				}
			}	
			echo '</SELECT><br/><br/>'.PHP_EOL;
			echo '<span style="width:70px;float:left;"><strong>����:</strong></span>'.$subject.'<br/><br/>'.PHP_EOL;
			/**********************     /     ����� ������ ������� ********************/
			echo '<textarea style="width:99%;height:200px;" name="text_'.$w.'">������������,
����� ����������� ������� �������� �� ��������� ���������:';

echo PHP_EOL;
$num=1;
foreach($mass as $key2 => $type){
    foreach($mass[$key2] as $arr2 => $name){
		if ($arr2==$nickName[$w]){
		echo PHP_EOL.$num++.'. '.substr($mass[$key2][$arr2][1], 2).' - '.$mass[$key2][$arr2][0];
		}
    }
}

echo PHP_EOL.'
� ���������, '.$user_name.' '.$user_last_name.'
Tel.:      +7 '.$phone.'
E-mail:    '.$email.'
web :      www.apelburg.ru

���:      +7  (812)  438-00-55 (��������������)
������:  +7 (495)  781-57-09 (��������������)
			</textarea></div>';
			echo PHP_EOL.'<div class="list_print">'. PHP_EOL;
            echo '<div style="text-align:center; margin-bottom:20px; font-weight:bold"><span>��������� /�������/</span></div>';
            echo '<table class="list_samples">
            	  	<tr>
                        <td width="4%" style="border:none"></td>
                        <td width="22%">���� ����������� ������ </td>
                    	<td width="58%">'.date("d-m-Y",time()).'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>�������� �����</td>
                    	<td align="center" ><img src="../../skins/images/img_design/header_logo.jpg" title="ApelBurg"  /></td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>���������� ����</td>
                    	<td>'.$user_name.' '.$user_last_name.'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>�������</td>
                    	<td>'.$phone.', (812) 438-00-55</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                  </table><br/>';
            echo '<table class="list_samples">';
            echo '<thead><tr><td width="4%">�</td><td width="22%">�������</td><td  width="58%">�����������, ����, ������</td><td>���-��</td><td>����</td></tr></thead>';
            echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
            echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
            $supplier1=$arr;
            $w++;
            $i++;
        }else if($supplier1==$arr){
        	echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
            echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
        	$supplier1=$arr;
            $i++;
        }else if($supplier1!=$arr && $supplier1!=1){
			$s=$w-1;
        echo '</table>'.PHP_EOL;
         echo '<div style="padding:10px 0 10px 0">-------------------------------------------------------------------------------------------------</div>';
			echo '<div style="text-align:center"><h4>��������</h4></div>';
            echo '<div> �,__________________________________������� �� ������������� �����</div>';
            echo '<div style="margin-top:10px;">_____________________________________________________________________</div>';
            echo '<div style="text-align:center"><span style="font-size:10px;">(�������� �����, �.�.�.)</span></div>';
            echo '<div> ����� � ������� _______________________________________________________</div>';
            echo '<div style="margin-top:10px; margin-bottom:100px">���� �������� �������� ______________________</div>';
            echo '</table>'.PHP_EOL.'</div>';
            echo '<div style="background:#CCCCCC; margin-top:20px; border-radius:5px;">
	<div style="width:60%; padding-top:30px; margin:0 0 0 20%; height:60px;">
    <div style=" width:50%; float:left; margin: 0 0 0 20%">
    	<input type="checkbox" name="save_pdf" id="save_pdf_'.$s.'"/><label for="save_pdf_'.$s.'">���������� PDF ���������</label>  
    </div>
    <div style="float:left; text-align:center"><input type="submit" onclick="document.getElementById(\'li_'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$w.'\').style.display=\'block\';document.getElementById(\'li_'.$w.'\').className += \' ui-tabs-active ui-state-active\';" style="background-image: url(\'../../skins/images/img_design/bg_button_menu.gif\'); border:none; width:130px; height:30px; font-size:16px; color:#fff;" value="���������" name="yes_'.$s.'"  class="send" /></div>
</div></div>';
            echo PHP_EOL.'</div>'.PHP_EOL;
			echo PHP_EOL.'</form>'.PHP_EOL;
			echo PHP_EOL.'<!-- ����� ����� '.$s.' -->'.PHP_EOL;
			echo PHP_EOL.'<!-- ������ ����� '.$w.' -->'.PHP_EOL;
			echo "<script type=\"text/javascript\">".PHP_EOL."
					// ������� �������� ����� ���������".PHP_EOL."
					$(document).ready(function() {".PHP_EOL."
						// ��������� 'myForm' �������������� ������ � ������ �� ���������� �������".PHP_EOL."
						$(\"#form_".$w."\").ajaxForm(function() {".PHP_EOL."
							alert('������ �".$w." ����������!');".PHP_EOL."
						});".PHP_EOL."
					});".PHP_EOL."
				</script>";
			echo PHP_EOL.' <form  method="post" action="./send_mail.php" id="form_'.$w.'" name="form_'.$w.'">'.PHP_EOL;
			echo '<input type="hidden" name="number" value="'.$w.'">'.PHP_EOL;
			echo '<input type="hidden" name="subject" value="������ ������� ��������">'.PHP_EOL;
			echo '<input type="hidden" name="file_pdf" value="'.$pdf_name[$w].'">'.PHP_EOL;
			echo '<input type="hidden" name="email_manager" value="'.$email.'">'.PHP_EOL;
			echo '<input type="hidden" name="name_manager" value="'.$user_name.' '.$user_last_name.'">'.PHP_EOL;
			echo '<input type="hidden" name="id_name" value="';
			$numer=1;
			foreach($mass as $key => $type){
				foreach($mass[$key] as $arr2 => $name){
						/* ���� �������� ������� */
						if ($arr2==$nickName[$w]){
						if($numer>1){echo ', ';}
						echo $mass[$key][$arr2][3];
						$numer++;
						}
						/* /���� �������� ������� */
				}
			}
			echo '">'.PHP_EOL;
			echo PHP_EOL.'<!-- ';
			print_r($pdf_name[$w]); //�������� ��������� ������
			echo ' -->'.PHP_EOL;
            $i=1; // ������� ���������� ������� � ������ �������
            echo '<div id="tabs-'.$w.'">'. PHP_EOL;
			echo '<div style=" width:66%;margin:0 auto; font-size:12px; padding: 10px 0 40px 0;">';
			/*************************** ����� ������ ������� ********************/
			
			echo PHP_EOL.'<span style="width:70px;float:left;"><strong>����</strong></span><SELECT name="supplier_email" style="width:50%">'.PHP_EOL;
			echo '<OPTION value="0">...</OPTION>'.PHP_EOL;
			foreach($email_sup as $key3 => $type){
				foreach($email_sup[$key3] as $arr4 => $name){
					if($arr4==$nickName_id[$w])
					echo '<OPTION value="'.$email_sup[$key3][$nickName_id[$w]][0].'">'.$email_sup[$key3][$nickName_id[$w]][0].' --- '.$email_sup[$key3][$nickName_id[$w]][1].'   '.$email_sup[$key3][$nickName_id[$w]][2].'</OPTION>'.PHP_EOL;
				}
			}	
			echo '</SELECT><br/><br/>'.PHP_EOL;
			echo '<span style="width:70px;float:left;"><strong>����:</strong></span>'.$subject.'<br/><br/>'.PHP_EOL;
			/**********************     /     ����� ������ ������� ********************/
			echo '<textarea style="width:99%;height:200px;" name="text_'.$w.'">������������,
����� ����������� ������� �������� �� ��������� ���������:';

echo PHP_EOL;
$num=1;
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr2 => $name){
      		/* ���� �������� ������� */
			if ($arr2==$nickName[$w]){
			echo PHP_EOL.$num++.'. '.substr($mass[$key][$arr2][1], 2).' - '.$mass[$key][$arr2][0];
			
			}
			/* /���� �������� ������� */
    }
}
echo PHP_EOL.'
� ���������, '.$user_name.' '.$user_last_name.'
Tel.:      +7 '.$phone.'
E-mail:    '.$email.'
web :      www.apelburg.ru

���:      +7  (812)  438-00-55 (��������������)
������:  +7 (495)  781-57-09 (��������������)
			</textarea></div>';
			echo PHP_EOL.'<div class="list_print">'. PHP_EOL;
            echo '<div style="text-align:center; margin-bottom:20px; font-weight:bold"><span>��������� /�������/</span></div>';
            echo '<table class="list_samples">
            	  	<tr>
                        <td width="4%" style="border:none"></td>
                        <td width="22%">���� ����������� ������ </td>
                    	<td width="58%">'.date("d-m-Y",time()).'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>�������� �����</td>
                    	<td align="center" ><img src="../../skins/images/img_design/header_logo.jpg" title="ApelBurg"  /></td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>���������� ����</td>
                    	<td>'.$user_name.' '.$user_last_name.'</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                    <tr>
                        <td width="4%" style="border:none"></td>
                        <td>�������</td>
                    	<td>'.$phone.', (812) 438-00-55</td>
                        <td style="border:none"></td>
                    	<td style="border:none"></td>
                  	</tr>
                  </table><br/>';
            echo PHP_EOL.'<table class="list_samples">';
            echo '<thead><tr><td width="4%">�</td><td>�������</td><td>�����������, ����, ������</td><td>���-��</td><td>����</td></tr></thead>';
            echo '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
             echo'<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            echo'<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            echo'<td align="center">1</td>'. PHP_EOL;
            echo'<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            echo '</tr>'.PHP_EOL;
        	$supplier1=$arr; 
            
            
            $w++;
            $i++;
        }
           
               
    }
     
}
 echo '</table>'.PHP_EOL;
 echo '<div style="padding:10px 0 10px 0">-------------------------------------------------------------------------------------------------</div>';
 echo '<div style="text-align:center"><h4>��������</h4></div>';
 echo '<div> �,__________________________________������� �� ������������� �����</div>';
 echo '<div style="margin-top:10px;">_____________________________________________________________________</div>';
 echo '<div style="text-align:center"><span style="font-size:10px;">(�������� �����, �.�.�.)</span></div>';
 echo '<div> ����� � ������� _______________________________________________________</div>';
 echo '<div style="margin-top:10px; margin-bottom:100px">���� �������� �������� ______________________</div>';
 
 echo '</div>';
 $s=$w-1;
 $c=$s-1;
 echo '<div style="background:#CCCCCC; margin-top:20px; border-radius:5px;">
	<div style="width:60%; padding-top:30px; margin:0 0 0 20%; height:60px;">
    <div style=" width:50%; float:left; margin: 0 0 0 20%">
    	<input type="checkbox" name="save_pdf" id="save_pdf_'.$s.'"/><label for="save_pdf_'.$s.'">���������� PDF ���������</label> 
    </div>
    <div style="float:left; text-align:center"><input type="submit" onclick="document.getElementById(\'li_'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$s.'\').style.display=\'none\';document.getElementById(\'tabs-'.$c.'\').style.display=\'block\';document.getElementById(\'li_'.$c.'\').className += \' ui-tabs-active ui-state-active\';" style="background-image: url(\'../../skins/images/img_design/bg_button_menu.gif\'); border:none; width:130px; height:30px; font-size:16px; color:#fff;" value="���������" name="yes_'.$s.'"  class="send" /></div>';
/*������ �� ������ �� � ��������� ������� ������� ������ ������������ �������� � ��������� ������*/
echo '</div>';
echo PHP_EOL.'</form>'.PHP_EOL;
echo PHP_EOL.'<!-- ����� ����� '.$s.' -->'.PHP_EOL;

#############################################
######      /  ����� ������           #######
#############################################


?>
<script type="text/javascript">
$('.send')
.data('counter', 0)                            // �������� �������
.click(function() {
    var counter = $('.send').data('counter');    // �������� ��������
   $('.send').data('counter', counter + 1);        // ����������� �������� �� 1
    if($('.send').data('counter')==<?php  echo $s; ?>){
		//alert("������");
							location.href="../../?page=samples&sample_page=request";
	}// ������� ���������� ������
});
</script>
</body>
</html>