<!-- begin skins/tpl/clients/client_details_field_general.tpl -->         
<div class="client_subsections">
   <table class="subsections_tbl">
        <tr>
            <td class="reqiests <?php if($subsection=='reqiests') echo 'on'; ?>">
                 <a href="?page=clients&section=client_folder&subsection=reqiests&client_id=<?php  echo $client_id; ?>" target="_blank">Входящие запросы</a>
            </td>
            <td class="calculate_table <?php if($subsection=='calculate_table') echo 'on'; ?>">
                <a href="?page=clients&section=client_folder&subsection=calculate_table&client_id=<?php  echo $client_id; ?>" target="_blank">Расчетная таблица</a>
            </td>
            <td class="orders <?php if($subsection=='orders') echo 'on'; ?>">
                <a href="?page=clients&section=client_folder&subsection=orders&client_id=<?php  echo $client_id; ?>" target="_blank">Заказы</a>
            </td>
            <td class="business_offers <?php if($subsection=='business_offers') echo 'on'; ?>">
                <a href="?page=clients&section=client_folder&subsection=business_offers&client_id=<?php  echo $client_id; ?>" target="_blank">КП</a>
            </td>
            <td class="documents <?php if($subsection=='documents') echo 'on'; ?>">
                <a href="?page=clients&section=client_folder&subsection=documents&client_id=<?php  echo $client_id; ?>" target="_blank">Документы</a>
            </td>
            <td class="documents <?php if($subsection=='client_card_table') echo 'on'; ?>">
                <a href="?page=clients&section=client_folder&subsection=client_card_table&client_id=<?php  echo $client_id; ?>" target="_blank">Карточка клиента</a>
            </td>
            <td class="empty">&nbsp;
                
            </td>
            <td class="history">
                <a href="" target="_blank">История</a>
            </td>
        </tr>
    </table>
</div>     
<!-- end skins/tpl/clients/client_details_field_general.tpl -->
 