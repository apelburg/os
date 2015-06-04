<div class="client_table" id="add_contact_face_new_form" style="text-align:center; font-size:12px">
<span class="button_add_new_row" style="float:none">Добавить контактное лицо</span>
<div class="border_in_table" style="margin-top:10px;"></div>
</div>
<div class="client_table">
	<table class="client_table_gen">
    	<tr>            
        	<td>
            	<table  id="client_dop_information">
                	<tr>
                    	<td>Дополнительная информация</td>
                    	<td>
                            <?php echo (!empty($supplier['dop_info']))?$supplier['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?>
                        </td>
                    </tr>
                	<tr>
                    	<td>Папка</td>
                    	<td><?php echo (!empty($supplier['ftp_folder']))?'Z:/'.$supplier['ftp_folder']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>' ?></td>
                    </tr>
                </table>
        	<td>
            	
            </td>
            <td>
            	
            </td>
    </tr>
    </table>
    <div class="border_in_table"></div>
    <div id="client_delete" data-id="<?php echo $supplier_id; ?>">Удалить поставщика</div>
</div>