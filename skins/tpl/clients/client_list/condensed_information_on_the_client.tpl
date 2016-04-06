<div class="client_details_field new_tbl ">
<table id="details_tbl" class="details_tbl">
<tr>
        <td class="company">
            <?php 
                if($_GET['page'] == 'cabinet'){
                    echo $back_without_client;
                }
            ?>
            <div class="container">
                <a href="?page=clients&section=client_folder&subsection=client_card_table&client_id=<?php echo $_GET['client_id']; ?>">  
                        <?php echo $company_name; ?></a>
            </div>
        </td>
       <td class="cap" style="width:70px;">
            Контакт:
        </td>
        <td class="name">
            <div class="container"> <?=$contact_face['name']?></div>
        </td> 
        <td class="empty">&nbsp;
             
        </td>
        <td class="cap">
            Тел.:
        </td>
        <td class="phone">
            <div class="container">
            <?php
                echo $contact_face['phone'];
            ?>
            </div>
        </td>
        <td class="cap">
                    E-mail :
        </td>
        <td class="email">
            <div class="container">
            <?php
                echo $contact_face['email'];
            ?>
            </div>
        </td>
    </tr>
</table>
</div>
