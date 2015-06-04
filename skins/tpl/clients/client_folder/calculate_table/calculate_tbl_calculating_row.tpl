<tr class="calculating_row" type="calculating_row" id="row_<?php echo $rt_id; ?>" use_in_calculation="<?php echo $rt_marker_summ; ?>" hidden_num="<?php echo $hidden_num; ?>" control_num="<?php echo $control_num; ?>">
    <td class="flank_cell">&nbsp;</td>
    <td class="row_num" oncontextmenu="openCloseMenu(event,'contextmenu',{'id':<?php echo $rt_id; ?>,'control_num':<?php echo $control_num; ?>});"><?php echo $row_counter; ?></td>
    <td class="master_btn noselect" width="30">
        <!--<div class="<?php echo ($rt_master_btn == 1)? '':'container'; ?>" id="masterBtnContainer<?php echo $rt_id; ?>">
           <input type="checkbox" id="masterBtn<?php echo $rt_id; ?>" name="masterBtn" rowIdNum="<?php echo $rt_id; ?>" onclick="masterBtnVizibility(this,'masterBtnContainer<?php echo $rt_id; ?>');onClickMasterBtn(this,<?php echo $rt_id; ?>);return false;" <?php echo ($rt_master_btn == 1)? 'checked':''; ?> /><label for="masterBtn<?php echo $rt_id; ?>"></label>
        </div>-->
        <div class="<?php echo ($rt_master_btn == 1)? '':'container'; ?>" id="masterBtnContainer<?php echo $rt_id; ?>">
           <input type="checkbox" id="masterBtn<?php echo $rt_id; ?>" name="masterBtn" rowIdNum="<?php echo $rt_id; ?>" onclick="onClickMasterBtn(this,<?php echo $rt_id; ?>);return false;" <?php echo ($rt_master_btn == 1)? 'checked':''; ?> /><label for="masterBtn<?php echo $rt_id; ?>"></label>
        </div>
         
    </td>
    <!--<td class="master_btn" onmouseover="show_hide_div('master_btn_container<?php echo $rt_id; ?>','block');" onmouseout="show_hide_div('master_btn_container<?php echo $rt_id; ?>','none');">
        <div id="master_btn_container<?php echo $rt_id; ?>"><input type="checkbox" /></div>
    </td>-->
    <td class="item_status">принят на производство</td>
    <td class="item_article_and_name">
        <?php 
            if($rt_type == 'article')  eval('?>'.$extra_panel_for_basic_catalog_row.'<?php ');
            if($rt_type == 'ordinary') eval('?>'.$extra_panel_for_other_catalog_row.'<?php ');
            if($rt_type == 'print')    eval('?>'.$extra_panel_for_polygraphy_catalog_row.'<?php ');
            if($rt_type == 'services') eval('?>'.$extra_panel_for_basic_catalog_row.'<?php ');
        ?>
    </td>
    <td class="item_333">
        <div class="print" type="extra_panel_tail">
            <?php  
               if($rt_type == 'article' || $rt_type == 'ordinary') echo '<span class="add_print_row_btn" onclick="aplCalculators.show_box();return false;">print</span>'; 
            ?>
        </div>
        <div class="plus"  type="name_tail">+</div>
    </td>
    <td class="item_quantity">
        <input id="quantity_input_<?php echo $rt_id; ?>" class="num_input" calculating_type="quantity"  hidden_num="<?php echo $hidden_num; ?>" value="<?php echo $rt_quantity; ?>" />
        <!--style="text-align:center;"-->
    </td>
    <td class="item_coming_price">
        <input id="coming_price_input_<?php echo $rt_id; ?>" class="num_input" calculating_type="coming_price"  hidden_num="<?php echo $hidden_num; ?>" value="<?php echo $rt_coming_price; ?>" />
    </td>
    <td class="item_currency">р.</td>
    <td class="item_coming_price_summ" id="coming_price_summ<?php echo $hidden_num; ?>"><?php echo $coming_price_summ; ?></td>
    <td class="item_currency_bg">р.</td>
    <td class="item_discount">0</td>
    <td class="item_currency">р.</td>
    <td class="item_discount_percent">0</td>
    <td class="item_currency_bg">%</td>
    <td class="item_price">
        <input id="price_input_<?php echo $rt_id; ?>" class="num_input" calculating_type="price"  hidden_num="<?php echo $hidden_num; ?>" value="<?php echo $rt_price; ?>" />
    </td>
    <td class="item_currency">р.</td>
    <td class="item_price_summ" id="price_summ<?php echo $hidden_num; ?>"><?php echo $price_summ; ?></td>
    <td class="item_currency_bg">р.</td>
    <td class="marker_summ<?php echo $style_marker_summ; ?>" hidden_num="<?php echo $hidden_num; ?>" onclick=" calculatingTableEmulator.switching_calculation(this);"><?php echo $viewing_marker_summ; ?></td>
    <td class="item_delta" id="delta<?php echo $hidden_num; ?>"><?php echo $delta; ?></td>
    <td class="item_currency_bg">р.</td>
    <td class="item_term">29.03.13</td>
    <td class="flank_cell">&nbsp;</td>
</tr>