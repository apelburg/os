<div class="client_table">
	<table class="client_table_gen" >
    	<tr>
        	<td>
            	<table>
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
            </td>
            <td>
            	<?php echo $cont_company_other; ?>
            </td>
    </tr>
    </table>
    
   
    <div class="border_in_table"></div>
</div>