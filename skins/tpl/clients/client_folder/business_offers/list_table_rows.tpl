<tr>
     <td class="flank_cell">&nbsp;</td>
     <td class="center without_padding"><?php  echo $date; ?></td>
     <td class="center without_padding"><?php  echo $order_num; ?></td>
     <td class="left"><?php  //echo $com_pred_data['description']; ?></td>
     <td class="left"><?php  //echo $com_pred_data['manager']; ?></td>
     <td class="without_padding_all">
         <table class="options_tbl">
             <tr>
                 <td><a href="?<?php  echo addOrReplaceGetOnURL('','show_kp_in_blank&show_kp').'&'.(isset($_GET['show_kp_in_blank'])? 'show_kp':'show_kp_in_blank').'='.$row['id']; ?>">посмотреть</a></td>
                 <td><a href='/os/modules/clients/client_folder/business_offers/to_print.php?data=<?php  echo 'new{@}'.$row['id'].'-'.$client_id.'-'.$user_id; ?>' target="_blank">напечатать</a></td>
                 <td><a href='#' onclick="kpManager.sendKpByMail(<?php  echo $row['id']; ?>);">письмо</a></td>
             </tr>
         </table>
  
       <!--<a href='?page=clients&razdel=show_client_folder&sub_razdel=com_offer&client_id=<?php //echo $client_id; ?>&preview=<?php //echo urlencode($file); ?>'>открыть</a> /-->
       <!--<a href='<?php  //echo $dir_name."/".$file; ?>'>скачать</a> /-->
        <!--<a href='#' onclick='rename_file("<?php  //echo $file; ?>");return false;'>переименовать</a> / /-->
   </td>
   <td class="<?php  echo $comment_style; ?>">
       <div managed="text" action="change_comment" bd_row_id="<?php echo $row['id']; ?>" bd_field="comment" when_done="set_color">
           <?php  echo $comment; ?>
       </div>
   </td>
   <td class="left">
      <?php  echo $send_time; ?>
   </td>
   <td class="center italic grey">
     <a href="?page=clients&section=client_folder&subsection=business_offers&client_id=<?php  echo $client_id; ?>&delete_com_offer=<?php  echo urlencode($row['id']); ?>" onclick='if(confirm(" Внимание! КП будет удалено!\r\n Вы действительно хотите удалить КП?")){ return true;} else{ return false;}'>DEL</a>
   </td>
   <td class="flank_cell">&nbsp;</td>
</tr>