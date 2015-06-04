
<!-- <script type="text/javascript" src="libs/js/client_card_table.js"></script> -->
<script type="text/javascript" src="libs/js/client_folders.js"></script><!--// для отправки стандартного окна методом POST -->
<script type="text/javascript" src="libs/js/supplier_card.js"></script>
<script type="text/javascript" src="libs/js/rate_supplier.js"></script>
<link href="./skins/css/client_card.css" rel="stylesheet" type="text/css">
<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table class="edit_general_info">
                    <tr>
                        <td>Сокращенное название</td>
                        <td>
                            <div class="edit_row edit" id="chenge_name_company" name="company" data-name="company" data-editType="text" data-button-name-window="save" data-idRow="<?php echo $supplier_id; ?>" data-tableName='SUPPLIERS_TBL'><?php echo trim($supplier['nickName']); ?></div>
                        </td>
                    </tr>

                    <tr>
                        <td>Полное название</td>
                        <td>
                            <div class="edit_row edit" id="chenge_fullname_company" name="company" data-name="fullcompany" data-editType="text" data-button-name-window="save" data-idRow="<?php echo $supplier_id; ?>" data-tableName='SUPPLIERS_TBL'><?php echo trim($supplier['fullName']); ?></div>
                        </td>
                    </tr>
                	<tr>
                    	<td>Рейтинг</td>
                    	<td class="no_edit">
                            <?php echo $supplierRating; ?>
                        </td>
                    </tr>
                	<tr>
                    	<td>Профили</td>
                    	<td>
                            <?php echo $get_activities; ?>
                        </td>
                    </tr>
                	<?php echo $supplier_address_s; ?>                    
                    <tr>
                        <td></td>
                        <td>
                            <div class="button_add_new_row adres_row"  data-tbl="SUPPLIERS_TBL" data-parent-id="<?php echo $supplier_id; ?>">Добавить адрес</div>
                        </td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="add_new_row_phone button_add_new_row"  data-parent-id="<?php echo $supplier_id; ?>" data-parenttable="SUPPLIERS_TBL">добавить телефон</div></td>
                    </tr>
                </table>
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row other_row add_new_row_other"  data-parent-id="<?php echo $supplier_id; ?>"  data-parenttable="SUPPLIERS_TBL">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
</div>
