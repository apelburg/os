<!-- <?php echo __FILE__; ?> -- START-->
<script type="text/javascript">
$(document).on('keyup', '.query_theme', function(event) {
    // первым параметром перелаём название функции отвечающей за отправку запроса AJAX
    // вторым параметром передаём объект к которому добавляется класс saved (класс подсветки)
    timing_save_input('save_status_name',$(this));
});


function fff(element,defaultBgString){
   if(element.value == defaultBgString) element.value = '';
   //timing_save_input('save_status_name',$(element));  
}

function save_status_name(obj){// на вход принимает object input
    var query_num = obj.attr('query_num');
	//alert(query_num);
    $.post('', {
        AJAX:'edit_query_theme',
        query_num:query_num,
        theme:obj.val()
    }, function(data, textStatus, xhr) {
         console.log(data);
        // обрабатываем положительный ответ из PHP
        if(data['response']=="OK"){
            // php возвращает json в виде {"response":"OK"}
            // если ответ OK - снимаем класс saved
            obj.removeClass('saved');
        }else{
            console.log('Данные не были сохранены.');
        }
    },'json');
}


// функция тайминга
function timing_save_input(fancName,obj){
    //если сохраниться разрешено, т.е. уже 2 сек. запросы со страницы не отправлялись
    if(!obj.hasClass('saved')){
        window[fancName](obj);
        obj.addClass('saved');                  
    }else{
        // стоит запрет, проверяем очередь по сейву данной функции
        if(obj.hasClass(fancName)){ //стоит в очереди на сохранение
            // стоит очередь, значит мимо... всё и так сохранится
        }else{
            // не стоит в очереди, значит ставим
            obj.addClass(fancName);
            // вызываем эту же функцию через n времени всех очередей
            var time = 2000;
            $('.'+fancName).each(function(index, el) {
                console.log($(this).html());
                
                setTimeout(function(){timing_save_input(fancName,$('.'+fancName).eq(index));// обнуляем очередь
        	if(obj.hasClass(fancName)){obj.removeClass(fancName);}}, time); 
            });         
        }       
    }
}
</script>   
<style type="text/css">
#order_art_edit{font-family: Gotham, "Helvetica Neue", Helvetica, Arial, sans-serif; font-size: 12px}


#info_string_on_query{ background-color: #5a5c61; color: #fff;}

#info_string_on_query ul{ padding: 0; list-style: none; margin: 0}
#info_string_on_query ul li{ display: inline-block;padding: 5px 10px;}
#back_to_string_of_claim a{width: 32px;
  height: 24px;
  cursor: pointer;
  background: url('../../skins/images/img_design/back_art.png') no-repeat;
  /* float: left; */
  position: absolute;
  z-index: 3;
  /* top: 61px; */
  margin-top: -5px;
  background-position-x: 3px;}
</style>

<div class="cabinet_top_menu">
  <ul class="central_menu">
    <li <?php if($manager_id == '24' ){echo 'class="selected"';}?> >
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_wait_the_process&client_id=<?=$_GET['client_id'];?>">
        <div class="border">Ожидают распределения</div>
      </a>
    </li>
    <li <?php if($query_status == 'not_process' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=no_worcked_men&client_id=<?=$_GET['client_id'];?>">
        <div class="border">Не обработанные МЕН</div>
      </a>
    </li>
    <li <?php if($query_status == 'taken_into_operation' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_taken_into_operation&client_id=<?=$_GET['client_id'];?>">
        <div class="border">На рассмотрении</div>
      </a>
    </li>
    <li <?php if($query_status == 'in_work' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_worcked_men&client_id=<?=$_GET['client_id'];?>">
        <div class="border">В работе Sales</div>
      </a>
    </li>
    <li <?php if($query_status == 'history' ){echo 'class="selected"';}?>>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_history&client_id=<?=$_GET['client_id'];?>">
        <div class="border">История </div>
      </a>
    </li>
    <li>
      <a href="http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=cabinet&section=requests&subsection=query_all&client_id=888">
        <div class="border">Все</div>
      </a>
    </li>
  </ul>
</div>

<div id="order_art_edit">
<div id="info_string_on_query">
  <ul>
			<li id="back_to_string_of_claim"></li>
			<li id="claim_number">Запрос №<?php  echo $query_num; ?></li>
			<li id="claim_date"><span>от <?php echo $create_time; ?></span></li>
			<!--<li id="button_standart_001" title="кнопка смены тендр/стандарт"><span>стандарт</span></li>	-->
			<li id="query_theme_block"><span>Тема:</span> <?php echo $theme_block; ?></li>
      <li style="float:right;height: 100%;width: 40px;"><span data-rt_list_query_num="<?php  echo $query_num; ?>" class="icon_comment_show white <?php echo Comments_for_query_class::check_the_empty_query_coment_Database($query_num); ?> "></span></li>
      <li style="float:right;"><?php  echo $cont_face; ?></li>
      <li style=""><div class="client_faces_select2" sourse="rt" query_num="'.$query_num.'" client_id="'.$client_id.'" onclick="openCloseMenu(event,'calcLevelSwitcher');">Калькулятор: <?php  echo $calculator_level_ru; ?></div>
      <input type="hidden" id="calcLevelStorage" value="<?php  echo $calculator_level; ?>"></li>
		</ul>
	</div>
<!-- 	<div id="options_bar" style="background-color:#92b73e;">
		<ul>
			<!--<li>Позиции № 1</li>
			<li>В работе select</li>
			<li>Каталожные</li>
            <li>Не принятые</li>
            <li>2 п</li>-->
            <!-- <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=business_offers&query_num=<?php  echo $query_num; ?>&client_id=<?php  echo $client_id; ?>" style="color:#FFFFFF;">Коммерческие предложения</a></li>
            <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=agreement&client_id=<?php  echo $client_id; ?>" style="color:#FFFFFF;">Договоры</a></li>
            <li><a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=oferta&client_id=<?php  echo $client_id; ?>" style="color:#FFFFFF;">Оферты</a></li> -->
            
		<!--</ul>
	</div> -->
 </div>    
<!-- <?php echo __FILE__; ?> -- END-->
 