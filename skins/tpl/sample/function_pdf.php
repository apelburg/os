<?php
	$db= mysql_connect ("localhost","root","");
	if(!$db) exit(mysql_error());
	mysql_select_db ("apelburg_base",$db);
	/*�������� ��������� ��� ���������� ������ PDF*/
	mysql_query('SET NAMES cp1251');          
    mysql_query('SET CHARACTER SET cp1251');  
    mysql_query('SET COLLATION_CONNECTION="cp1251_general_ci"');

//����� ������� �������
include_once('../../../libs/php/pdf/fpdf.php');
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
$result = mysql_query($query,$db);   //������ �� ��������� ������ ������ ��� ������������ ����������;
if(!$result)exit(mysql_error());
$suplier=1; // ������� ����������� ��� ������ �������
$w=1; //������� ��� ������ �������
$print_html='';
$table1='';
$table2='';
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



$w=1; //������� �������
$suplier1=1; //������� �����������
 
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
        if ($suplier1==1){
        	if(isset($phone) && $phone!=""){$phone=$phone.", ";}
        	$i=1; // ������� ���������� ������� � ������ ������� 
            $print_html .= '��������� /�������/';
            $table1 .= '<table>'.PHP_EOL.'
            	  	<tr>
                        <td>���� ����������� ������ </td>
                    	<td>'.date("d-m-Y",time()).'</td>
                  	</tr>
                    <tr>
                        <td>�������� �����</td>
                    	<td><img src="libs/php/logo.jpg" title="ApelBurg"  /></td>
                  	</tr>
                    <tr>
                        <td>���������� ����</td>
                    	<td>'.$user_name.' '.$user_last_name.'</td>
                  	</tr>
                    <tr>
                        <td>�������</td>
                    	<td>'.$phone.'(812) 438-00-55</td>
                  	</tr>
                  </table><br/>';
            $table2 .= '<table>'.PHP_EOL;
            $table2 .= '<tr>'.PHP_EOL.'<td width="4%">�</td>'.PHP_EOL.'<td width="22%">�������</td>'.PHP_EOL.'<td  width="58%">�����������, ����, ������</td>'.PHP_EOL.'<td>���-��</td>'.PHP_EOL.'<td>����</td></tr>';
            $table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
            $table2 .='<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            $table2 .='<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            $table2 .='<td align="center">1</td>'. PHP_EOL;
            $table2 .='<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            $table2 .= '</tr>'.PHP_EOL;
            $suplier1=$arr;
            $w++;
            $i++;
        }else if($suplier1==$arr){
        	$table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
            $table2 .='<td>'.substr($mass[$key][$arr][1], 2).'</td>'. PHP_EOL;
            $table2 .='<td>'.$mass[$key][$arr][0].'</td>'. PHP_EOL;
            $table2 .='<td align="center">1</td>'. PHP_EOL;
            $table2 .='<td align="center">'.$mass[$key][$arr][2].'</td>'. PHP_EOL;
            $table2 .= '</tr>'.PHP_EOL;
        	$suplier1=$arr;
            $i++;
        }else if($suplier1!=$arr && $suplier1!=1){    
            $table2 .= '</table>'.PHP_EOL;
            $i=1; // ������� ���������� ������� � ������ �������
            $table2 .= '<table >';
            $table2 .= '<tr><td width="4%">�</td><td>�������</td><td>�����������, ����, ������</td><td>���-��</td><td>����</td></tr>';
            $table2 .= '<tr>'.PHP_EOL.'<td align="center">'.$i.'</td>'.PHP_EOL; //����� ������ ������ ������
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



//����� 1 �������
//������ 2 �������

$w=1; //������� �������
$suplier1=1; //������� �����������
 
foreach($mass as $key => $type){
    foreach($mass[$key] as $arr => $name){
        if ($suplier1==1){//����������� ��� ������ �������(������� ����� ���������)
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
			$pdf->SetFont('Arial-BoldMT','',8); // ������ ����� � ��� ������
			$pdf->ln(5);
			$pdf->Cell(10,5,'',0,0,'l');
			$pdf->SetTextColor(214,3,0); // �������
			$pdf->Cell(50,5,'�������',1,0,'l');
			$pdf->SetTextColor(22,50,255); // �����
			$pdf->Cell(90,5,''.$phone.'(812) 438-00-55',1,0,'l');
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
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'L');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
			/* /���� �������� ������� */
			
			/*******************************/
            $suplier1=$arr;
            $w++;
            $i++;
        }else if($suplier1!=$arr && $suplier1!=1){//���������� ��� ����� ����������.... �� ���� ����� ����� ������� ������ ���� � ������� �����(�������� ����� � ������ �����)
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
			$pdf->Output($q.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
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
			$pdf->Cell(90,5,''.$phone.'(812) 438-00-55',1,0,'l');
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
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'L');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
			/* /���� �������� ������� */
			
			/*******************************/
        	$suplier1=$arr;			
            $w++;
            $i++;
        }else if($suplier1==$arr){// ����������� ��� ������������ ������ ������ � ���� �� ���������� (���� ���������)
            /*******************************/
			
			/* ���� �������� ������� */
			$pdf->ln(5);
			$pdf->Cell(10,5,$i,1,0,'C');
			$pdf->Cell(50,5,substr($mass[$key][$arr][1], 2),1,0,'C');
			$pdf->Cell(90,5,$mass[$key][$arr][0],1,0,'L');
			$pdf->Cell(20,5,'1',1,0,'C');
			$pdf->Cell(20,5,$mass[$key][$arr][2],1,0,'C');
			/* /���� �������� ������� */
			
			/*******************************/  
        	$suplier1=$arr;
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
	$pdf->Output($q.'_'.date('d-m-Y_G.i.s',time()).'.pdf');
	/* ����� ������ ����� */

?>




<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Refresh" content="600"> <!-- 10*60 -->
<link href="../../../libs/php/skins/css/styles.css" rel="stylesheet" type="text/css">
<link href="../../../libs/php/skins/css/styles_sample.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="../../../libs/php/favicon.ico" type="image/x-icon">
<script type="text/javascript" src="../../../libs/php/libs/js/common.js"></script><?php
if(isset($_GET['page']) && $_GET['page']=="samples"){
echo PHP_EOL; //PHP_EOL - ��������� �������� ������ ������������ ������ /r/n ��� ��������������������
echo '<script type="text/javascript" src="libs/js/jquery.1.10.2.min.js"></script>' . PHP_EOL;
echo '<script type="text/javascript" src="libs/js/jquery-ui.js"></script>' . PHP_EOL;
echo '<link href="./skins/css/jquery-ui.css" rel="stylesheet" type="text/css">' . PHP_EOL;
}
 ?>
<script type="text/javascript" src="../../../libs/php/libs/js/upWindowMenu.js"></script>
<script type="text/javascript" src="../../../libs/php/libs/js/tableDataManager.js"></script>
<title>������ ������</title>
</head>

<body>
<div class="main_container">
    <table class="main_menu_tbl">
        <tr>
            <td>
                <a href="../../../libs/php/?page=clients&section=clients_list">�������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=suppliers">����������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=planner">�����</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=orders">������</a>
            </td>
             <td>
                <a href="../../../libs/php/?page=documents">���������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=samples&sample_page=start">�������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=delivery">��������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=design">������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=empty">������������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=empty">�����������</a>
            </td>
            <td>
                <a href="../../../libs/php/?page=empty">���</a>
            </td>
            <td style="width:auto;">&nbsp;
                
            </td>
            <td style="width:250px;padding:0px;">
                <table class="authentication_plank_tbl">
                    <tr>
                        <td style="width:auto;text-align:right;">
                            <div style="width:228px;overflow:hidden;">
                                <nobr><?php echo $position.': '.$user_name.' '.$user_last_name; ?></nobr>
                            </div>
                        </td>
                        <td style="padding:0px 2px;">
                            <div>
                                <a href="#" onclick="return show_hide_div('authentication_menu_div');"><img src="../../../libs/php/skins/images/img_design/flag.png"></a>
                            </div>
                            <div class="authentication_menu_container">
                               <div class="authentication_menu_div" id="authentication_menu_div">
                                    <?php echo $authentication_menu_dop_items; ?>
                                    <div class="cap2"><nobr><a href="../../../libs/php/?out">����� �� ���������� <!--<span class="cross">&#215</span>--></a></nobr></div>
                                  
                               </div>
                            </div>
                            
                            
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table class="quick_panel_tbl">
        <tr>
            <td class="quick_button">
            <?php
            if(isset($_GET['page']) && $_GET['page']=='samples'){
            echo '<input type="button" onclick="submitform(\'request_samples\')" name="request_samples" style="border: 1px solid #9f9e9d; background:white; color:grey; padding:2px; margin-left:8%;cursor:pointer;box-shadow: 0 2px 5px black;" value="�������� �������">';
            }else{
            echo '<div class="quick_button_div">
                    <a href="#" class="button" onclick="openCloseMenu(event,\'quickMenu\'); return false;">&nbsp;</a>
                </div>';
            } 
            ?>
                <!---->
            </td>
            <td class="quick_search">
                <div class="search_div">
                    <div class="search_cap">����� ��:</div>
                    <div class="search_field"><input type="text"></div>
                    <div class="search_button"><img src="../../../libs/php/skins/images/img_design/quick_search_button.png"></div>
                    <div class="clear_div"></div>
                </div>
            </td>
            <td class="quick_view_button">
                <div class="quick_view_button_div">
                    <a href="#" class="button" onclick="openCloseMenu(event,'rtTypeViewMenu'); return false;">&nbsp;</a>
                </div>
            </td>
        </tr>
    </table>

<?php echo $content; ?>
</div>
<div style="position:absolute;right:0px;bottom:0px;"><a href="#" onclick="alert(error_report);return false;">������</a></div>
</body>
</html>