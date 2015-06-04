<div class="delete_contact_face_table">
    <div class="delete_contact_face_table_button" data-contface="<?php echo $this_contact_face['id']; ?>">X</div>
</div>

<div class="client_table client_contact_face_tables">
	<table class="client_table_gen" >
    	<tr>
        	<td>
            	<table class="contact_face_tbl_edit" data-contface="<?php echo $this_contact_face['id']; ?>">
                	<tr>
                    	<td>ФИО</td>
                    	<td><strong><?php echo $this_contact_face['last_name'].' '.$this_contact_face['name'].' '.$this_contact_face['surname']; ?></strong></td>
                    </tr>
                	<tr>
                    	<td>Должность</td>
                    	<td><?php echo $this_contact_face['position']; ?></td>
                    </tr>
                	<tr>
                    	<td>Отдел</td>
                    	<td><?php echo $this_contact_face['department']; ?></td>
                    </tr>
                	<tr>
                    	<td>Примечание</td>
                    	<td><?php echo $this_contact_face['note']; ?></td>
                    </tr>
                </table>
        	<td>
            	<?php echo $cont_company_phone; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="add_new_row_phone button_add_new_row" data-parent-id="<?php echo $this_contact_face['id']; ?>" data-parenttable="SUPPLIERS_CONT_FACES_TBL">добавить телефон</div></td>
                    </tr>
                </table>                
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
                <table>
                    <tr>
                        <td></td>
                        <td><div class="button_add_new_row other_row add_new_row_other" data-parenttable="SUPPLIERS_CONT_FACES_TBL"  data-parent-id="<?php echo $this_contact_face['id']; ?>">добавить...</div></td>
                    </tr>
                </table>
            </td>
    </tr>
    </table>    
    <div class="border_in_table"></div>
</div>