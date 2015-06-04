<tr>
     <td class="flank_cell">&nbsp;</td>
     <td class="center without_padding"><?php  echo $date; ?></td>
     <td class="center without_padding"><?php  echo $order_num; ?></td>
     <td class="left"><?php  echo $com_pred_data['description']; ?></td>
     <td class="left"><?php  echo $com_pred_data['manager']; ?></td>
     <td class="without_padding_all">
         <table class="options_tbl">
             <tr>
                 <td><a href="?<?php  echo $_SERVER['QUERY_STRING'].'&show_old_kp='.$client_id.'/'.$file; ?>">посмотреть</a></td>
                 <td><a href='/os/modules/clients/client_folder/business_offers/to_print.php?data=<?php  echo 'old{@}'.$client_id.'/'.$file; ?>' target="_blank">напечатать</a></td>
                <!-- onclick="kp_to_print('old','<?php  echo $client_id.'/'.$file; ?>');"-->
                 <td><a href='#' onclick="alert('старая версия КП не имеет опции отправки по почте');">письмо</a></td>
             </tr>
         </table>
  
       <!--<a href='?page=clients&razdel=show_client_folder&sub_razdel=com_offer&client_id=<?php  echo $client_id; ?>&preview=<?php  echo urlencode($file); ?>'>открыть</a> /-->
       <!--<a href='<?php  echo $dir_name."/".$file; ?>'>скачать</a> /-->
        <!--<a href='#' onclick='rename_file("<?php  echo $file; ?>");return false;'>переименовать</a> / /-->
   </td>
   <td class="<?php  echo $comment_style; ?>">
       <div managed="text" file_name="<?php echo $file; ?>" file_exicution="change_kp_comment_old_version" when_done="set_color">
           <?php  echo $comment; ?>
       </div>
        <!--<a href='#' onclick='change_file_comment("<?php  echo $file; ?>");return false;' style='text-decoration:none;' title="добавить комментарий">+</a>&nbsp;
        <a href='#' onclick='delete_file_comment("<?php  echo $file; ?>");return false;' style='text-decoration:none;' title="удалить комментарий">-</a>-->
        
   </td>
   <td class="left grey">
      ----
   </td>
   <td class="center italic grey">
       <a href='?page=clients&section=client_folder&subsection=business_offers&client_id=<?php  echo $client_id; ?>&delete_com_offer=<?php  echo urlencode($file); ?>&id=<?php  echo $com_pred_data["id"]; ?>&old_version=true' onclick='if(confirm(" Внимание! КП будет удалено!\r\n Вы действительно хотите удалить КП?")){ return true;} else{ return false;}'>DEL</a>
   </td>
   <td class="flank_cell">&nbsp;</td>
</tr>