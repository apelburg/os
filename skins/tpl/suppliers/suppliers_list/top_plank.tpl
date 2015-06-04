<div class="options_bar noselect">
   <div class="block">
      <div class="sub_block title">Контрагенты:</div>
      <div class="sub_block">
         <table class="toggle_bar"><tr>
         <td class="<?php echo ($section == 'suppliers_list')?'active':''; ?>" width="80"><a href="?page=suppliers&section=suppliers_list">Имя</a></td>
         <td class="<?php echo ($section == 'profiles_list')?'active':''; ?>" width="80"><a href="?<?php echo addOrReplaceGetOnURL('section=profiles_list','search'); ?> ">Профиль</a></td>
         </tr></table>
      </div>
   </div> 
   <div class="block">
      <div class="sub_block title">Город:</div>
      <div class="sub_block">
         <table class="toggle_bar"><tr>
         <td class="<?php echo ($filter_by_cities == 'spb')?'active':''; ?>"><a href="?<?php echo addOrReplaceGetOnURL('filter_by_cities=spb',''); ?>">СПб</a></td>
         <td class="<?php echo ($filter_by_cities == 'msk')?'active':''; ?>"><a href="?<?php echo addOrReplaceGetOnURL('filter_by_cities=msk',''); ?>">МСК</a></td>
         <td class="<?php echo (!$filter_by_cities)?'active':''; ?>"><a href="?<?php echo addOrReplaceGetOnURL('','filter_by_cities'); ?>">Все</a></td>
         </tr></table>
      </div>
   </div>   
   
   <div class="block">
      <div class="sub_block title"><img style="margin:2px 3px -2px 0px;"src="skins/images/img_design/star_icon.png" />Рейтинг</div>
      <div class="sub_block"><?php echo $rating_bar; ?></div>  
   </div>        
        
   <div class="block">
      <div class="sub_block title">Сортировать по:</div>
      <div class="sub_block">
         <table class="toggle_bar"><tr>
         <td class="<?php echo $by_alphabet_class; ?>"><a href="?<?php echo addOrReplaceGetOnURL('sotring=by_alphabet',''); ?>">алфавиту</a></td>
         <td class="<?php echo $by_creating_date_class; ?>"><a href="?<?php echo addOrReplaceGetOnURL('sotring=by_creating_date',''); ?>">созданию</a></td>
         </tr></table>
      </div>
   </div>       
  <div class="block" style="float:right">
      <div class="sub_block">
        <table class="toggle_bar"><tr><td><a href="?<?php echo addOrReplaceGetOnURL('','filter_by_letter&filter_by_rating&sotring&filter_by_cities'); ?>" style="">сбросить все фильтры</a></td></tr></table></div> 
   </div>
   <div class="clear_div"></div>    
</div>
<div class="options_bar noselect">
    <table class="alphabet_plank">
       <tr>
           <td class="left" style="width:40px;">
            
            <?php echo implode('',$alphabet_plank); ?>
           
           </td>
        </tr>
    </table>
</div>
<!--
<div class="options_bar align_center">
    <?php //echo $page_navigation; ?>
</div>
-->