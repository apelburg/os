<div class="options_bar noselect">
   <div class="block">
      <div class="sub_block title">Контрагенты:</div>
      <div class="sub_block">
         <table class="toggle_bar"><tr>
          <td class="<?php echo ($section == 'suppliers_list')?'active':''; ?>" width="80"><a href="?page=suppliers&section=suppliers_list">Имя</a></td>
         <td class="<?php echo ($section == 'profiles_list')?'active':''; ?>" width="80"><a href="?page=suppliers&section=profiles_list">Профиль</a></td>
         </tr></table>
      </div>
   </div> 
   <div class="block">
      <div class="sub_block">
        <table class="toggle_bar"><tr><td><a href="#" onclick="return get_checked_ids_and_make_request(this);" style="">показать выбранные</a></td></tr></table></div>
   </div>
   <div class="block">
      <div class="sub_block">
        <table class="toggle_bar" width="120"><tr><td><a href="?<?php echo addOrReplaceGetOnURL('','filter_by_profies'); ?>" style="">сбросить</a></td></tr></table></div> 
   </div>
   <div class="clear_div"></div>    
</div>
<!--
<div class="options_bar align_center">
    <?php //echo $page_navigation; ?>
</div>
-->