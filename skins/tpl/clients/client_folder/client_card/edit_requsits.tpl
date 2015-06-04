<style type="text/css">
  .client_form_table{border-collapse: collapse;
  margin: auto;
  width: 90%;
  font-family: Verdana, Arial, Helvetica, sans-serif;
  font-size: 12px;
}  
input, textarea{
  font-size: 12px;
    color: #444;
  height: 1.694em;
  text-indent: .8em;
  border: 1px solid #efefef;
  text-indent: .8em;
  border: 1px solid #efefef;
}
.client_form_table td input, .client_form_table td textarea , .client_form_table td select{
   margin-left:-20px;
}
.client_form_table td select{
   margin-left:-20px;
   float:left; width:90%;font-weight:bold; font-size:14px; font-family:Arial, Helvetica, sans-serif;
}
input:hover,textarea:hover,textarea:focus, input:focus{
  outline: none;  
  box-shadow: rgba(0, 0, 0, 0.2) 0px 2px 6px 0px, rgba(0, 0, 0, 0.0470588) 0px 2px 3px 0px;
}
.client_form_table .note_div .note {
  position: absolute;
  font-size: 9px;
  width: 500px;
  color: #777;
}
.client_form_table{
   border-collapse:collapse;
   margin:auto;
   width:90%;
}
.client_form_table td{
   vertical-align:top;
   border:solid #CCCCCC 0px;
   padding-left:10px;
   text-align:left;
   padding:0 0 0 2%;
   padding-top:3px;
}

.client_form_table .note_div{
   position:relative;
   height:24px;
   margin-top: 28px
}
.client_form_table .note_div .note{
   position:absolute;
   font-size:9px;
   width:500px;
   color:#777;
}

