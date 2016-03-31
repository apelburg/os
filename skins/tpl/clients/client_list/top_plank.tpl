<div class="options_bar noselect">
   <div class="block">
      <div class="sub_block title"><img style="margin:2px 3px -2px 0px;"src="skins/images/img_design/star_icon.png" />Рейтинг</div>
      <div class="sub_block"><?php echo $rating_bar; ?></div>  
   </div>        
        
   <div class="block">
      <div class="sub_block title">Сортировать по:</div>
      <div class="sub_block">
         <table class="toggle_bar"><tr>
         <td class="<?php echo $by_alphabet_class; ?>"><a href="?<?php echo addOrReplaceGetOnURL('sotring=by_alphabet','num_page'); ?>">алфавиту</a></td>
         <td class="<?php echo $by_creating_date_class; ?>"><a href="?<?php echo addOrReplaceGetOnURL('sotring=by_creating_date','num_page'); ?>">созданию</a></td>
         <td class="<?php echo $by_rt_update_class; ?>"> <a href="?<?php echo addOrReplaceGetOnURL('sotring=by_rt_update','num_page'); ?>">изменению</a></td></tr></table>
      </div>
   </div>  
   
    <div class="block">
      <div class="sub_block title">Менеджер:</div>
      <div class="sub_block">
         <a href="#" onclick="return dropDownManagerList.generate(this);">ссылка</a>
      </div>
   </div>    
        
   <div class="block" style="float:right">
      <div class="sub_block">
        <table class="toggle_bar"><tr><td><a href="?<?php echo addOrReplaceGetOnURL('','filter_by_letter&num_page&filter_by_rating&sotring'); ?>" style="">сбросить все фильтры</a></td></tr></table></div> 
   </div> 
   <div class="clear_div"></div>
        
        
</div>
<div class="options_bar noselect">
    <table class="alphabet_plank">
       <tr>
           <td class="left" style="width:90px;">
            
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