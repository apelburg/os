<table style="float:left; width:100%; margin:20px 0 20px 0;padding:0 0 20px 0;border-bottom:2px dashed #CECECE;">
    <tr>
        <td colspan="2">
            <div style="border-bottom:1px solid #cecece;font-size:18px;"><?php echo (trim($requesit['company'])!='')?$requesit['company']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
        <td colspan="2">
            <div style="border-bottom:1px solid #cecece;font-size:18px;">Банковские реквизиты</div>
        </td>							
    </tr>
    <tr>
        <td width="15%">Полное наименование</td>
        <td width="35%">
            <div class="info_white"><?php echo (trim($requesit['comp_full_name'])!='')?$requesit['comp_full_name']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 								
        <td width="10%">БАНК</td>
        <td width="40%">
            <div class="info_white"><?php echo (trim($requesit['bank'])!='')?$requesit['bank']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?> <?php echo (trim($requesit['bank_address'])!='')?$requesit['bank_address']:''; ?></div>
        </td>
    </tr>
    <tr>
        <td>Инн</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['inn'])!='')?$requesit['inn']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 
        <td>Р/С</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['r_account'])!=0)?$requesit['r_account']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
    <tr>
        <td>КПП</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['kpp'])!=0)?$requesit['kpp']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 
        <td>корр/С</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['cor_account'])!='')?$requesit['cor_account']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
    <tr>
        <td colspan="2">
            <div style="border-bottom:1px solid #cecece;font-size:18px;">Адрес и телефон</div>
        </td>
        <td>БИК</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['bik'])!='')?$requesit['bik']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
    <tr>
        <td>Юридический адрес</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['legal_address'])!='')?$requesit['legal_address']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 
        <td>ОГРН</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['ogrn'])!='')?$requesit['ogrn']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
    <tr>
        <td>Фактический адрес<br> (почтовый)</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['postal_address'])!='')?$requesit['postal_address']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 
        <td>ОКПО</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['okpo'])!=0)?$requesit['okpo']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
    <tr>
        <td>Телефоны</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['phone1'])!='' && trim($requesit['phone2'])!='')?$requesit['phone1'].'  '.$requesit['phone2'] :'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td> 
        <td>Доп. инфо</td>
        <td>
            <div class="info_white"><?php echo (trim($requesit['okpo'])!=0)?$requesit['okpo']:'<span style="color:#D8D3D3">Информация отсутствует</span>'; ?></div>
        </td>
    </tr>
</table>
