<!-- <?php echo __FILE__; ?> -- START-->
<!-- добавление телефона -->
<div id="add_new_phone" style="display:none;" title="Добавить номер телефона">
	<form>
		<table>
			<tr>
				<td>
					<select name="type_phone" id="type_phone">
						<option value="Рабочий" checked>Рабочий</option>
						<option value="Факс" >Факс</option>
						<option value="Домашний" >Домашний</option>
					</select>
				</td>
				<td><input type="text" name="telephone" id="phone_numver"></td>
				<td><div>доб. <input type="text" name="dop_phone" id="dop_phone_numver"></div></td>
			</tr>
		</table>
		<input type="hidden" name="AJAX" value="add_new_phone_row">
		<input type="hidden" name="client_id" value="">
		<input type="hidden" name="parent_tbl" value="">
	</form>
</div>

<!-- удаление телефона -->
<style type="text/css" media="screen">
	#delete_one_contact_row{ display: none}
</style>
<div id="delete_one_contact_row">
	Вы уверены, что хотите удалить данное поле?
</div>
<!-- добавление www, VK, E-mail -->
<style type="text/css" media="screen">
	#new_other_row_info{ display: none}
</style>
<div id="new_other_row_info">
	<form>
		<table>
			<tr>
				<td>
					<select name="type" id="new_other_row_infoType">
						<option selected="selected" value="0" disabled>Выберите тип записи</option>
						<option value="email"  >E-mail</option>
						<option value="web_site" >адрес web - сайта</option>
						<option value="vk" >адрес VK</option>
						<option value="fb" >адрес FaceBook</option>
						<option value="twitter" >адрес Twitter</option>
						<option value="skype" >Skype</option>
						<option value="icq" >ICQ</option>
						<option value="other" >другое</option>
					</select>
				</td>
				<td><input type="text" name="input_text" id="input_text"></td>
				<input type="hidden" name="AJAX" value="add_new_other_row">
				<!-- <input type="hidden" name="type" id="new_other_row_infoType_input"> -->
				<input type="hidden" name="client_id" value="<?php echo $supplier_id; ?>">
				<input type="hidden" name="parent_tbl" value="">
			</tr>
		</table>
	</form>
</div>

<!-- contact face edit -->
<style type="text/css">
	#contact_face_edit_form{display: none}
	#contact_face_edit_form table{width: 100%;}
	#contact_face_edit_form table tr td:nth-of-type(1){width: 70px; text-align: right}
	#contact_face_edit_form input{width: 100%}
</style>
<div id="contact_face_edit_form">	
	<form>
		<table>
			<tr>
				<td>Фаммилия</td>
				<td><input type="text" name="last_name" ></td>
			</tr>
			<tr>
				<td>Имя</td>
				<td><input type="text" name="name" ></td>
			</tr>
			<tr>
				<td>Отчество</td>
				<td><input type="text" name="surname" ></td>
			</tr>
			<tr>
				<td>Должность</td>
				<td><input type="text" name="position" ></td>
			</tr>
			<tr>
				<td>Отдел</td>
				<td><input type="text" name="department" ></td>
			</tr>
			<tr>
				<td>Прим.</td>
				<td><input type="text" name="note" ></td>
			</tr>
		</table>
		<input type="hidden" name="AJAX" value="contact_face_edit_form">
		<input type="hidden" name="id" value="">
	</form>
</div>

<style type="text/css">
	#client_dop_information_cont_w{
		display: none
	}
	#client_dop_information_cont_w table{ width:100%}
	#client_dop_information_cont_w textarea,#client_dop_information_cont_w input{width:100%; font-size: 12px}
	#client_dop_information_cont_w table tr td:nth-of-type(1){width: 50px}
