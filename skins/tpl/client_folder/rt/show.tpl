<!-- <?php echo __FILE__; ?> -- START-->
<script type="text/javascript">// возвращает размер
$(document).on('click', '.getSizesBtn', function(event) {
	event.preventDefault();
	var pos_id = $(this).attr('pos_id');
    var obj = $(this);
	$.ajax({
        type: 'POST',
        url: '',
        dataType: 'html',
        data: 'getSizesForArticle=' + 1 +'&pos_id=' + pos_id
      })
      .done(function(response) {
	    // alert(response);
		var json_obj = JSON.parse(response);
		if(json_obj.error){
		   if(json_obj.error == 'multi') obj.replaceWith('<br>в позиции несколько вариантов расчетов - зайдите в артикул');
		   if(json_obj.error == 'nothingFind') obj.replaceWith('<br>ошибка получения информации');
		}
		else obj.replaceWith('<br>'+json_obj.sizes.join('<br>'));
      })
});
// блокирование части элементов на странице
$( document ).ready(function() {
  $('#blanket').height($('#rt_tbl_body').height()+250);
});  
</script>
<link href="<?php  echo HOST; ?>/skins/css/rt_position.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/statusTooltip.js"></script>
<!-- сервис центр -->
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.serviceCenter.js"></script>
<link href="<?php  echo HOST; ?>/skins/css/serviceCenter.css" rel="stylesheet" type="text/css">

<!-- транслитерация-->
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.liTranslit.js"></script>
<!-- формы -->
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/forms__js.js"></script>
<!-- выбор услуги в диалоговом окне -->
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/attach_dop_serv.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/calendar_consturctor.js"></script>


<script type="text/javascript" src="<?=HOST;?>/libs/js/drag_and_drop.js"></script>

<link href="<?php  echo HOST; ?>/skins/css/rt.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/skins/css/checkboxes.css" rel="stylesheet" type="text/css">


<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/scrolledTableSizeFixing.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/rtCalculatorClass.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/printCalculatorClass.js"></script>
<script type="text/javascript" src="./libs/js/up_window_consructor.js"></script>
<div class="scrolled_tbl_container"> 
<?php echo $rt; ?>
</div>
<?php if($block_page_elements){ ?>
<div style="position:absolute;top:37px;left:50px;width:140px;height:35px;border:#ffff00 solid 0px" onclick="noticeQueryBlocked();"></div>
<div style="position:absolute;top:110px;left:0px;right:0px;height:95px;border:#ffff00 solid 0px" onclick="noticeQueryBlocked();"></div>
<?php } ?>
<!-- <?php echo __FILE__; ?> -- END-->