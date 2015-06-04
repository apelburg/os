<!-- КОНТАКТНЫЕ ДАННЫЕ ДЛЯ РЕКВИЗИТОВ -->
  <div>    
  <table class="client_form_table" id="chief_fields_tbl">      
    <tr>
    </tr>
            <tr>
            <td></td>
            <td colspan="2"><div style="text-align:left;font-weight:bold; height:15px;">Сотрудники</div></td>
            <td colspan="3">
              <input type="radio" class="radio_acting" field_type="acting" name="acting" <?php if($contact['acting']=='1'){echo 'checked';} ?>> Лицо, подписывающее договор  
              <input type="hidden" class="acting_check" name="form_data[managment1][<?php echo ($key+1); ?>][acting]" value="<?php echo $contact['acting']; ?>">
              <input type="hidden" field_type="id" name="form_data[managment1][<?php echo ($key+1); ?>][id]" value="<?php echo $contact['id']; ?>">
              <input type="hidden" field_type="requisites_id" name="form_data[managment1][<?php echo ($key+1); ?>][requisites_id]" value="<?php echo $contact['requisites_id']; ?>">
              <input type="hidden" field_type="type" name="form_data[managment1][<?php echo ($key+1); ?>][type]" value="<?php echo $contact['type']; ?>">
           </td>
    </tr>
        <tr>
            <td width="10%" align="right">Должность</td>
            <td width="23%">       
              <!-- <input type="hidden" name="form_data[managment1][1][position]" value="Генеральный директор"> -->
                <select class="my_select" name="form_data[managment1][<?php echo ($key+1); ?>][post_id]">
                    <?php echo $get__clients_persons_for_requisites; ?>
                </select>
                <style type="text/css">
                
                </style>
                <div class="new_person_type_req">+</div>
              
        <div class="note_div" style="margin:22px 0 0 3px; ">
                <div class="note">Должность пишите с большой буквы.</div>
      </div>
          </td>
          <td width="10%">На основании</td>
            <td width="23%">
                <input type="text" name="form_data[managment1][<?php echo ($key+1); ?>][basic_doc]" value="<?php echo $contact['basic_doc']; ?>">
                <div class="note_div">
                  <span onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value=this.innerHTML" style="float:left; font-size:9px; background: #75B775; color:white; cursor:pointer; text-align:center; margin:3px 10px 0 0; line-height:10px; padding:2px; border:1px solid white;">Устава</span>
                  <span onclick="this.parentNode.parentNode.getElementsByTagName('input')[0].value=this.innerHTML" style="float:left; font-size:9px; background: #75B775; color:white; cursor:pointer; text-align:center; margin:3px 10px 0 0; line-height:10px; padding:2px; border:1px solid white;">доверенности</span>
                </div>
            </td>
          <td colspan="2" width="23%" align="center">           
          </td>
          <td rowspan="2" width="10%" style="padding-left:50px;">
              <delete_btn>
                <span class="cont_faces_field_delete_btn" data-tbl="CLIENT_REQUISITES_MANAGMENT_FACES_TBL" data-id="<?php echo $contact['id']; ?>" style="<?php if($key==0){echo "display:none;";} ?>cursor:default">x</span>
              </delete_btn>
          </td>
    </tr>
    <tr>
            <td width="180" align="right">ФИО</td>
            <td width="220">
                <input type="text" name="form_data[managment1][<?php echo ($key+1); ?>][name]" value="<?php echo $contact['name']; ?>">
                <div class="note_div">
                   <div class="note">ФИО указывайте полностью и в следующем порядке:<br>Иванов Иван Иванович.</div>
                </div>
            </td>
            <td width="40" align="right">В падеже</td>
            <td width="220">
                <input type="text" name="form_data[managment1][<?php echo ($key+1); ?>][name_in_padeg]" value="<?php echo $contact['name_in_padeg']; ?>">
                <div class="note_div">
                   <div class="note">В родительном падеже.</div>
                </div>
            </td>
           
            <td colspan="2">
            </td>
    </tr>
    </table>
</div>
