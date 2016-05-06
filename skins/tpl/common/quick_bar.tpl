<table class="quick_bar_tbl  noselect">
    <tr>
        <td class="quick_button">
            <div id="quick_button_back">
                <?php echo $quick_button_back; ?>
            </div>
            <?php echo $quick_button; ?>  
        </td>
        <td class="quick_search">
            <!-- <form> -->
                <?php
                    if(isset($_GET['page']) && $_GET['page'] == 'suppliers'){
                        include_once('supplier_search.tpl');
                    }else{
                        include_once('client_search.tpl');
                    }

                ?>
            <!-- <input type="submit" style="display:none">  -->
            <!-- </form> -->
        </td>

        <td class="quick_button_2">
            <!--<<div id="new_query" class="std_button">Новый запрос</div>-->
        </td>
        <td class="quick_button_3">

            <!--<div class="quick_button_circle">
                <div class="quick_button_circle__circle" style="background-image: url(./skins/images/img_design/button_circle_1.png);border-color:green">
                    <div class="quick_button_circle__alert">12</div>
                </div>
                <div class="quick_button_circle__text">Уведомления</div>
            </div> -->
            <?php
            if(@$ACCESS['planner']['access']){
                echo $planner_display;
            }
            ?>
        </td>
        <td class="quick_view_button">
            <?php echo $view_button; ?>
        </td>
    </tr> 
</table>