</style>
<div id="client_dop_information_cont_w">
	<form>
		<table>
            <tr>
                <td>Дополнительная информация</td>
                <td>
                	<textarea placeholder="информация отсутствует" name="dop_info"><?php echo (!empty($supplier['dop_info']))?$supplier['dop_info']:''; ?></textarea>                	
               	</td>
            </tr>
            <tr style="display:none">
                <td>Папка</td>
                <td>
                	<input type="text" name="ftp_folder" value="<?php echo (!empty($supplier['ftp_folder']))?$supplier['ftp_folder']:'' ?>" placeholder="информация отсутствует">
                </td>
            </tr>
        </table>
		<input type="hidden" name="AJAX" value="edit_client_dop_information">
		<input type="hidden" name="id" value="<?php echo $supplier_id; ?>">
	</form>
</div>

<!-- удаление строки с контактным лицом -->
<style type="text/css">
	#deleteing_row_cont_face{display: none}

</style>
<div id="deleteing_row_cont_face">Информация о данном контактном лице будет удалена безвозвратно. Продолжить?</div>

<!-- добавление нового контактного лица -->
<style type="text/css">
	#contact_face_new_form{display: none}
	#contact_face_new_form table{width: 100%;}
	#contact_face_new_form table tr td:nth-of-type(1){width: 70px; text-align: right}
	#contact_face_new_form input{width: 100%}
</style>
<div id="contact_face_new_form">	
	<form>
		<table>
			<tr>
				<td>Фаммилия</td>
				<td><input type="text" name="last_name" ></td>
			</tr>
			<tr>
				<td>Имя</td>
				<td><input type="text" name="name" ></td>
			</tr>
			<tr>
				<td>Отчество</td>
				<td><input type="text" name="surname" ></td>
			</tr>
			<tr>
				<td>Должность</td>
				<td><input type="text" name="position" ></td>
			</tr>
			<tr>
				<td>Отдел</td>
				<td><input type="text" name="department" ></td>
			</tr>
			<tr>
				<td>Прим.</td>
				<td><input type="text" name="note" ></td>
			</tr>
		</table>
		<input type="hidden" name="AJAX" value="contact_face_new_form">
		<input type="hidden" name="parent_id" value="<?php echo $supplier_id; ?>">
	</form>
</div>



<style type="text/css">
	#show_requesit,#edit_requesit,#dialog-confirm,#dialog-confirm2,#create_requesit,#create_client,#new_person_type_req,#client_delete_div{display:none;}#create_client input{width: 100%}
	#new_person_type_req table{width: 100%}
	#new_person_type_req input{width: 90%}
	#create_client input{width: 100%}
</style>
<div id="show_requesit"></div>
<div id="create_requesit"></div>
<div id="edit_requesit"></div>
<div id="create_client">
	<form>
		
		<table>
		<tr>
			<td>Название</td>
			<td>
				<input type="text" name="company">
				<input type="hidden" name="AJAX" value="create_client">
				<input type="hidden" name="rate" value="0">
			</td>
		</tr>
		<tr>
			<td colspan="2"></td>
		</tr>	
		<tr>
			<td>Дополнительная информация</td>
			<td><textarea type="text" name="dop_info"></textarea></td>
		</tr>	
	</table>
	</form>
</div>
<div id="new_person_type_req">
	<form>
		<input type="hidden" name="AJAX" value="new_person_type_req">
		<table>
			<tr>
				<td>
					Должность: <br>
					<input type="text" name="position"><br>
					<span style="font-size:8px; float:left; color:#E45A71; margin-bottom:5px;">ВНИМАНИЕ! Должность пишите с большой буквы</span>
				</td>
			</tr>
			<tr>
				<td>
					Должность в родительном падеже(кого? чего?): <br>
					<input type="text" name="position_in_padeg"><br>
					<span style="font-size:8px; float:left; color:#E45A71; margin-bottom:5px;">ВНИМАНИЕ! Должность пишите с большой буквы</span>
				</td>
			</tr>
		</table>
	</form>
</div>
<div id="dialog-confirm">Данные об этом контакте будут удалены безвозвратно. Продолжить? </div>
<div id="dialog-confirm2">Данные реквизиты будут удалены безвозвратно. Продолжить? </div>
<!-- <?php echo __FILE__; ?> -- END-->