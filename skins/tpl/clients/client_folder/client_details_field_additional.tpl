<!-- begin skins/tpl/clients/client_details_field_additional.tpl -->         
<div class="client_details_field">
    <div class="open_close_btn_container">
        <div class="open_close_btn open" onclick="open_close_overflow_container(this,'details_field_container','details_tbl','scroll_container',23);">
            &nbsp;
        </div>
    </div>
    <div id="details_field_container" class="details_field_container">
        <table id="details_tbl" class="details_tbl">
            <tr>
                <td class="company">
                    <div class="container"><a href="?page=clients&section=client_folder&subsection=client_card_table&client_id=<?php  echo $client_id; ?>" target="_blank"><?php echo $client_name; ?></a></div>
                </td>
                <td class="cap" style="width:70px;">
                    Контакт:
                </td>
                <td class="name">
                    <div class="container"> <?php echo $main_cont_face_data['name']; ?></div>
                </td>
                <td class="empty">&nbsp;
                     
                </td>
                <td class="cap">
                    Тел.:
                </td>
                <td class="phone">
                    <div class="container"> <?php echo $client_data_arr['phone']; ?></div>
                </td>
                <td class="cap">
                    Моб.:
                </td>
                <td class="mobile">
                    <div class="container"> <?php echo $main_cont_face_data['mobile']; ?></div>
                </td>
                <td class="cap">
                    E-mail.:
                </td>
                <td class="email">
                    <div class="container"> <?php echo (!empty($main_cont_face_data['email']))? $main_cont_face_data['email']:$client_data_arr['email']; ?></div>
                </td>
            </tr>
            <tr>
                <td class="name">
                    <div class="container"><span class="cap">куратор:</span> <?php echo $manager_nickname; ?><span class="cap">рег.: <?php echo $client_reg_date; ?></span></div>
                </td>
                <td class="cap">
                    Должность:
                </td>
                <td class="name">
                    <div class="container"> <?php echo $main_cont_face_data['position']; ?></div>
                </td>
                <td class="">&nbsp;
                     
                </td>
                <td class="cap">
                    ISQ.:
                </td>
                <td class="name">
                    <div class="container"> </div>
                </td>
                <td class="cap">
                    Тел.:
                </td>
                <td class="name">
                    <div class="container"> <?php echo $main_cont_face_data['phone']; ?></div>
                </td>
                <td class="cap">
                    www.:
                </td>
                <td class="name">
                    <div class="container"> <?php echo $client_data_arr['web_site']; ?></div>
                </td>
            </tr>
        </table>
    </div>  
</div>   
<!-- end skins/tpl/clients/client_details_field_additional.tpl -->
 