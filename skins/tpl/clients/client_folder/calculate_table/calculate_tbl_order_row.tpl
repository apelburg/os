<tr class="order_row" id="row_<?php echo $rt_id; ?>" hidden_num="<?php echo $hidden_num; ?>" type="order_row" order_num="<?php echo $order_num; ?>" client_manager_id="<?php echo $rt_client_manager_id; ?>">
    <td class="flank_cell">&nbsp;</td>
    <td class="row_num" onclick="copy_order(this,<?php echo $rt_id; ?>);"></td>
     <td class="master_btn"  style="padding:0px;">
        <input type="checkbox" name="_masterBtn" rowIdNum="<?php echo $rt_id; ?>"/>
    </td>
    <td class="order_details" colspan="2">Заказ № <?php echo $order_num; ?>/ <?php echo $date; ?> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; счет № AAA от 01.01.0001</td>
    <td class="empty" colspan="12">&nbsp;</td>
    <td class="client_details" colspan="6">
        <div class="client_details_select" row_id="<?php echo $rt_id; ?>" client_id="<?php echo $client_id; ?>" onclick="openCloseMenu(event,'clientManagerMenu');">контакт: <?php echo $client_manager; ?></div>
    </td>
    <td class="flank_cell">&nbsp;</td>
</tr>