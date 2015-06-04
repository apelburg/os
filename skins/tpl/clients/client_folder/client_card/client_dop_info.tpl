<div class="client_table">
	<table class="client_table_gen" >
    	<tr>
        	<td>
            	<table>
                	<tr>
                    	<td>Примечание</td>
                    	<td><?php echo (!empty($client['dop_info']))?$client['dop_info']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?></td>
                    </tr>                    
                    <tr>
                        <td>Папка</td>
                        
                        <td>
                            <a target="_blank" href="№">
                            <?php echo (!empty($client['ftp_folder']))?'Z:/'.$client['ftp_folder']:'<span style="color:rgb(187, 187, 187);">информация отсутствует</span>'; ?></a></td>
                    </tr>
                </table>
        	<td>
            	
            </td>
            <td>
            	
            </td>
    </tr>
    </table>
    
   
    <div class="border_in_table"></div>
</div>