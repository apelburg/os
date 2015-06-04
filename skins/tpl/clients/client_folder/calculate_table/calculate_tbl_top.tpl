<!-- begin skins/tpl/clients/client_folder/calculate_tbl_top.tpl --> 
<script type="text/javascript" src="libs/js/apl_calculators.js"></script> 
<script type="text/javascript" src="libs/js/calculatingTableEmulator.js"></script>
<script type="text/javascript" src="libs/js/assosiatingScrolledTable.js"></script>
<script type="text/javascript" src="libs/js/tableDataManager.js"></script>  
<script type="text/javascript">
   tableDataManager.url = '?page=clients&section=client_folder&subsection=calculate_table&client_id=<?php echo $client_id; ?>&update_tr_field_ajax=1';
</script>
<link href="./skins/css/checkboxes.css" rel="stylesheet" type="text/css">
<link href="./skins/css/calculators.css" rel="stylesheet" type="text/css">
<div class="calculate_tbl_container">
    <table class="calculate_tbl" scrolled="header">
        <tr class="header">
            <td>&nbsp;</td>
            <td>&nbsp;</td>
            <td style="padding:0px; text-align:left;" colspan="2">
                <div class="master_button noselect">
                    <a href="#" onclick="openCloseMenu(event,'tableMenu'); return false;">&nbsp;</a>
                    <div id="reset_master_button" class="reset_button" onclick="resetMasterBtn(this);">&nbsp;</div>
                </div>
            </td>
            <td>артикул/описание/поставщик</td>
            <td><a href="#" onclick="alert(calculatingTableEmulator.tbl_model);return false;">&nbsp;&nbsp;</a>
            <a href="#" onclick="print_r(calculatingTableEmulator.tbl_model);return false;">&nbsp;&nbsp;</a></td>
            <td class="bordered">тираж</td>
            <td class="bordered" colspan="4">цена входящая 1ш./тираж</td>
            <td class="bordered" colspan="4">скидка наценка</td>
            <td class="bordered" colspan="4">цена клиента 1ш./тираж</td>
            <td class="bordered">+/-</td>
            <td class="bordered" colspan="2">delta</td>
            <td>срок изг.</td>
            <td>&nbsp;</td>
        </tr>
    </table>
    <div id="scroll_container">    
        <table id="calculate_tbl" class="calculate_tbl" client_id="<?php echo $client_id; ?>" control_num="<?php echo $control_num; ?>" tbl="managed" scrolled="body">    
            <?php echo $rows; ?>
        </table>
        <div style="height:14px"></div>
    </div>
</div>

<!-- end skins/tpl/clients/client_folder/calculate_tbl_top.tpl -->