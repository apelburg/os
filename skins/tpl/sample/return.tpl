<!-- begin skins/tpl/sample/request.tpl -->
    <div id="sample_content">
    	<div id="sample_content_head">
            <div>
                <table>
                    <thead>
                        <tr>
                            <td width="2%"></td>
                            <td class="sample_num_rows_check" width="2%"><input type="checkbox" id="checkbox_all" name="c_'.$item['id_n'].'" onChange="check_all_for_request(this,'<?php echo $_GET["sample_page"]."'"; if(isset($_GET['sort'])){echo ",'".$_GET['sort']."'";} ?>);" ><label for="checkbox_all"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                            <?php                    
                            if(isset($_GET['sort']) && $_GET['sort']=='client'){
                                echo "<td width=\"10%\" onclick=\"javascript:location='?page=";
                                echo $_GET['page']."&sample_page=".$_GET['sample_page'];
                                echo "'\" style=\"cursor:pointer;\">поставщик</td>";
                                }else{
                                echo "<td width=\"10%\" onclick=\"javascript:location='?page=";
                                echo $_GET['page']."&sample_page=".$_GET['sample_page'];
                                echo "&sort=client'\" style=\"cursor:pointer;\">заказчик</td>";
                                }
                            ?>
                           <td width="9%">артикул</td>
                            <td width="30%">описание</td>
                            <td width="6%">шт</td>
                            <td width="6%">залог-поставщик</td>
                            <td width="6%">залог-клиент</td>
                             <td width="6%">получили</td>
                            <td width="6%">сдавать</td>
                            <td width="6%">продлено</td>
                            <td style="background:#D6D6D6; border-right:#D6D6D6; width:12%"></td>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
        <div id="scroll_container">
        <?php
            if(isset($_GET['sort']) && $_GET['sort']=='client'){
            $sort = "client_id";
            }else{
            $sort = "supplier_id";
            }
            $query = "
             SELECT 
             `s`.`id` AS `id_n`,
             `s`.*,              
             `m`.`name` AS `name2`,
             `m`.`nickname` AS `nickname`, 
             `b`.*,
             `cl`.`company`,
             `cl`.`addres` AS `client_addres`,
             `supp`.`addres` AS `supplier_addres`,
             `supp`.`nickName`,
             `supp`.`fullName`,
             DATE_FORMAT(`date_of_receipt`,'%d.%m.%Y') AS `date_of_receipt`
            FROM
             `samples` AS `s`
              INNER JOIN
              order_manager__manager_list AS m
                ON s.manager_id = m.id
              INNER JOIN
              base AS b
                ON s.tovar_id = b.id
              INNER JOIN
              order_manager__client_list AS cl
                ON s.client_id = cl.id
              INNER JOIN
              order_manager__supplier_list AS supp
                ON s.supplier_id = supp.id
              WHERE s.manager_id = ".$_SESSION['access']['user_id']."
                ORDER BY `s`.`$sort` ASC
                ";
			//echo $query;
            $result = mysql_query($query,$db);
            $count=0;
            $table=0;
            if(!$result)exit(mysql_error());
            //echo mysql_num_rows($result);
            if(mysql_num_rows($result) > 0){
            
                    while($item = mysql_fetch_assoc($result)){
                    if($item['stage']==6 || ((strtotime($item['date_of_deposit']))-strtotime(date("Y-m-d"))-172800)<=0){
                    if($item['stage']==6 || ((strtotime($item['postponement_date']))-strtotime(date("Y-m-d"))-172800)<=0 && $item['stage']!=7){
                           
                      
                    //print_r($item);
                    if(isset($_GET['sort']) && $_GET['sort']=='client'){
                        $sort = $item['company']; 
                        $hiden_sort = $item['client_addres'];                       
                        $in_table=$item['nickName'];
                        $greate_row_driver_list = 'greate_row_driver_list(this,\''.$_GET["sample_page"].'\', \'return\')';                        
                        $check_on_for_request =     'check_on_for_request(this,\''.$_GET["sample_page"].'\', \'return\')';
                        }else{
                        $sort = $item['nickName']; 
                        $hiden_sort = $item['supplier_addres'];                        
                        $in_table=$item['company'];
                        $greate_row_driver_list = 'greate_row_driver_list(this,\''.$_GET["sample_page"].'\')';
                        $check_on_for_request =     'check_on_for_request(this,\''.$_GET["sample_page"].'\')';
                        } 
                        
                        if(!isset($supplier)){
                        ++$table;
                        $count=1;
                        /* ?page=samples&sample_page=sample_request */
                        echo '<form name="request_samples" id="request_samples_2" action="modules/samples/function_send.php" method="post">
                       
                        	<div class="sample_content_tables"><table id="table_'.$table.'">
                            <thead>
                                <tr>
                                    <td width="2%"></td>
                                    <td  class="sample_num_rows_check" width="2%"><input type="checkbox" id="check_table_'.$table.'" name="c_'.$item['id_n'].'" onChange="'.$check_on_for_request.'" ><label for="check_table_'.$table.'"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                                    <td colspan="9">'.$sort.'<input type="hidden" value="'.$hiden_sort.'"></td>
                                    <td width="12%"><img src="skins/images/img_design/reset_btn_minus.png" style="cursor:pointer;" onClick="comment_off(this)"></td>
                                </tr>
                            </thead>
                           <tbody>';
                             if($item['stage']==4){
                            $stage_b = 'style="background:#F2CE04;"';//в процессе доставки
                            }else if($item['stage']==5){
                            $stage_b = 'style="background:#8ecdf6;"';//у клиента
                            }else if($item['stage']==6){

                            $stage_b = '';//возврат
                            }else{
                            	$stage_b = 'style="background:#F9AD9F;"';
                                $disabled = '';
                            }
                         echo'
                                <tr>
                                    <td class="sample_num_rows"><!--<div class="marker_sytatus"'.$stage_b.'>&nbsp;&nbsp;</div>-->'.$count.'</td>
                                    <td class="sample_num_rows_check"><input type="checkbox" id="check_'.$item['id_n'].'" name="c_'.$item['id_n'].'" onChange="'.$greate_row_driver_list.'" ><label for="check_'.$item['id_n'].'"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                                    <td width="10%">'.$in_table.'</td>
                                    <td width="9%" onclick="img_show_togle(this)" class="td_togle">'.$item['art'].'<div><img src="'.$img_catalog.$img_arr[$item['art']]['small'].'"></div></td>
                                    <td width="30%">'.$item['name'].'</td>
                                   <td width="6%" class="sample_num_rows_unit">
                                        '.$item['quantity_of_samples'].'
                                    </td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_supplier'].'</span> р</td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_client'].'</span> р</td>                                    
                                    <td width="6%">'.$item['date_of_receipt'].'</td>
                                    <td width="6%">';
                          echo deposit_red_date($item['date_of_deposit'],$item['postponement_date']);
                          echo '</td>
                                    <td width="6%">'.red_date($item["postponement_date"]).'</td>
                                    <td class="sample_note"><input type="text" name="" value="'.$item['note'].'" onKeyup="change_note(this)"/></td>
                                </tr>';
                       }else if(isset($supplier) && $supplier==$sort){
                         if($item['stage']==4){
                            $stage_b = 'style="background:#F2CE04;"';//в процессе доставки
                            $disabled = 'disabled';
                            }else if($item['stage']==5){
                            $disabled = 'disabled';
                            $stage_b = 'style="background:#8ecdf6;"';//у клиента
                            }else if($item['stage']==6){
                            $disabled = 'disabled';

                            $stage_b = '';//возврат
                            }else{
                            	$stage_b = 'style="background:#F9AD9F;"';
                                $disabled = '';
                            }
                        echo'
                                <tr>
                                    <td class="sample_num_rows"><!--<div class="marker_sytatus"'.$stage_b.'>&nbsp;&nbsp;</div>-->'.$count.'</td>
                                    <td class="sample_num_rows_check"><input type="checkbox" id="check_'.$item['id_n'].'" name="c_'.$item['id_n'].'" onChange="'.$greate_row_driver_list.'" ><label for="check_'.$item['id_n'].'"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                                    <td width="10%">'.$in_table.'</td>
                                    <td width="9%" onclick="img_show_togle(this)" class="td_togle">'.$item['art'].'<div><img src="'.$img_catalog.$img_arr[$item['art']]['small'].'"></div></td>
                                    <td width="30%">'.$item['name'].'</td>
                                    <td width="6%" class="sample_num_rows_unit">
                                        '.$item['quantity_of_samples'].'
                                    </td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_supplier'].'</span> р</td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_client'].'</span> р</td>                                    
                                    <td width="6%">'.$item['date_of_receipt'].'</td>
                                    <td width="6%">';
                          echo deposit_red_date($item['date_of_deposit'],$item['postponement_date']);
                          echo '</td>
                                    <td width="6%">'.red_date($item["postponement_date"]).'</td>
                                    <td class="sample_note"><input type="text" name="" value="'.$item['note'].'" onKeyup="change_note(this)"/></td>
                                </tr>';
                        }else if(isset($supplier) && $supplier!=$sort){
                        echo'
                            </tbody>
                        </table></div>';
                        $count=1;
                        ++$table;
                        echo '<div class="sample_content_tables">
                        <table id="table_'.$table.'">
                            <thead>
                                <tr>
                                    <td width="2%"></td>
                                    <td  class="sample_num_rows_check" width="2%"><input type="checkbox" id="check_table_'.$table.'" name="c_'.$item['id_n'].'" onChange="'.$check_on_for_request.'" ><label for="check_table_'.$table.'"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                                    <td colspan="9">'.$sort.'<input type="hidden" value="'.$hiden_sort.'"></td>
                                    <td width="12%"><img src="skins/images/img_design/reset_btn_minus.png" style="cursor:pointer;" onClick="comment_off(this)"></td>
                                </tr>
                            </thead>
                           <tbody>';
                             if($item['stage']==4){
                            $stage_b = 'style="background:#F2CE04;"';//в процессе доставки
                            $disabled = 'disabled';
                            }else if($item['stage']==5){
                            $disabled = 'disabled';
                            $stage_b = 'style="background:#8ecdf6;"';//у клиента
                            }else if($item['stage']==6){
                            $disabled = 'disabled';

                            $stage_b = '';//возврат
                            }else{
                            	$stage_b = 'style="background:#F9AD9F;"';
                                $disabled = '';
                            }
                         echo'
                                <tr>
                                    <td class="sample_num_rows"><!--<div class="marker_sytatus"'.$stage_b.'>&nbsp;&nbsp;</div>-->'.$count.'</td>
                                    <td class="sample_num_rows_check"><input type="checkbox" id="check_'.$item['id_n'].'" name="c_'.$item['id_n'].'" onChange="'.$greate_row_driver_list.'" ><label for="check_'.$item['id_n'].'"></label><input type="hidden" value="'.$item['client_id'].'" class="client_hidden"><input type="hidden" value="'.$item['company'].'" class="client_nickName_hidden"><input type="hidden" value="'.$item['supplier_id'].'" class="suplier_hidden"><input type="hidden" value="'.$item['nickName'].'" class="suplier_nickName_hidden"><input type="hidden" value="'.$item['client_addres'].'" class="client_addres_hidden"><input type="hidden" value="'.$item['supplier_addres'].'" class="supplier_addres"></td>
                                    <td width="10%">'.$in_table.'</td>
                                    <td width="9%" onclick="img_show_togle(this)" class="td_togle">'.$item['art'].'<div><img src="'.$img_catalog.$img_arr[$item['art']]['small'].'"></div></td>
                                    <td width="30%">'.$item['name'].'</td>
                                    <td width="6%" class="sample_num_rows_unit">
                                        '.$item['quantity_of_samples'].'
                                    </td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_supplier'].'</span> р</td>
                                    <td width="6%" class="sample_summ"><span>'.$item['under_pledge_client'].'</span> р</td>                                    
                                    <td width="6%">'.$item['date_of_receipt'].'</td>
                                    <td width="6%">';                                   
                          echo deposit_red_date($item['date_of_deposit'],$item['postponement_date']);                          
                          echo '</td>
                                    <td width="6%">'.red_date($item["postponement_date"]).'</td>
                                    <td class="sample_note"><input type="text" name="" value="'.$item['note'].'" onKeyup="change_note(this)"/></td>
                                </tr>';
                            
                        }
                        ++$count;
                    $supplier=$sort;	
                    
                    }
                    }
                    }
            }
            echo'
                            
                            </tbody>
                        </table></div></form>';
                        
            ?> 
        
        
        </div>
        
        	
        


</div>   </div> 
<!-- start window driver -->
<div id="bg">
<div class="form_for_driver_header"><span>транспортировка</span><a onClick="document.getElementById('bg').style.display='none'">закрыть Х</a></div>
<div class="windows">

    <div id="form_for_driver_body">
	</div>
</div>
</div>
<!-- end window driver -->    
<!-- end skins/tpl/sample/request.tpl --> 

