<?php

	include("../mysql.php");
	mysql_query("SET character_set_client='cp1251'");
	mysql_query("SET character_set_results='cp1251'");
	mysql_query("SET collation_connection='cp1251_general_ci'"); 

	$StrArr = explode("c_",$_GET['id_samples']);
	$i=0; 
	$id_str='';
	foreach ($StrArr as $key => $value){
		if($key>0){
		   if($i==0){
			   $id_str = $value;
		   }else if($i>0){
			   $id_str .= ', '.$value;
		   }
		$i++;	
		}
	}
	$query ="
	SELECT
	 `s`.*, 
	`m`.*, 
	`b`.*,
	`b`.`name` AS `article_name`, 
	`m`.`id` AS `manager_id`,
	`cl`.*
	FROM 
	`samples` AS `s` 
	INNER JOIN 
	`base` AS `b` ON `s`.`tovar_id` = `b`.`id` 
	INNER JOIN 
	`order_manager__manager_list` AS `m` ON `s`.`manager_id` = `m`.`id`
	INNER JOIN
              order_manager__client_list AS cl
                ON s.client_id = cl.id
	 WHERE 
	 `s`.`id` IN(".$id_str.")";
	//echo $query;
	$result = mysql_query($query,$db) or die(mysql_error());
	if(!$result)exit(mysql_error());
            //echo mysql_num_rows($result);
            if(mysql_num_rows($result) > 0){
                    while($item = mysql_fetch_assoc($result)){
					$new_arr[] = $item;
					}
			}
?>
<!doctype html>
<html>
<head>
<meta charset="windows-1251">
<title>�������� ��� ��������</title>
</head>
<style>
	html{
		font-family:Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif;
		
	}
	table{
		border:1px solid grey;
		border-collapse: collapse;
	}
	table tr td{
		border: 1px solid grey;
	}
</style>
<?php
/*echo '<pre>';
print_r($new_arr);
echo '<pre>';*/
?>
<body>
<div style="width:100%; margin:0 auto;">
<div>
  <div style="text-align:center">
    <p><img src="./skins/images/img_design/header_logo.jpg" title="ApelBurg"></p>
    <p>��������� ������ �������� ��� &laquo;��������� �����&raquo;</p>
  </div>
  <p style="float:right">���� ___ _____________ <?php echo date("Y"); ?> �.</p>
  <p>&nbsp;</p>
  
  <?php
  $i=0;
  $i2=0;
  foreach ($new_arr as $key => $value){
	  if($i==0){
		  if(empty($new_arr[$key]['comp_full_name']) && str_replace(' ','',$new_arr[$key]['comp_full_name'])==''){
		  	  $comp_name = $new_arr[$key]['company'];
		  }else{
			  $comp_name = $new_arr[$key]['comp_full_name'];
		  }
	  	echo'<p>������������ �������: '.$comp_name.'<br>
				���: '.$new_arr[$key]['phone'].'<br>
			  </p>
			   <table style="width:100%">
				<thead>
				  <tr>
					<td width="4%">�</td>
					<td width="22%">�������</td>
					<td width="58%">�����������, ����, ������</td>
					<td>���-��</td>
					<td>����</td>
				  </tr>
				</thead>
				<tbody>';
	  }
	  $i++;
	  echo '
		   <tr>
			  <td align="center">'.$i2++.'</td>
			  <td>'.$new_arr[$key]['art'].'</td>
			  <td>'.$new_arr[$key]['article_name'].'</td>
			  <td align="center">'.$new_arr[$key]['quantity_of_samples'].'</td>
			  <td align="center">'.$new_arr[$key]['price'].'</td>
			</tr>
		  ';
	}
  ?>

    
    </tbody>
  </table> 
  <div>
    <p><strong>���� �������� ��������: ___ _____________ 2014 �.</strong></p>
    <p style="font-size:20px; text-align:center"><strong>--------------------------------------------------------------------------------------------------------------------</strong></p>
  </div>
  <div>
   		�,_____________________________________________________, �������(�) �� ������������� <br>
        ��� &laquo;��������� �����&raquo; ��������� �������. �������� ������� ������� � ��������� ����. <br>
        � ������ �����/���������� ��������, �������� ���������� ��� &laquo;��������� �����&raquo; <br>
        �� ������ ���������.<br>
  </div>
  <div>
  
  </div>
  <br/><br/><br/><br/>
<div>________________ (______________________)<br>
  (�������)_____________________</div>
</div>
</div>
</body>
</html>
