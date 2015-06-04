<tr class="itog_row" id="row_<?php echo $rt_id; ?>"  hidden_num="<?php echo $hidden_num; ?>" type="itog_row">
    <td class="flank_cell">&nbsp;</td>
    <td class="row_num"></td>
    <td class="master_btn" style="padding:0px;">
        <!--<input type="checkbox" name="masterBtn" rowIdNum="<?php //echo (isset($rt_id))? $rt_id : '' ; ?>"/>-->
    </td>
    <td class="status">оплачено 100%</td>
    <td class="itog" colspan="2">Итого</td>
    <td></td>
    <td colspan="2"></td>
    <td class="itog_coming_summ" id="itog_coming_summ<?php echo $hidden_num; ?>"><?php echo number_format($itog_coming_summ,"2",".",''); ?></td>
    <td class="item_currency_bg">р.</td>
    <td colspan="2"></td>
    <td colspan="2"></td>
    <td colspan="2"></td>
    <td class="itog_summ" id="itog_summ<?php echo $hidden_num; ?>"><?php echo number_format($itog_summ,"2",".",''); ?></td>
    <td class="item_currency_bg">р.</td>
    <td class="bg"></td>
    <td class="itog_delta" id="itog_delta<?php echo $hidden_num; ?>"><?php echo number_format($itog_delta,"2",".",''); ?></td>
    <td class="item_currency_bg">р.</td>
    <td class="item_555"></td>
    <td class="flank_cell">&nbsp;</td>
</tr>