.client_form_table input[type="text"]{
   width:100%;
}
.client_form_table textarea{
    height: 70px;
  margin: 0px 0px 0px -20px;
  width: 100%;
  padding: 0;
  font-size: 12px;
   width:100%;
}
.cont_faces_delete_btn{
  text-align: center;
  padding: 5px 10px;

}
.div_between_form_rows {
  border-top: 1px dashed #BBBBBB;
  margin: 22px 14px 32px 14px; 
  margin: 14px;
}
#add_the_new_man{ padding: 5px; color: #7f7e7c;/*float: left;*/ cursor: default;}
#add_the_new_man:hover{ background: #f3f3f3}
.new_person_type_req{cursor:pointer;width: 16px; height:16px; float:left;  font-weight:bold; color:white; text-align:center; padding:0; margin:2px 0 0 15px; background:#C33}



</style>

<form action="" id="requisits_edit_form" onsubmit="return sendform_1()" name="form" method="POST">
<input type="hidden" name="ajax_standart_window" value="update_requisites">
<table class="client_form_table" style="margin-top:15px;">
  <tr>
      <td></td>
      <td colspan="5"><div style="text-align:left;font-weight:bold; height:15px;">Компания</div></td>
    </tr>
  <tr>
    <td width="10%"></td>
    <td width="23%">
       <input id="form_data_company" type="text" name="company" value='<?php echo $requesit['company']; ?>'>
       <input id="" type="hidden" name="client_id" value="<?php echo $client_id; ?>">
       <input type="hidden" name="requesit_id" value="<?php echo $requesit['id']; ?>">
       <input type="hidden" name="form_data[phone2]" value="">
       <input type="hidden" name="form_data[phone1]" value="">
    </td>
    <td rowspan="2" width="10%">Юр.адрес</td>
    <td rowspan="2" width="23%">
      <textarea name="form_data[legal_address]" id="legalqqq" style="height:70px"><?php echo $requesit['legal_address']; ?></textarea>
    </td>
    <td rowspan="2" width="10%"> Почт.адрес<br>
        <span onclick="document.getElementById('postal').innerHTML=document.getElementById('legalqqq').innerHTML;document.getElementById('postal').value=document.getElementById('legalqqq').value;" style="float:left; font-size:9px; background: #75B775; color:white; cursor:pointer; text-align:center; margin:3px 0 0 0; line-height:10px; padding:2px; border:1px solid white;">Скопировать юр. адрес</span></td>
    <td rowspan="2" width="23%">
       <textarea name="form_data[postal_address]" id="postal" style="height:70px"><?php echo $requesit['postal_address']; ?></textarea>
    </td>
  </tr>
  <tr>
    <td width="10%">Юр.лицо<br>
    <span onclick="document.getElementById('comp_full_name').innerHTML='Общество с ограниченной ответственностью «»'" style="float:left; font-size:9px; background: #75B775; color:white; cursor:pointer; text-align:center; margin:3px 0 0 3px; line-height:10px; padding:2px; border:1px solid white;">ООО</span>
    <span onclick="document.getElementById('comp_full_name').innerHTML='Закрытое акционерное общество «»'" style="float:left; font-size:9px; background: #75B775; color:white; cursor:pointer; text-align:center; margin:3px 0 0 3px; line-height:10px; padding:2px; border:1px solid white;">ЗАО</span>
    </td>
    <td width="23%">
       <textarea name="form_data[comp_full_name]" id="comp_full_name" style="height:100%"><?php echo $requesit['comp_full_name']; ?></textarea>
    </td>
  </tr>
</table>
<!-- разделитель --><div class="div_between_form_rows"></div><!-- // разделитель -->


<div id="chief_fields_div">
<?php 
Client::edit_requsits_show_person_all(Client::edit_requsits_show_person($requesit['id']),$client_id);?>
</div>
<!-- разделитель --><div class="div_between_form_rows"></div><!-- // разделитель -->

    <div class="cont_faces_delete_btn">
        <span id="add_the_new_man" onclick="return add_new_management_element('chief_fields_div');">+ добавить ещё сотрудника контрагента</span>
    </div>

<!-- разделитель --><div class="div_between_form_rows"></div><!-- // разделитель -->

<!-- БАНКОВСКИЕ РЕКВИЗИТЫ -->
    <table class="client_form_table">
        <tr>
            <td></td>
            <td colspan="5"><div style="text-align:left;font-weight:bold; height:15px;">Банковские реквизиты</div></td>
        </tr>
        <tr>
            <td width="10%">Банк</td>
            <td width="23%">
                <textarea name="form_data[bank]"></textarea>
            </td>
            <td width="10%">Адрес</td>
            <td width="23%">
                <textarea name="form_data[bank_address]"></textarea>
            </td>
            <td width="10%">ОКПО</td>
            <td width="23%">
                <input type="text" name="form_data[okpo]" value="">
            </td>
        </tr>
        <tr>
            <td>ИНН</td>
            <td>
                <input type="text" name="form_data[inn]" value="">
            </td>
            <td>КПП</td>
            <td>
               <input type="text" name="form_data[kpp]" value="">
            </td>
            <td>ОГРН</td>
            <td>
               <input type="text" name="form_data[ogrn]" value="">
            </td>
        </tr>
        <tr>
            <td>Расчетн. счет</td>
            <td>
                <input type="text" name="form_data[r_account]" value="">
            </td>
            <td>Кор.счет</td>
            <td>
                <input type="text" name="form_data[cor_account]" value="">
            </td>
            <td>БИК</td>
            <td>
               <input type="text" name="form_data[bik]" value="">
            </td>
    </tr>
    </table>

<!-- разделитель --><div class="div_between_form_rows"></div><!-- // разделитель -->

<!-- ДОПОЛНИТЕЛЬНАЯ ИНФОРМАЦИЯ -->
    <table class="client_form_table">
        <tr>
            <td width="8%"></td>
            <td colspan="6">
                <div style="text-align:left;font-weight:bold; height:15px;">Дополнительная информация</div>
            </td>
        </tr>
        <tr>
            <td width="10%"></td>
            <td width="23%">
                <textarea type="text" name="form_data[dop_info]"></textarea>
            </td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    </table>
</form>