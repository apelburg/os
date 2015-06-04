<table class="quick_bar_tbl">
        <tr>
            <td class="quick_button" <?php   if(isset($_GET['page']) && $_GET['sample_page']=='received'){echo 'style="width:600px;"';} ?>>
            <?php
            ######################## кнопка #########################
            if(isset($_GET['page']) && $_GET['sample_page']=='start'){
           		echo '<input type="button" onclick="document.forms[\'request_samples\'].submit()" name="request_samples" class="button_off"  value="запросить образцы">';
            }else if(isset($_GET['page']) && $_GET['sample_page']=='request'){           
            	echo '<input type="button" onclick="submit_received_button(\'request\',\'button_1\')" name="request_samples" class="button_on" value="заказать">';
            	echo '<input type="button" onclick="submit_received_button(\'request\',\'button_2\')" name="request_samples" class="button_off" value="позиций нет - {х}">';
            }else if(isset($_GET['page']) && $_GET['sample_page']=='ordered'){           
            	echo '<input type="button" onclick="submit_received_button(\'ordered\',\'button_1\')" name="request_samples" class="button_on" value="образец получен">';
            	echo '<input type="button" onclick="submit_received_button(\'ordered\',\'button_2\')" name="request_samples" class="button_off" value="позиций нет - {х}">';
            }else if(isset($_GET['page']) && $_GET['sample_page']=='received'){           
            	echo '<input type="button" id="received_b1" onclick="$(\'#bg\').show()" name="request_samples" class="button_off" value="Доставить" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:100px;">';
            	echo '<input type="button" id="received_b2" onclick="submit_received_button(\'received\',\'received_2\')" name="request_samples" class="button_off" value="Срок продлен" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:100px;">';
            	echo '<input type="button" onclick="submit_received_button(\'received\',\'received_3\')" name="request_samples" class="button_off" value="Возвращен клиентом" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:150px;">';
                echo '<input type="button" onclick="submit_received_button(\'received\',\'received_4\')" name="request_samples" class="button_off" value="У клиента" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:100px;">';
            }else if(isset($_GET['page']) && $_GET['sample_page']=='client_hand'){           
            	echo '<input type="button" id="received_b2" onclick="submit_received_button(\'client_hand\',\'button_1\')" name="request_samples" class="button_off" value="Срок продлен" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:100px;">';
            	echo '<input type="button" onclick="submit_received_button(\'client_hand\',\'received_5\')" name="request_samples" class="button_off" value="Возвращен клиентом" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:150px;">';
            }else if(isset($_GET['page']) && $_GET['sample_page']=='return'){           
            	echo '<input type="button" onclick="$(\'#bg\').show()" name="request_samples" class="button_off" value="Вернуть образец" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:150px;">';
            	echo '<input type="button" onclick="submit_received_button(\'return\',\'button_1\')" name="request_samples" class="button_off" value="в полученные" style="margin-left:2%;margin-right:2%;margin-top:20px;margin-bottom:21px; width:150px;">';
            }else{
            
           		echo '<div class="quick_button_div" style="height:45px">
                    <a href="#" class="button" id="received_b3" onclick="openCloseMenu(event,\'quickMenu\'); return false;">&nbsp;</a>
                </div>';
            } 
            ######################## кнопка #########################
            ?>
                <!---->
            </td>
            <td class="quick_search">
                <div class="search_div">
                    <div class="search_cap">Поиск по:</div>
                    <div class="search_field"><input type="text"></div>
                    <div class="search_button"><img src="./skins/images/img_design/quick_search_button.png"></div>
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