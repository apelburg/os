<table class="quick_bar_tbl  noselect">
    <tr>
        <td class="quick_button">
            <div id="quick_button_back">
                <a href="javascript:history.go(-1)"></a>
            </div>
            <?php echo $quick_button; ?>  
        </td>
        <td class="quick_search">
            <!-- <form> -->
                <div class="search_div">
                    <div class="search_cap">Поиск:</div>
                    <div class="search_field">                    
                        <input id="search_query" placeholder="по онлайн сервису" type="text" onclick="delete_alert_win();" value="<?php echo (isset($_GET['search']))? $_GET['search'] : ''; ?>"><div class="undo_btn"><a href="#"  onclick="return  clear_search_input();">&#215;</a></div></div>
                    <div class="search_button" onClick="do_search(this/*'filter_by_letter&num_page&filter_by_rating&sotring'*/);">&nbsp;</div>
                    <div class="clear_div"></div>
                </div>
            <!-- <input type="submit" style="display:none">  -->
            <!-- </form> -->
        </td>

        <td class="quick_button_2">
            <div id="new_query" class="std_button">Новый запрос</div>
        </td>
        <td class="quick_button_3">

            <div class="quick_button_circle">
                <div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_1.png);border-color:green">
                    <div class="quick_button_circle__alert">12</div>
                </div>
                <div class="quick_button_circle__text">Уведомления</div>
            </div> 

            <div class="quick_button_circle">
                <div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_2.png); border-color:red">
                    <div class="quick_button_circle__alert">12</div>
                </div>
                <div class="quick_button_circle__text">Планировщик</div>
            </div> 
        </td>
        <td class="quick_view_button">
            <?php echo $view_button; ?>
        </td>
    </tr> 
</table>