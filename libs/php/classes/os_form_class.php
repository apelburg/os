<?php 
/*
в конце названий методов указан формат в котором выдаётся информация по окончании работы метода
Html, Array, String, Int
Если метод работает с базой сначала указывается обравиатура Database 
и уже потом Тип возвращаемых данных
PS было бы неплохо взять взять это за правило 
*/
    class Forms{
          // id пользователя
     private $user_id;
     private $user_access;
     // тип продукта с которым работает форма
     private $type_product;
          
          // сюда будем сохранять id html элементов формы, чтобы иметь понятие какие id мы использовать уже не можем
          // id в основной своей массе используются для label
          private $id_closed =array(); 
          
          // html код отвечающий за удаление записи, 
          // которую добаил менеджер для личного пользования
          private $span_del = '<span class="delete_user_val">X</span>';
                        
          public $form_type = array();
          function __construct(){
               $this->user_id = $_SESSION['access']['user_id'];
               // допуски пользователя под которым мог зайти Админ
               $this->user_access = $this->get_user_access_Database_Int($this->user_id);
               // реальные допуски
               $this->user_access_real = $_SESSION['access']['access'];
               ## данные POST
               if(isset($_POST['AJAX'])){
                    $this->_AJAX_($_POST['AJAX']);
               }
               ## данные GET --- НА ВРЕМЯ ОТЛАДКИ !!!
               if(isset($_GET['AJAX'])){
                    $this->_AJAX_($_GET['AJAX']);
               }               
          }
          
          //////////////////////////
          //   methods_AJAX  -- start
          //////////////////////////
               ########   вызов AJAX   ########
               private function _AJAX_($name){
                    $method_AJAX = $name.'_AJAX';
                    // если в этом классе существует искомый метод для AJAX - выполняем его и выходим
                    if(method_exists($this, $method_AJAX)){
                         $this->$method_AJAX();
                         exit;
                    }                        
               }
               
          /////////////////////////////////////////////////////////////////////////////////////
          //     -----  START  -----  ПРОВЕРЕНО !!!!!  -----  START  -----
          /////////////////////////////////////////////////////////////////////////////////////
               
               // запросчик формы после выбора типа продукции
               private function get_form_Html_AJAX(){
                    //////////////////////////
                    //   Для каталожной продукции
                    //////////////////////////
                    if(isset($_POST['type_product']) && $_POST['type_product'] == "cat"){
                         echo '{"response":"show_new_window","type":"cat","title":"Введите № артикула","html":"'.base64_encode($this->get_for_add_catalog_product()).'"}';  
                         exit;
                    }
                    //////////////////////////
                    //   Для некаталожной продукции
                    //////////////////////////
                    // запрашиваем из POST массива данные о типе продукта
                    $t_p = (isset($_POST['type_product']) && $_POST['type_product']!="")?$_POST['type_product']:'none';
                   
                    // запрос формы html
                    // echo '{"response":"show_new_window","html":"asdsad"}';
                    echo '{"response":"show_form_moderate_window","html":"'.base64_encode($this->get_product_form_Html($t_p)).'"}';
               }
               // проверяем наличие артикула на сайте, выводим его описание при нахождении
               private function check_exists_articul_AJAX(){
                    $html = '';
                    if(strlen($_POST['art']) < 4){
                         $html .= '<div class="inform_message red">Количество символов в артикуле должно быть не менее 4 (четырёх) символов.</div>';
                         echo '{"response":"OK","html":"'.base64_encode($html).'"}';
                         exit;
                    }
                    $html .= '<form>';
                    // делаем запрос в базу по артикулу
                    $art_arr = $this->search_articule_Database($_POST['art']);
                    
                    // получаем количесвто найденных совпадений
                    $count = count($art_arr);
                    switch ($count) {
                         case 1: // всё впорядке, мы нашли то, что искали
                              $html .= '<div class="inform_message">Найдено <strong>одно</strong> совпадение</div>';
                              $html .= '<table id="choose_one_of_several_articles">';
                              $html .= '<tr>';
                              $html .= '<th>п</th>';
                              $html .= '<th>Арт.</th>';
                              $html .= '<th>Название</th>';
                              $html .= '<th>Поставщик</th>';
                              $html .= '<th>Апл</th>';
                              $html .= '</tr>';
                              $n = 1;
                              $html .= '<tr data-art_id="'.$art_arr[0]['id'].'"  data-art_name="'.$art_arr[0]['name'].'" data-art="'.$art_arr[0]['art'].'" class="checked">';
                              $html .= '<td>'.$n++.'</td>';
                              $html .= '<td>'.$art_arr[0]['art'].'</td>';
                              $html .= '<td>'.$art_arr[0]['name'].'</td>';
                              $html .= '<td>'.identify_supplier_by_prefix($art_arr[0]['art']).'</td>';
                              $html .= '<td><a target="_blank" href="http://www.apelburg.ru/description/'.$art_arr[0]['id'].'/">на сайт</a></td>';
                              $html .= '</tr>';
                              // добавляем полное описание артикула
                              if(trim($art_arr[0]['description']) != ''){
                                   $html .= '<tr>';
                                   $html .= '<td colspan="5">';
                                   $html .= '<div>'.$art_arr[0]['description'].'</div>';
                                   $html .= '</td>';
                                   $html .= '</tr>';     
                              }
                              
                              $html .= '</table>';
                              
                              // добавляем скрытые поля
                              $html .= '<input type="hidden" name="AJAX" value="insert_in_database_new_catalog_position">';
                              $html .= '<input type="hidden" name="art_id" value="'.$art_arr[0]['id'].'">';
                              $html .= '<input type="hidden" name="art" value="'.$art_arr[0]['art'].'">';
                              $html .= '<input type="hidden" name="art_name" value="'.$art_arr[0]['name'].'">';
                              $html .= '</form>';
                              break;
                         case 0: // мы ненашли ничего
                              $html = '<div class="inform_message red">Такого артикула нет в базе. Попробуйте ввести другое значение.</div>';
                              break;
                         
                         default: // мы нашли более одного совпадения
                              $html .= '<div class="inform_message">Найдено <strong>'.$count.'</strong> совпадения(й). Пожалуйста уточните Ваш запрос.</div>';
                              $html .= '<table id="choose_one_of_several_articles">';
                              $html .= '<tr>';
                              $html .= '<th>п</th>';
                              $html .= '<th>Арт.</th>';
                              $html .= '<th>Название</th>';
                              $html .= '<th>Поставщик</th>';
                              $html .= '<th>Апл</th>';
                              $html .= '</tr>';
                              $n = 1;
                              foreach ($art_arr as $key => $articul) {
                                   $html .= '<tr data-art_id="'.$articul['id'].'" data-art_name="'.$articul['name'].'" data-art="'.$articul['art'].'" '.(($key==0)?'class="checked"':'').'>';
                                   $html .= '<td>'.$n++.'</td>';
                                   $html .= '<td>'.$articul['art'].'</td>';
                                   $html .= '<td>'.$articul['name'].'</td>';
                                   $html .= '<td>'.identify_supplier_by_prefix($articul['art']).'</td>';
                                   $html .= '<td><a target="_blank" href="http://www.apelburg.ru/description/'.$articul['id'].'/">на сайт</a></td>';
                                   $html .= '</tr>';
                              }
                              $html .= '</table>';
                              // добавляем скрытые поля
                              $html .= '<input type="hidden" name="AJAX" value="insert_in_database_new_catalog_position">';
                              $html .= '<input type="hidden" name="art_id" value="'.$art_arr[0]['id'].'">';
                              $html .= '<input type="hidden" name="art_name" value="'.$art_arr[0]['name'].'">';
                              $html .= '<input type="hidden" name="art" value="'.$art_arr[0]['art'].'">';
                              $html .= '</form>';
                              break;
                    }
                    
                    echo '{"response":"OK","html":"'.base64_encode($html).'"}';
               }
               // добавление каталожного товара в РТ
               private function insert_in_database_new_catalog_position_AJAX(){
                    global $mysqli; 
                    if(!isset($_POST['chosen_size'])){
                         //////////////////////////
                         //  запрашиваем размеры к выбранному артикулу
                         //////////////////////////
                         $query = "SELECT * FROM `".BASE_DOP_PARAMS_TBL."` where art_id = '".(int)$_POST['art_id']."'";
                         $size_arr = array();
                         $result = $mysqli->query($query) or die($mysqli->error);
                         if($result->num_rows > 0){
                              while($row = $result->fetch_assoc()){
                                   $size_arr[] = $row;
                              }
                         }
                         if(count($size_arr) > 1){ // если размеров более одного выводим новую форму с выбором размера
                              $html = '';
                              $html .= '<form>';
                              // выводим таблицу размеров
                              $html .= '<div class="inform_message">Выберите размер</div>';
                              $html .= '<table id="choose_the_size">';
                              $html .= '<tr><th colspan="2">'.$_POST['art_name'].'</th></tr>';
                              $html .= '<tr><th>Размер</th><th>Цена</th></tr>';
                              foreach ($size_arr as $key => $value) {
                                   $html .= '<tr>';
                                        $html .= '<td>';
                                             $html .= $value['size'];
                                        $html .= '</td>';
                                        $html .= '<td>';
                                             $html .= '<span>'.$value['price'].'</span> р.';
                                        $html .= '</td>';
                                   $html .= '</tr>';
                              }
                              $html .= '</table>';
                              $html .= '<input type="hidden" name="chosen_size" class="chosen_size" value="">';// размер
                              $html .= '<input type="hidden" name="price_out" class="price_out" value="">';// размер
                              // вывод скрытых полей переданных ранее
                              foreach ($_POST as $key => $value) {
                                   $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                              }
                              $html .= '</form>';
                              echo '{"response":"show_new_window" , "title":"Выберите размер","html":"'.base64_encode($html).'"}';
                              exit;
                         }else{
                              $this->price_out = $size_arr[0]['price'];
                         }
                    }else{
                         $this->price_out = $_POST['price_out'];
                    }
                    // echo '{"response":"none"}';
                    // echo $this->print_arr($this);
                    // echo $this->print_arr($_POST);
                    // exit;
                    //////////////////////////
                    //  осущевствляем проверку всех необходимых данных
                    //////////////////////////
                    if(isset($_GET['query_num'])){
                         $this->query_num = (int)$_GET['query_num'];
                    }else{
                         return 'не указан query_num';
                    }
                    //////////////////////////
                    //  Вставляем строку в main_rows
                    //////////////////////////
                    $this->sort_num = $this->get_sort_num();    
                    $query ="INSERT INTO `".RT_MAIN_ROWS."` SET
                         `query_num` = '".$this->query_num."',
                         `name` = '".trim($_POST['art_name'])."',
                         `date_create` = CURRENT_DATE(),
                         `art_id` = '".$_POST['art_id']."',
                         `art` = '".$_POST['art']."',
                         `type` = 'cat',
                         `sort` = '".$this->sort_num."'";                    
                   
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    $main_rows_id = $mysqli->insert_id;  
                    //////////////////////////
                    //  вставляем строку в dop_data
                    //////////////////////////
                     $query ="INSERT INTO `".RT_DOP_DATA."` SET
                         `row_id` = '".$main_rows_id."',                         
                         `price_out` = '".$this->price_out."',
                         `row_status` = 'green',
                         `glob_status` = 'в работе'";                    
                   
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    // $main_rows_id = $mysqli->insert_id; 
                    echo '{"response":"OK","function":"window_reload"}';
                    // echo $this->print_arr($_POST);
               }
               // выводит форму выбора типа товара
               private function to_chose_the_type_product_form_AJAX(){
                    // форма выбора типа продукта
                    echo '{"response":"show_new_window","html":"'.base64_encode($this->to_chose_the_type_product_form_Html()).'","title":"Выберите тип продукции"}';
               }
               //////////////////////////
               // удаление поля
               //////////////////////////
               private function delete_input_width_form_AJAX(){
                    global $mysqli;
                    $query = "DELETE FROM `".FORM_INPUTS."` WHERE `id`='".(int)$_POST['row_id']."';";
                    $result = $mysqli->query($query) or die($mysqli->error);
                    echo '{"response":"OK","function":"update_form"}';
               }
               //////////////////////////
               // заведение нового поля ввода в базу
               //////////////////////////
               private function greate_new_input_AJAX(){
                    //////////////////////////
                    //     проверка на невведённые данные (если проверка не пройдена - возвращается форма и запись не производится)
                    //////////////////////////
                    //echo  $this->print_arr($_POST);exit;
                         if($_POST['type'] == ""){
                              return $this->get_form_width_add_input_AJAX();
                         }
                         // if(trim($_POST['name_ru']) == ""){
                         //      return $this->get_form_width_add_input_AJAX();
                         // }
                         if(trim($_POST['name_en']) == ""){
                              return $this->get_form_width_add_input_AJAX();
                         }
                    //////////////////////////
                    //  запись поля в базу
                    //////////////////////////
                    global $mysqli;
                    $command = isset($_POST['update_form'])?'UPDATE':'INSERT INTO';
                    $where = isset($_POST['update_form'])?" WHERE `id` = '".$_POST['row_id']."'":"";
                    // $html = $this->print_arr($_POST);
                    $query = $command." `".FORM_INPUTS."` SET
                         `name_ru` = '".trim($_POST['name_ru'])."',
                         `name_en` = '".trim($_POST['name_en'])."',
                         `placeholder` = '".trim($_POST['placeholder'])."',
                         `parent_name` = '".trim($_POST['parent_name'])."',
                         `type` = '".trim($_POST['type'])."',
                         `author_id` = '".trim($_POST['author_id'])."',
                         `author_access` = '".trim($_POST['author_access'])."',
                         `type_product` = '".trim($_POST['type_product'])."'";  
                    if (isset($_POST['the_small_text_on'])) {
                         $query .= ",`note` = '<span style=\'font-size:".(int)$_POST['change_the_font_size']."px\'>".trim($_POST['note'])."</span>'";
                    }else{
                         $query .= ",`note` = '".trim($_POST['note'])."'";
                    }
                    if(isset($_POST['parent_id']) && trim($_POST['parent_id']) != ""){
                         $query .= ",`parent_id` = '".trim($_POST['parent_id'])."'";
                    }
                    if(isset($_POST['val']) && trim($_POST['val']) != ""){
                         $query .= ",`val` = '".trim($_POST['val'])."'";
                    }                  
                    if(isset($_POST['cancel_selection'])){
                         $query .= ",`cancel_selection` = '1'";
                    }else{
                         $query .= ",`cancel_selection` = '0'";
                    }            
                    if(isset($_POST['moderate'])){
                         $query .= ",`moderate` = '1'";
                    }else{
                         $query .= ",`moderate` = '0'";
                    }
                    if(isset($_POST['btn_add_var'])){
                         $query .= ",`btn_add_var` = '1'";
                    }else{
                         $query .= ",`btn_add_var` = '0'";
                    }                 
                    if(isset($_POST['btn_add_val'])){
                         $query .= ",`btn_add_val` = '1'";
                    }else{
                         $query .= ",`btn_add_val` = '0'";
                    }
                    $query .= $where;
                   
                    $result = $mysqli->query($query) or die($mysqli->error);
                    echo '{"response":"OK","html":"'.base64_encode($query).'","function":"update_form"}';
                    // echo '{"response":"show_new_window_2","html":"'.base64_encode($this->print_arr($_POST)).'","title":"Проверяем что проиходит"}'; 
               }
               //////////////////////////
               //  форма редактирования старого поля
               //////////////////////////
               private function edit_input_width_form_AJAX(){
                    $this->input = $this->get_child_listing_Database_Array((int)$_POST['row_id']);
                    if(isset($this->input[0])){
                         echo '{"response":"show_new_window_2","function":"update_form","html":"'.base64_encode($this->get_form_width_add_input_AJAX()).'","title":"Редактор поля"}'; 
                    }else{
                         echo '{"response":"php_message_alert","message":"поле не найдено"}';
                    }
               }
               //////////////////////////
               //  форма добавления нового поля
               //////////////////////////
               private function get_form_width_add_input_AJAX(){
                    $html = '';
                    $html .= '<div id="get_form_width_add_input">';
                         $html .= '<form>';
                         $html .='<div class="block">';
                         $html .= '<div class="head_texter">Перемещение по родителям</div>';
                         $html .= '<strong>ID родителя:</strong>(указать 0 в случае перемещения в корень)<br><input type="text" name="parent_id" value=""><br>';
                         unset($_POST['AJAX']);
                         foreach ($_POST as $key => $value) {
                              // if($key == "parent_name"){
                              //      $html .= '<input type="text" name="'.$key.'" value="'.$value.'" >';
                              // }else{
                              if($key == 'parent_name' || $key == 'parent_id'){
                                   $html .= '<strong>Имя родителя</strong>(указать имя формы в случае перемещения в корень)<br>';
                                   $html .= '<input type="text" name="'.$key.'" value="'.$value.'"><br>';
                              }else{
                                   $html .= '<input type="hidden" name="'.$key.'" value="'.$value.'">';
                              }
                                   
                              // }
                         }
                         $html .='</div >';
                         $html .='<div class="block">';
                              $html .= '<div class="head_texter">Общая информация</div>';
                              $html .= 'Название<br>';
                              $html .= '<input type="text" name="name_ru" id="cirillic_name_input" '.((isset($this->input[0]['name_ru']))?'value="'.$this->input[0]['name_ru'].'"':'').((isset($_POST['name_ru']))?'value="'.$_POST['name_ru'].'"':'').'><br>';
                              $html .= 'Пояснения к названию (мелкий шрифт)<br>';
                              $html .= '<input type="text" name="note"  value="'.(isset($this->input[0]['note'])?strip_tags($this->input[0]['note'],"<span>"):'').(isset($_POST['note'])?$_POST['note']:'').'">';
                              $html .= '<br>';
                              $html .= '<input type="checkbox" name="the_small_text_on" id="the_small_text_on">';
                              $html .= '<label for="the_small_text_on" id="the_small_text_on_label">Изменить размер текста в пояснении</label>';
                              $html .= '<br>';
                              $html .= '<select id="change_the_font_size" name="change_the_font_size">';
                                   $html .= '<option value="10">8</option>';
                                   $html .= '<option value="10">9</option>';
                                   $html .= '<option value="10" selected="selected">10</option>';
                                   $html .= '<option value="10">11</option>';
                                   $html .= '<option value="10">12 стандартно</option>';
                              $html .= '</select>';
                              
                              $html .= '<br>';
                              $html .= '<br>';
                              $html .= 'Название на англ.<br>';
                              // т.к. в большинстве случаев ключ нужен отличный от остальных - генерим такой
                              // исключение из правил зарезервированные системой названия
                              $name_engl = 'apl'.md5(time());
                              $html .= '<input type="text" name="name_en" id="eng_name_input" value="'.(isset($this->input[0]['name_en'])?$this->input[0]['name_en']:'').(isset($_POST['name_en'])?$_POST['name_en']:'').'"><span id="add_auto_key" data-key="'.$name_engl.'">Подставить ключ</span><br>';
                              // $html .= '<input type="text" name="name_en" id="eng_name_input" value="'..'"><br>';
                         $html .='</div>';
                         $html .='<div class="block">';
                              $html .= '<div class="head_texter">Выберите тип поля</div>';
                              $html .= '<select name="type" id="check_the_type_of_input">';
                                   $html .= '<option value=""></option>';//
                                   $html .= '<option value="checkbox" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="checkbox")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="checkbox")?'selected="selected"':'').'>Галка</option>';
                                   $html .= '<option value="radio" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="radio")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="radio")?'selected="selected"':'').'>Радио</option>';
                                   $html .= '<option value="text" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="text")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="text")?'selected="selected"':'').'>текстовое поле</option>';
                                   $html .= '<option value="textarea" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="textarea")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="textarea")?'selected="selected"':'').'>большое текстовое поле</option>';
                                   $html .= '<option value="big_header" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="big_header")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="big_header")?'selected="selected"':'').'>Заголовок большой</option>';                         
                                   $html .= '<option value="small_header" '.((isset($this->input[0]['type']) && $this->input[0]['type']=="small_header")?'selected="selected"':'').((isset($_POST['type']) && $_POST['type']=="small_header")?'selected="selected"':'').'>Заголовок малый</option>';
                              $html .= '</select><br>';
                         $html .='</div>';
                         $html .='<div class="block">';
                              $html .= '<div class="head_texter">Настройки для текстовых полей:</div>';
                              $html .= 'Подсказка внутри текстогого поля.<br>';
                              $html .= '<input type="text" name="placeholder" value="'.(isset($this->input[0]['placeholder'])?$this->input[0]['placeholder']:'').(isset($_POST['placeholder'])?$_POST['placeholder']:'').'"><br><br>';
                              $html .= 'Предвведённый текст <span style="font-size:11px;color:red;">(может быть использован только для текстовых полей)</span><br>'; 
                              $html .= '<input type="text" name="val" id="val_name_input" value="'.(isset($this->input[0]['val'])?$this->input[0]['val']:'').(isset($_POST['val'])?$_POST['val']:'').'"><br>';
                         $html .='</div>';
                         // $html .= 'Настройки для заголо';
                         $html .='<div class="block">';
                              $html .= '<div class="head_texter">Настройки заголовка:</div>';
                                   $html .= '<input type="checkbox" name="moderate" id="moderate_id" '.((isset($this->input[0]['moderate']) and $this->input[0]['moderate'] == 1)?'checked':'').(isset($_POST['moderate'])?'checked':'').'><label for="moderate_id">Модерация</label><br>';
                                   $html .= '<input type="checkbox" name="btn_add_var" id="btn_add_var_id" '.((isset($this->input[0]['btn_add_var']) and $this->input[0]['btn_add_var'] == 1)?'checked':'').(isset($_POST['btn_add_var'])?'checked':'').'><label for="btn_add_var_id">Кнопка "добавить свой вариант"</label><br>';
                                   $html .= '<input type="checkbox" name="btn_add_val" id="btn_add_val_id" '.((isset($this->input[0]['btn_add_val']) and $this->input[0]['btn_add_val'] == 1)?'checked':'').(isset($_POST['btn_add_val'])?'checked':'').'><label for="btn_add_val_id">Кнопка "добавить своё значение"</label><br>';
                                   $html .= '<input type="checkbox" name="cancel_selection" id="cancel_selection_id" '.((isset($this->input[0]['cancel_selection']) and $this->input[0]['cancel_selection'] == 1)?'checked':'').(isset($_POST['cancel_selection'])?'checked':'').'><label for="cancel_selection_id">Кнопка "отменить выбранное"</label><br>';
                         $html .='</div>';
                         
                         
                         $html .= '<input type="hidden" name="author_id" value="'.$this->user_id.'">';
                         $html .= '<input type="hidden" name="author_access" value="'.$this->user_access.'">';
                         if(isset($this->input)){// если мы редактируем поле
                              $html .= '<input type="hidden" name="update_form" value="1">';
                         }
                         $html .= '<input type="hidden" name="AJAX" value="greate_new_input">';
                                                 
                         $html .= '<form>';
                    $html .'</div>';
                    if(isset($this->input)){// если мы редактируем поле - возвращаем простой html
                         return $html;
                    }
                    echo '{"response":"show_new_window_2","html":"'.base64_encode($html).'","title":"Создание поля"}';
               }
          
          //////////////////////////////////////////////////////////////////////////////////
          //   -----  END  -----  ПРОВЕРЕНО !!!!!  -----  END  -----
          ///////////////////////////////////////////////////////////////////////////////////
          /////////////////////////////////////////////////////////////////////////////////////
          //     -----  START  -----  НЕ ПРОВЕРЕНО  -----  START  -----
          /////////////////////////////////////////////////////////////////////////////////////
               // сохраняет некаталожные варианты
               private function save_no_cat_variant_AJAX(){
                    unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
                    $this->insert_new_options_in_the_Database();
               }
               // обрабатывает заполненую форму и генерирует варианты
               private function general_form_for_create_product_AJAX(){
                    unset($_POST['AJAX']); // уничтожаем переменную, дабы она не попала в массив обработки
                    
                    $html = '<div style="border-top:1px solid red">'.$this->restructuring_of_the_entry_form().'</div>';
                    // функция не стандартная!!!! отдаём чистый Html
                    echo $html;
               }
          
          //////////////////////////////////////////////////////////////////////////////////
          //   -----  END  -----  НЕ ПРОВЕРЕНО  -----  END  -----
          ///////////////////////////////////////////////////////////////////////////////////
               
               
               
          //////////////////////////
          //   methods_AJAX  -- end
          //////////////////////////
          //////////////////////////
          //  methods
          //////////////////////////
               
          /////////////////////////////////////////////////////////////////////////////////////
          //     -----  START  -----  ПРОВЕРЕНО !!!!!  -----  START  -----
          /////////////////////////////////////////////////////////////////////////////////////
               // получает массив описаний всех полей (кроме списков)
               private function get_cirilic_names_from_Database(){
                    $query = "SELECT `name_en` AS `parent_name`,`name_ru`,`type` FROM `".FORM_INPUTS."` WHERE type IN ('textarea','text','small_header','big_header');";
                    global $mysqli;               
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[] = $row;
                         }
                    }
                    return $arr;             
               }
               // получает все поля по данной форме
               private function get_all_inputs_from__Database(){
                    $query = "SELECT * FROM `".FORM_INPUTS."` WHERE type_product = '".$this->type_product."';";
                    global $mysqli;               
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[$row['id']] = $row;
                         }
                    }
                    return $arr;             
               }
               
               // генератор id
               private function generate_id_Strintg($name){
                    //$id = $val['parent_name'].'_'.($id_i++);
                    $this->id_closed[$name][] = true;
                    $id = $name.'_'.count($this->id_closed[$name]);
                    return $id;
               }
               // запрашивает из базы допуски пользователя
               // необходимо до тех пор, пока при входе в чужой аккаунт меняется только id
               private function get_user_access_Database_Int($id){
                    global $mysqli;
                    $query = "SELECT `access` FROM `".MANAGERS_TBL."` WHERE id = '".$id."'";
                    $result = $mysqli->query($query) or die($mysqli->error);                   
                    $int = 0;
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $int = (int)$row['access'];
                         }
                    }
                    //echo $query;
                    return $int;
               }
               //////////////////////////
               //  ФОРМА заведения продукта     
               //////////////////////////
               public function get_form_Html($type_product){
                    // запоминаем type_product, чтобы обращаться к нему из других методов не передавая его в качестве параметра
                    $this->type_product = $type_product;
                    //////////////////////////
                    //  собираем Html формы
                    //////////////////////////
                         $html = '';
                         $html .= '<div id="general_form_for_create_product" data-type_product="'.$this->type_product.'">';
                              $html .= '<form>';
                              
                                   //////////////////////////
                                   //  для админов добавляем кнопку обновить
                                   //////////////////////////
                                        if($this->user_access_real == 1){
                                             $html .= '<div id="replace_from_window" data-type="'.$type_product.'"><button type="button">Обновить</botton></div>';
                                             
                                        }
                                   
                                   //////////////////////////
                                   //   запрашиваем форму  
                                   //////////////////////////   
                                        $html .= $this->generate_form_Html();
                              
                              $html .= '</form>';
                         //////////////////////////
                         //  для админов добавляем кнопки удалить и редактировать
                         //////////////////////////
                              if($this->user_access == 1){
                                   $html .= '<br><span class="add_element redactor_buttons" data-id="0" data-name_en="'.$this->type_product.'" data-type_product="'.$this->type_product.'">Добавить поле</span>';
                              }
                         //////////////////////////
                         //  Правила добавления полей
                         //////////////////////////
                         if($this->user_access == 1){
                                             /*
                                                  naimenovanie
                                                  product_dop_text
                                                  quantity
                                             */
                                             $html .= '<div style="background-color: rgba(255, 0, 0, 0.12);margin-top:15px; padding:15px;">';
                                             $html .= '<strong>Правила добавления полей</strong><br>';
                                             $html .= '<div>В каждой форме должны содержаться 4 обязательных поля</div>';
                                             $html .= '<ul>';
                                             $html .= '<li>
                                                            <table style="border:none">
                                                                 <tr>
                                                                      <td>№</td>
                                                                      <td>Русский</td>
                                                                      <td>- английский</td>
                                                                 </tr>
                                                                 <tr>
                                                                      <td>1</td>
                                                                      <td>наименование</td>
                                                                      <td>- <strong>naimenovanie</strong></td>
                                                                 </tr>
                                                                 <tr>
                                                                      <td>2</td>
                                                                      <td>доп. название</td>
                                                                      <td>- <strong>product_dop_text</strong></td>
                                                                 </tr>
                                                                 <tr>
                                                                      <td>3</td>
                                                                      <td>тираж</td>
                                                                      <td>- <strong>quantity</strong></td>
                                                                 </tr>
                                                                 <tr>
                                                                      <td>4</td>
                                                                      <td>дата (календарь)<br>тип поля - текстовое поле</td>
                                                                      <td>- <strong>date</strong></td>
                                                                 </tr>
                                                             </table>
                                                       </li>';
                                             
                                             $html .= '</ul>';
                                             $html .= '</div>';
                         }
                         $html .= '</div>';
                         
                    return $html;
               }
               // учавствует в генерации формы для создания товара
               private function generate_form_Html(){
                    //////////////////////////
                    //  вычисляем поля и заголовки
                    //////////////////////////
                         $this->inputs =  $this->get_form_Html_listing_Database_Array();
                    
                    //////////////////////////
                    //  вывод массива вместо формы -- start
                    //////////////////////////
                         // return $this->print_arr($this->inputs);
                    //////////////////////////
                    //  вывод массива вместо формы -- end
                    //////////////////////////
                    $html = '<input type="hidden" name="AJAX" value="general_form_for_create_product">';
                    $html .= '<input type="hidden" name="type_product" value="'.$this->type_product.'">';
                    //////////////////////////
                    //  возвращаем форму
                    //////////////////////////
                    return $this->generate_form_Database_Array($this->inputs,$this->type_product).$html;
               }
               // получаем группы товаров и их секции с описанием
               private function get_arr_section_product_Database(){
                    global $mysqli;
                    $query = "SELECT * FROM `".FORM_GROUPS."` ";
                    $query .= " INNER JOIN `".FROM_SECTIONS."` ON `".FROM_SECTIONS."`.`parent_group`=`".FORM_GROUPS."`.`group_name_en`";
                    // echo $query;
                    $this->arr_section_product = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    
                    $name = '';
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              if($name != $row['group_name_en']){
                                   $this->arr_section_product[$row['group_name_en']] = array(
                                        'name' => $row['group_name_ru']                              
                                   );     
                              }
                              // $form_groups[] = $row;
                              
                              $this->arr_section_product[$row['group_name_en']]['sections'][$row['name_en']] = array(
                                   'name' => $row['name_ru'],
                                   'readonly' => $row['readonly'],
                                   'access' => $row['access'],
                                   'description' => $row['description']
                                   ); 
                              $name = $row['group_name_en'];
                         }
                    }
               }
               // // запрашивает данные по типам товаров
               // private function get_form_type_Database(){
               //      global $mysqli;
               //      $query = "SELECT * FROM `".FORM_GROUPS."` ";
               //      $query .= " INNER JOIN `".FROM_SECTIONS."` ON `".FROM_SECTIONS."`.`parent_group`=`".FORM_GROUPS."`.`group_name_en`";
               //      // echo $query;
               //      $this->arr_section_product = array();
               //      $result = $mysqli->query($query) or die($mysqli->error);
               // }
               // возвращает html формы для заведения запроса на расчёт в отделе снабжения
               public function get_product_form_Html($type_product){
                    // запоминаем выбранный тип продукции
                    $this->type_product = $type_product;
                    $form = self::get_form_Html($this->type_product);
                    return $form;
               }
               // возвращает форму для каталожной продукции
               public function get_for_add_catalog_product(){
                    ob_start();
                         
                         include_once './skins/tpl/client_folder/rt/add_new_position.tpl';
                         $html = ob_get_contents();
                    
                    ob_get_clean();
                    
                    return $html;
               }
               // возвращает форму выбора заведения новой позиции в запрос
               // осущевствляется выбор типа товара
               # на вход подается номер запроса
               private function to_chose_the_type_product_form_Html(){
                    $html = '';
                    $html .= '<form>';
                    $html .= '<table id="get_form_Html_tbl">';
                    $html .= '<tr><th>Тип</th><th>Описание типа</th></tr>';
                    $i=0;
                    // получаем группы товаров и их секции с описанием
                    $arr_section_product = $this->get_arr_section_product_Database();
                    foreach ($this->arr_section_product as $section_product => $section_product_array) {
                         $html .= '<tr><td colspan="2"><div class="section_div">'.$section_product_array['name'].'</div></td></tr>'; // название раздела
                         
                         foreach ($section_product_array['sections'] as $key => $value) {
                              if($this->user_access != 1 && $key == 'pol_many_temp'){
                                   continue;
                              }
                              if($value['access']){
                                   $readonly = ($value['readonly'])?'disabled':'';
                                   $readonly_style = ($value['readonly'])?'style="color:grey"':'';
                                   $html .= '<tr>';
                                        $html .= '<td>';
                                        $html .= '<input type="radio" name="type_product" id="type_product_'.$i.'" value="'.$key.'" '.$readonly.'><label '.$readonly_style.' for="type_product_'.$i.'">'.$value['name'].'</label>';
                                        $html .= '</td>';
                                        $html .= '<td>';
                                        $html .= '<label '.$readonly_style.' for="type_product_'.$i.'">'.$value['description'].'</label>';
                                        $html .= '</td>';
                                   $html .= '</tr>';
                                   $i++;
                              }
                         }
                    }
                    $html .= '</table>';               
                    
                    $html .= '<input type="hidden" name="AJAX" value="get_form_Html">';
                    $html .= '</form>';
                    return $html;
                    // 'show_new_window';
               }
               // поиск артикула
               private function search_articule_Database($art){
                    global $mysqli;
                    $query = "SELECT * FROM `".BASE_TBL."` WHERE `art` LIKE '%".trim($art)."%';";
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[] = $row;
                         }
                    }
                    return $arr;
               }
               // геенератор ФОРМ
               private function generate_form_Database_Array($inputs_arr,$parent_name = '' ,$parent_type ='',$num = 0, $parent = '', $button_var_on = 0){
                    // return $this->print_arr($inputs_ar);
                    $html = '';
                    $redactor_buttons = '';
                    $small_header_buttons = ''; // кнопки для форм
                    $big_header_buttons = ''; // кнопки для форм
                    if(is_array($inputs_arr)){
                         // $this->name_en01 = '';
                         foreach ($inputs_arr as $name_en => $row_inputs) {
                              
                              if($this->user_access ==1){
                                   $html .= '<br>';
                              }
                              $p_name = '';
                              if($parent_type == 'small_header' || $parent_type == 'big_header'){
                                   // если это группа checkbox, то 
                                   // echo $this->form_type[$type_product][$input['parent_name']]['btn_add_var'];
                                   // if($input['type']=='checkbox' && isset($this->form_type[$type_product][$input['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$input['parent_name']]['btn_add_var']){
                                   // if($row_inputs['type']=='checkbox' && isset($inputs_arr[$row_inputs['parent_name']]['btn_add_var']) && !$inputs_arr[$row_inputs['parent_name']]['btn_add_var']){
                                   if($row_inputs['type']=='checkbox' && $button_var_on==0){
                                        $p_name = $row_inputs['parent_name'].'[][]';
                                   }else{
                                        $p_name = $row_inputs['parent_name'].'[0][]';
                                   }
                              }else{
                                   // если есть вконце [], то вырезаем их
                                   $parent = (substr($parent, -2, 2)=='[]')?substr($parent,0,strlen($parent)-2):$parent;
                                   // $parent_ooo = str_replace('['.$row_inputs['name_en'].']', '', $parent);
                                   // если нет в конце [0], добавляем их 
                                   if(!strstr($parent, "[0]")){
                                        $parent = $parent.'[0]';
                                   }
                                   $p_name = $parent.(($num>1)?'['.$row_inputs['parent_name'].']':'').'[]';
                                   // $p_name = $parent.'['.$row_inputs['parent_name'].']'.'[]';
                                   // $p_name = $parent.''.'[]';
                              }
                              if($this->user_access == 1 && $this->user_id == 425){
                                   $html .= '<span style="color:#92B3DC">
                                             id = '.$row_inputs['id'].'; parent_name = '.$row_inputs['parent_name'].'; <br>
                                             type = '.$row_inputs['type'].'; parent_type = '.$parent_type.'; <br>
                                             p_name = "'.$p_name.'"; $button_var_on = "'.$button_var_on.'";
                                             </span><br>';
                              }
                              //закрываем DIV
                              $id = $this->generate_id_Strintg($row_inputs['name_en']);
                              //$html .= '<div class="one_row_for_this_type '.(($this->user_access == 1)?'shine_edit_blocks':'').' '.$name_en.'" data-type="'.$name_en.'" data-moderate="0" data-id="1">';
                              //////////////////////////
                              //  для админов добавляем кнопки удалить и редактировать -- START
                              //////////////////////////
                              if($this->user_access == 1){
                                   // к полю select нельзя прикреплять подгруппы полей и заголовков, поэтому для select запрещаем кнопку "Добавить"
                                   // т.к. для select доаольно трудоёмко исполнить поле редактирование, кнопку "Редактировать" не выводим 
                                  $redactor_buttons = '';
                                   // Ред временно  ( пока не готово ) отключил
                                   $redactor_buttons .= ($row_inputs['type']!="select")?'&nbsp;<span class="group_edit redactor_buttons" data-id="'.$row_inputs['id'].'" data-name_en="'.$parent_name.'">Ред.</span>':'';
                                   $redactor_buttons .= '<span class="group_del redactor_buttons" data-id="'.$row_inputs['id'].'" data-name_en="'.$row_inputs['name_en'].'">Удалить</span>';
                                   $redactor_buttons .= ($row_inputs['type']!="select")?'<span class="add_element redactor_buttons" data-id="'.$row_inputs['id'].'" data-name_en="'.$row_inputs['name_en'].'"  data-type_product="'.$this->type_product.'">Добавить поле</span>':'';
                              }
                              //////////////////////////
                              //  для админов добавляем кнопки удалить и редактировать -- END
                              //////////////////////////
                              
                              //////////////////////////
                              //  вычисляем название поля
                              ////////////////////////// 
                              // $p_name = '';
                              if(isset($_GET['show_dop_info_for_admin'])){
                                   $html .= $row_inputs['id'].'   -    '.$row_inputs['name_en'];     
                              }
                              
                              switch ($row_inputs['type']) {
                                   case 'small_header':
                                        // закрываем предыдущий div, если он был открыт
                                        // закрываем div small_heaer
                                        if(isset($small_header) && $small_header > 0){
                                             $html .= '</div>';
                                             $html .= $small_header_buttons;
                                             $small_header = 0;
                                        }
                                        
                                        // запоминаем, что унас открыт div big_header
                                        $$row_inputs['type'] = $row_inputs['id'];
                                        //////////////////////////
                                        // запоминаем html кнопок разрешённых для данного поля
                                        //////////////////////////
                                             if($row_inputs['btn_add_var'] == 1 || $row_inputs['btn_add_val'] == 1 || $row_inputs['cancel_selection'] == 1){
                                                  $small_header_buttons = '<div class="buttons_form">';
                                                       $small_header_buttons .= ($row_inputs['btn_add_var'])?'<span class="btn_add_var">+ Добавить вариант</span>':'';
                                                       $small_header_buttons .= ($row_inputs['btn_add_val'])?'<span class="btn_add_val">+ Нет в списке</span>':'';
                                                       $small_header_buttons .= ($row_inputs['cancel_selection'])?'<span class="cancel_selection">Сбросить выбранное</span>':'';
                                                  $small_header_buttons .= '</div>';
                                             }else{
                                                 $small_header_buttons = ''; 
                                             }
                                        //открываем div small_header
                                        $html .= '<div class="one_row_for_this_type '.(($this->user_access == 1)?'shine_edit_blocks':'').' '.$name_en.' '.$row_inputs['type'].'" data-type="'.$name_en.'" data-moderate="'.$row_inputs['moderate'].'" data-id="'.$row_inputs['id'].'">';
                                        // если необходима модерация - указываем
                                        $moderate = ($row_inputs['moderate'])?'<span style="color:red; font-size:14px">*</span>':'';
                                        // название 
                                        $html .= '<strong class="'.$row_inputs['type'].'">'.$row_inputs['name_ru'].' '.$moderate.'</strong>'.$redactor_buttons;
                                        // доп описание по полю
                                        $html .= ($row_inputs['note']!='')?'<div style="font-size:10px">'.$row_inputs['note'].'</div>':'<br>';
                                            
                                        break;
                                   case 'big_header':
                                        // закрываем предыдущий div, если он был открыт
                                        // закрываем div big_header
                                        if(isset($big_header) && $big_header > 0){
                                             $html .= '</div>';
                                             $html .= $big_header_buttons;
                                             $big_header = 0;
                                        }
                                        // запоминаем, что унас открыт div big_header
                                        $$row_inputs['type'] = $row_inputs['id'];
                                        //////////////////////////
                                        // запоминаем html кнопок разрешённых для данного поля
                                        //////////////////////////
                                             if($row_inputs['btn_add_var'] == 1 || $row_inputs['btn_add_val'] == 1 || $row_inputs['cancel_selection'] == 1){
                                                  $big_header_buttons .= '<div class="buttons_form">';
                                                       $big_header_buttons .= ($row_inputs['btn_add_var'])?'<span class="btn_add_var">+ вариант</span>':'';
                                                       $big_header_buttons .= ($row_inputs['btn_add_val'])?'<span class="btn_add_val">+ значение</span>':'';
                                                       $big_header_buttons .= ($row_inputs['cancel_selection'])?'<span class="cancel_selection">Сбросить выбранное</span>':'';
                                                  $big_header_buttons .= '</div>';
                                             }else{
                                                 $big_header_buttons = ''; 
                                             }
                                        //открываем div small_header
                                        $html .= '<div class="one_row_for_this_type '.(($this->user_access == 1)?'shine_edit_blocks':'').' '.$name_en.' '.$row_inputs['type'].'" data-type="'.$name_en.'" data-moderate="'.$row_inputs['moderate'].'" data-id="'.$row_inputs['id'].'">';
                                             // если необходима модерация - указываем
                                             $moderate = ($row_inputs['moderate'])?'<span style="color:red; font-size:14px">*</span>':'';
                                             // название 
                                             $html .= '<strong class="'.$row_inputs['type'].'">'.$row_inputs['name_ru'].' '.$moderate.'</strong>'.$redactor_buttons;
                                             // доп описание по полю
                                             $html .= ($row_inputs['note']!='')?'<div style="font-size:10px">'.$row_inputs['note'].'</div>':'<br>';
                                          
                                        // $html .= '<strong>'.$row_inputs['name_ru'].'</strong>';
                                        break;
                                   case 'text':
                                        $html .= '<input data-id="'.$row_inputs['id'].'" type="'.$row_inputs['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$row_inputs['val'].'" placeholder="'.$row_inputs['placeholder'].'">'.$redactor_buttons.'<br>';
                                        break;
                                   case 'textarea':
                                             // выводполя
                                             $html .= '<textarea data-id="'.$row_inputs['id'].'" id="'.$id.'" name="'.$p_name.'" placeholder="'.$row_inputs['placeholder'].'">'.$row_inputs['val'].'</textarea>'.$redactor_buttons.'<br>';
                                        break;
                                   case 'checkbox':
                                        $html .= '<input data-id="'.$row_inputs['id'].'" type="'.$row_inputs['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$row_inputs['name_ru'].'"><label for="'.$id.'">'.$row_inputs['name_ru'].' '.$row_inputs['note'].''.$redactor_buttons.'</label><br>';
                                        break;
                                   case 'radio':
                                        $html .= '<input data-id="'.$row_inputs['id'].'" type="'.$row_inputs['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$row_inputs['name_ru'].'"><label for="'.$id.'">'.$row_inputs['name_ru'].' '.$row_inputs['note'].''.$redactor_buttons.'</label><br>';
                                        break;
                                   case 'select':
                                        $html .= '<select name="'.$p_name.'">';
                                        foreach (json_decode($row_inputs['json']) as $key => $value) {
                                             $html .= '<option value="'.$value.'">'.$value.'</option>';
                                        }
                                        $html .= '</select>';
                                        $html .= $row_inputs['name_ru'].'<br>';
                                        break;
                                   
                                   default:
                                        $html .= '';
                                        $html .= '';
                                        break;
                              }

                              if(!empty($row_inputs['child'])){
                                   // $button_var_on = ($row_inputs['btn_add_var'] == 1)?$row_inputs['btn_add_var']:$button_var_on;
                                   if($row_inputs['type'] == 'small_header' || $row_inputs['type'] == 'big_header'){
                                        $html .= $this->generate_form_Database_Array($row_inputs['child'],$row_inputs['name_en'],$row_inputs['type'],($num + 1),$row_inputs['name_en'],$row_inputs['btn_add_var']);
                                   }else{
                                        $html .= '<div class="pad" '.(($this->user_access == 1)?' style="display: block;"':'').'>';
                                             $html .= $this->generate_form_Database_Array($row_inputs['child'],$row_inputs['name_en'],$row_inputs['type'],($num + 1),$p_name,$row_inputs['btn_add_var']);
                                        $html .= '</div>';
                                   }
                                   // закрываем div small_heaer
                                   if(isset($small_header) && $small_header > 0){
                                        $html .= '</div>';
                                        $html .= $small_header_buttons;
                                        $small_header = 0;
                                   }
                                   // закрываем div big_header
                                   if(isset($big_header) && $big_header > 0){
                                        $html .= '</div>';
                                        $html .= $big_header_buttons;
                                        $big_header = 0;
                                   }
                                        
                                   
                              }
                              // $prevent_id = $row_inputs['id'];
                         }
                         // закрываем div small_heaer
                         if(isset($small_header) && $small_header > 0){
                              $html .= '</div>';
                              $html .= $small_header_buttons;
                              $small_header = 0;
                         }
                         // закрываем div big_header
                         if(isset($big_header) && $big_header > 0){
                              $html .= '</div>';
                              $html .= $big_header_buttons;
                              $big_header = 0;
                         }
                         
                         // $html .= $this->print_arr($this->inputs);
                         
                         return $html; // отдать массив
                    }else{
                         return '';
                    }
               }
               
               // запрашивает из базы список CHILD для полей формы
               private function get_child_listing_Database_Array($child){
                    global $mysqli;               
                    $query = "SELECT * FROM `".FORM_INPUTS."` WHERE `id` IN (".$child.")";
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[] = $row;
                         }
                    }
                    return $arr;
               }
               
          //////////////////////////////////////////////////////////////////////////////////
          //   -----  END  -----  ПРОВЕРЕНО !!!!!  -----  END  -----
          ///////////////////////////////////////////////////////////////////////////////////
               
               
               
               
          /////////////////////////////////////////////////////////////////////////////////////
          //     -----  START  -----  НЕ ПРОВЕРЕНО  -----  START  -----
          /////////////////////////////////////////////////////////////////////////////////////
               // заносит новые варианты в базу, на вход принимает массив POST
               public function insert_new_options_in_the_Database(){
                    $id_i = (isset($_GET['id'])?$_GET['id']:0);
                    // $query_num_i = (isset($this->POST['query_num']))?$_POST['query_num']:(isset($_GET['query_num'])?$_GET['query_num']:0);
                    $query_num_i =isset($_GET['query_num'])?$_GET['query_num']:0;
                    //type_product
                    $type_product = isset($_POST['type_product'])?$_POST['type_product']:0;
                    // проверяем наличие вариантов, если все впорядке идём дальше
                    if(!isset($_POST['json_variants']) || count($_POST['json_variants'])==0){return 'Не было создано ни одного варианта.';}
                    
                    // echo '<pre>';
                    // print_r($this->POST['json_general']);
                    // echo '</pre>';
                    
                    if($query_num_i!=0){
                         // если нам известен $query_num, то работа ведётся из РТ
                         
                         #/ получаем наименование и доп название позиции из Json
                         $arr = json_decode($_POST['json_general'],true);
                         
                         #/ заводим новую строку позиции и получаем её id
                         $new_position_id = $this->insert_new_main_row_Database($query_num_i,$arr,$type_product);
                         
                         #/ для каждой строки варианта заводим новую строку варианта с ценой равной нулю
                         
                         #/ Json
                         foreach ($_POST['json_variants'] as $key => $json_for_variant) {
                              // $str = json_decode(,true);
                              $this->insert_new_dop_data_row_Database($new_position_id,$json_for_variant);
                         }
                         // echo ;
                         echo 'OK';
                         return;
                    }else if($id_i){
                    // В ВЕРСИИ 1.0 ДЕИСТВИЯ С РЕДАКТИРОВАНИЕМ ВАРИАНТОВ ВНУТРИ ПОЗИЦИИ НЕ ПРЕДУСМОТРЕНЫ
                    return;
                         // если нам известен $id, то работа ведётся из позиции
                         #/ 1 выбираем json позиции и считываем его в массив1
                         #/ 2 считываем в массив2 новый json
                         #/ 3 свиреряем
                         #/ ? для каждой строки варианта заводим новую строку варианта с ценой равной нулю
                    }              
                    return 'неожиданный конец программы #0001';            
               }
               private function insert_new_dop_data_row_Database($new_position_id,$json_for_variant){
                    global $mysqli;     
                    // получаем информацию о тираже варианта
                    $arr = json_decode($json_for_variant,true);
                    $quantity = $arr['quantity'];
                    // исключаем информацию о тираже из json варианта
                    // unset($arr['quantity']);
                    //$json_for_variant = json_encode($arr);
                    // status_snab - присваиватся(по умолчанию) первый статус - on_calculation (на расчёт)
                    $query ="INSERT INTO `".RT_DOP_DATA."` SET
                         `row_id` = '".$new_position_id."',
                         `quantity` = '".$quantity."',
                         `price_in` = '0',
                         `price_out` = '0',
                         `create_date` = CURRENT_DATE(),
                         no_cat_json = '".addslashes($json_for_variant)."'";          
                   
                   $result = $mysqli->query($query) or die($mysqli->error);
                    
                    return $mysqli->insert_id;
               }
               private function get_sort_num(){
                    global $mysqli;
                    $query = "SELECT max(`sort`) AS `max_num` FROM `".RT_MAIN_ROWS."` WHERE `query_num` = '".(int)$_GET['query_num']."'";
                    $num = 0;
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $num = $row['max_num']+1;
                         }
                    }
                    return $num;
               }
               // вставить строку в RT_MAIN_ROWS
               private function insert_new_main_row_Database($query_num_i, $arr, $type_product){    
                    $this->sort_num = $this->get_sort_num();
                    // echo '<pre>';
                    // print_r($arr);
                    // echo '</pre>';
                    global $mysqli;     
                    $query ="INSERT INTO `".RT_MAIN_ROWS."` SET
                         `query_num` = '".$query_num_i."',
                         `name` = '".$arr['naimenovanie'][0]." ".(isset($arr['product_dop_text'][0])?$arr['product_dop_text'][0]:'')."',
                         `date_create` = CURRENT_DATE(),
                         `type` = '".$type_product."',
                         `sort` = '".$this->sort_num."',
                        `dop_info_no_cat` = '".addslashes($_POST['json_general'])."'";                    
                    $result = $mysqli->query($query) or die($mysqli->error);
                    return $mysqli->insert_id;    
               }
               
          //////////////////////////////////////////////////////////////////////////////////
          //   -----  END  -----  НЕ ПРОВЕРЕНО  -----  END  -----
          ///////////////////////////////////////////////////////////////////////////////////
               
               // обработка данных из формы
               public function restructuring_of_the_entry_form(){
                    //////////////////////////
                    // запоминаем тип продукции
                    //////////////////////////
                         $this->type_product = $_POST['type_product'];
                         unset($_POST['type_product']);
                    //////////////////////////
                    // запоминаем данные из формы
                    //////////////////////////
                         $data_array_with_form = $_POST;
                    
                    
                    //////////////////////////
                    //  получаем массив описния полей   
                    //////////////////////////
                         $arr = $this->get_cirilic_names_from_Database();
                         foreach ($arr as $key => $value) {
                              $product_options[$value['parent_name']] = array('name'=>$value['name_ru']); 
                         }         
                    
                    // return $this->print_arr($data_array_with_form);
                    
                    // считаем количество возможных вариаций вариантов расчёта
                    
                    // объявляем массив
                    $array_for_table = array();
                    // перебираем входящие данные и пишем в массив
                    foreach ($data_array_with_form as $header_name_en => $value) {
                         
                         // $value - всегда массивы, в противном случае это будет сервисная информация
                         if(!is_array($value)){continue;}
                         /*
                              $value содержит форму, относящуюся к малым заголовкам
                         */
                         
                         // название поля в кириллице                      
                         foreach ($value as $v) {// перебор по вариантам
                              $array_for_table[$header_name_en][]= str_replace(' , ,', ', ', implode('; ',$this->gg_Array($v,1,$product_options)));                              
                              // $array_for_table[$header_name_en][]= implode('; ',$this->gg_Array($v,1,$product_options));                              
                         }
                    }
                    $return = $this->greate_table_variants_Html($array_for_table,$product_options);
                    
                    return $return;
               }
               // всомагательная функция обработки результатов выбора 
               private function gg_Array($arr,$n=0,$product_options){
                    $html = array();
                    $i=0;$k=0;
                    $one_zapate = 0;
                    foreach ($arr as $key1 => $val1) {// снимаем значения
                         if(!is_array($val1)){ // если не массив - то мы добрались до значения
                              
                              $html1 = $val1;
                              // прибавляем ключ
                              $html[(++$i)] = ($html1!='')?$html1.' ':' ';
                              $k=$i; //запоминаем ключ для сравнения
                         }else{
                              # если строка, то у предыдущего поля были дети и $val1 - массив
                              # кирилическое название детей хрнаится в базе
                              if(isset($product_options[$key1]['name']) && $product_options[$key1]['name']!=''){
                                   $html[$i] .= $product_options[$key1]['name'].': '.implode(', ',$this->gg_Array($val1,0,$product_options));
                              }else{
                                   //определяем нужен ли тут знак припинания и какой
                                   $zn ='';
                                   if($one_zapate==0 && trim($html1)!=''){
                                        if($k!=$i){ //  это значит, что родитель всё ещё предыдйщий и нам нужна запятая
                                        $zn = (($n>=0 && trim($html1)!='')?', ':'');
                                   }else{
                                        switch ($n) {// знаки присваивания для разных уровней вложенности
                                             case 1: // уровень первый
                                                  $zn = (trim($html1)!='')?', ':'';
                                                  break;
                                             case 0: // уровень второй
                                                   $zn = (trim($html1)!='')?', ':'';
                                                  break;
                                             
                                             default: // третий и выше
                                                  $zn =  (trim($html1)!='')?', ':'';
                                                  break;
                                        }
                                        // $zn .= ' --$n='.$n.'--';
                                        //$zn = (($n>0)?': ':'');
                                   }
                                   }
                                   
                                   
                                   // удаляем пустые значения (необходимо для неназванных полей)
                                   // array_diff($arr, array(''));
                                   // запоминаем значение
                                   $value = implode(', ', $this->gg_Array($val1,(($n>0)?0:(-1)), $product_options));
                                   
                                   $str = $zn.$value;

                                   $html[$i] .= (trim($str) != "" && trim($value)!=',')?$str:'';  
                                   //$html[$i] .= $zn.implode(', ',$this->gg_Array($val1,0,$product_options));     
                                   
                                   $k++;
                                   
                              }                        
                         }
                         
                    }
                    // сначала метод работал с Html, потом стал работать с Array, название переменной осталось
                    return $html;
               }
               
               // возвращает таблицу всех возможных вариантов из множества, которое натыкал юзер
               private function greate_table_variants_Html($arr,$product_options){
                    // удаляем дубли
                    $arr = $this->delete_identical_variants_Array($arr);
                    // поучаем массив вариантов
                    $array = $this->greate_array_variants_Array($arr);
                    
                    // массив для сохранения предыдущего варианта при выводе строк вариантов
                    // нужен для выделения различий между каждым следующим вариантом
                    $prev_variant = array();
                    // перерабатываем его в таблицу
                    $html = '';
                    $html .= '<form>';
                    $html .= '<input type="hidden" name="AJAX" value="save_no_cat_variant">';
                    $html .= "<input type='hidden' name='json_general' value='".json_encode($arr)."'>";
                    $html .= "<input type='hidden' name='type_product' value='".$this->type_product."'>";
                    // $html .= $this->print_arr($array);
                    $html .= '<div id="json_general" style="display:none">'.json_encode($arr).'</div>';
                    $html .= '<table class="answer_table">';
                    $html .= '<tr>';
                    $html .= '<th>№ варианта</th>';
                    $html .= '<th>Описание</th>';
                    $html .= '<th>удалить</th>';
                    $html .= '</tr>';
                    foreach ($array as $key => $variant) {
                         $html .= "<tr>";
                         $html .= '<td>'.($key+1);
                         // $html .= '<div class="json_hidden" style="display:none">'.json_encode($variant).'</div>';
                         $html .= "<input type='hidden' name='json_variants[]' value='".json_encode($variant)."'>";
                         $html .= '</td>';
                         $html .= '<td>';
                         foreach ($variant as $key1 => $value1) {
                              $bold = (isset($prev_variant[$key1]) && $prev_variant[$key1]!=$value1)?'bold':'normal';
                              $html .= '<span style="font-weight:'.$bold.'">'.$product_options[$key1]['name'].'</span>: '.$value1.'<br>';
                         }
                         $html .= '</td>';
                         $html .= '<td><span class="delete_user_val">X</span></td>';
                                   
                         $html .= '</tr>';
                         $prev_variant = $variant;
                    }
                    
                    $html .= '</table>';
                    $html .= '</form>';
                    return $html;
               }
               // возвращает переработанный массив вариантов
               private function greate_array_variants_Array($arr){
                    // подсчёт количества вариаций 
                    $count = 1;
                    foreach ($arr as $key => $value) {
                         $count = $count*count($value);
                    }         
                    
                    // создаем массив вариантов 
                    $n = 0;
                    // объявляем новый массив
                    $variants = array();
                    foreach ($arr as $key2 => $value2) {
                         
                         if ($n==0) {
                              $f=0;
                              foreach ($value2 as $key3 => $value3) {
                                   for ($k=0; $k < $count/count($value2); $k++) { 
                                        $variants[$f][$key2] = $value3;
                                   $f++;          
                                   }    
                              }
                              $n++;     
                         }else{
                              $f=0;
                              for ($k=0; $k < $count/count($value2); $k++) { 
                                   foreach ($value2 as $key3 => $value3) {                          
                                        $variants[$f][$key2] = $value3;
                                   $f++;          
                                   }    //$f++;
                              }
                              $n=0;
                         }
                    }
                    return $variants;
               }
               //вычищаем дубли вариантов появившиеся из-за неверного заполнения формы
               private function delete_identical_variants_Array($arr){
                    $new_arr = array();
                    foreach ($arr as $key => $value) {
                         $new_arr[$key][0] = $value[0];
                         foreach ($value as $key2 => $value2) {
                              $identical = 0;
                              foreach ($new_arr[$key] as $key3 => $value3) {
                                   if($value3==$value2){// если такой уже есть 
                                        $identical = 1;
                                   }
                              }
                              if($identical==0){// если это не повтор
                                   $new_arr[$key][] = $value2;
                              }
                         }
                    }
                    return $new_arr;
               }
               
               
               
               public function get_names_form_type($type_product){    
                    global $mysqli;               
                    $query = "SELECT * FROM `".FORM_INPUTS."` WHERE `type_product` = '".$type_product."' AND `type` IN ('small_header','big_header','text','textarea')";
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[$row['name_en']] = $row;
                         }
                    }
                    return $arr;
               }
               // запрашивает из базы список вариантов для полей формы по отдельности
               private function get_form_Html_listing_Database_Array($parent_id = 0){
                    global $mysqli;               
                    $query = "SELECT * FROM `".FORM_INPUTS."` WHERE `type_product` = '".$this->type_product."' AND `parent_id` = '".$parent_id."'";
                    $arr = array();
                    $result = $mysqli->query($query) or die($mysqli->error);
                    if($result->num_rows > 0){
                         while($row = $result->fetch_assoc()){
                              $arr[$row['name_en']] = $row;
                              $arr[$row['name_en']]['child'] = $this->get_form_Html_listing_Database_Array($row['id']); 
                         }
                    }
                    return $arr;
                    // return $arr;
               }
               
               
               
               // генерит html
               // private function generate_form_Html_OLD($inputs_arr, $parent='', $type_product){ 
               //      // echo '<pre>';
               //      // print_r($arr);
               //      // echo '</pre>';
               //      $html = '';
               //      $select = 0;
               //      $prevent_type_input = '';
               //      $html .= $this->print_arr($inputs_arr);
               //      foreach ($inputs_arr as $input){
               //           if($select > 0 && $prevent_type_input=="select" && $input['type'] != 'select'){$html .= '</select><br>';$select = 0;}
               //           $prevent_type_input = $input['type'];
               //           $p_name = '';
               //           if($parent==''){
               //                // если это группа checkbox, то 
               //                // echo $this->form_type[$type_product][$input['parent_name']]['btn_add_var'];
               //                // if($input['type']=='checkbox' && isset($this->form_type[$type_product][$input['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$input['parent_name']]['btn_add_var']){
               //                if($input['type']=='checkbox' && isset($this->form_type[$type_product][$input['parent_name']]['btn_add_var']) && !$this->form_type[$type_product][$input['parent_name']]['btn_add_var']){
               //                     $p_name = $input['parent_name'].'[][]';
               //                }else{
               //                     $p_name = $input['parent_name'].'[0][]';
               //                }
               //           }else{
               //                $parent = (substr($parent, -2, 2)=='[]')?substr($parent,0,strlen($parent)-2):$parent;
                              
               //                 if(!strstr($parent, "[0]")){
               //                     $parent = $parent.'[0]';
               //                 }
               //                $p_name = $parent.'['.$input['parent_name'].']'.'[]';
               //           }
                         
                         
               //           $html .= ($input['note']!='')?'<span style="font-size:10px">'.$input['note'].'</span><br>':'';
                         
               //           // обёртка для форматирования при редактировании формы
               //           if($this->user_access == 1){
               //              $html .= '<div class="one_input">';
               //           }
                         
               //           switch ($input['type']) {
               //                case 'textarea':// если тип поля textarea
               //                     //if($select > 0){$html .= '</select><br>';$select =0;}
               //                     switch ($input['manager_id']) {
               //                          case '0': // если запись соответствует 0, т.е. обязательна для вывода
               //                               // выводим как есть
               //                               $html .= '<textarea data-id="'.$input['id'].'" id="'.$id.'" name="'.$p_name.'">'.$input['val'].'</textarea><br>';
               //                               break;
               //                          case $this->user_id: // если запись соответствует id менеджера
               //                               // позволяем менеджеру удалить своё поле
               //                               $html .= '<textarea data-id="'.$input['id'].'" id="'.$id.'" name="'.$p_name.'">'.$input['val'].'</textarea>'.$this->span_del.'<br>';
               //                               break;
                                        
               //                          default:
               //                               # code...
               //                               break;
               //                     }    
               //                     break;
               //                case 'text':// если тип поля text
               //                     //if($select > 0){$html .= '</select><br>';$select =0;}
               //                     switch ($input['manager_id']) {
               //                          case '0': // если запись соответствует 0, т.е. обязательна для вывода
               //                               // выводим как есть
               //                               $html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><br>';
               //                               break;
               //                          case $this->user_id: // если запись соответствует id менеджера
               //                               // позволяем менеджеру удалить своё поле
               //                               $html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'">'.$this->span_del.'<br>';
               //                               break;
                                        
               //                          default:
               //                               # code...
               //                               break;
               //                     }    
               //                     break;
               //                case 'select':// если тип поля select
               //                     if($select == 0){$html .= '<select name="'.$p_name.'">';$select =1;}
               //                     switch ($input['manager_id']) {
               //                          case '0': // если запись соответствует 0, т.е. обязательна для вывода
               //                               // выводим как есть
               //                               $html .= '<option data-id="'.$input['id'].'" id="'.$id.'" value="'.$input['val'].'">'.$input['val'].'</option><br>';
               //                               break;
               //                          case $this->user_id: // если запись соответствует id менеджера
               //                               // позволяем менеджеру удалить своё поле
               //                               $html .= '<option data-id="'.$input['id'].'" id="'.$id.'" value="'.$input['val'].'">'.$input['val'].' '.$this->span_del.'</option><br>';
               //                               break;
                                        
               //                          default:
               //                               # code...
               //                               break;
               //                     }    
               //                     break;
                              
               //                default:
               //                     //if($select > 0){$html .= '</select><br>';$select =0;}
               //                     switch ($input['manager_id']) {
               //                          case '0': // если запись соответствует 0, т.е. обязательна для вывода
               //                               // выводим как есть
               //                               $html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><label for="'.$id.'">'.$input['val'].'</label>';
               //                               // добавляем кнопку добавления поля ввода в группу
               //                               if($this->user_access == 1){
               //                                    // $html .= '<div class="div_add_input_in_group">';
               //                                    $html .= '<span class="add_input_in_form redactor_buttons" data-id="'.$input['id'].'">+ Добавить поле</span>';
               //                                    $html .= '<span class="delete_input_width_form redactor_buttons" data-id="'.$input['id'].'">Удалить</span>';
               //                                    // $html .= '</div>';
               //                               }
               //                               $html .= '<br>';
               //                               break;
               //                          case $this->user_id: // если запись соответствует id менеджера
               //                               // позволяем менеджеру удалить своё поле
               //                               $html .= '<input data-id="'.$input['id'].'" type="'.$input['type'].'" id="'.$id.'" name="'.$p_name.'" value="'.$input['val'].'"><label for="'.$id.'">'.$input['val'].' '.$this->span_del.'</label>';
               //                               // добавляем кнопку добавления поля ввода в группу
               //                               if($this->user_access == 1){
               //                                    // $html .= '<div class="div_add_input_in_group">';
               //                                    $html .= '<span class="add_input_in_form redactor_buttons" data-id="'.$input['id'].'">+ Добавить поле</span>';
               //                                    $html .= '<span class="delete_input_width_form redactor_buttons" data-id="'.$input['id'].'">Удалить</span>';
               //                                    // $html .= '</div>';
               //                               }
               //                               $html .= '<br>';
               //                               break;
                                        
               //                          default:
               //                               # code...
               //                               break;
               //                     }    
               //                     break;
               //           }
               //           if($select > 0 && $prevent_type_input=="select" && $input['type'] != 'select'){$html .= '</select><br>';$select = 0;}
               //           // получаем детей
               //           if($input['child']!=''){
               //                $arr_child = $this->get_child_listing_Database_Array($input['child']);
               //                $html .= '<div class="pad" '.(($this->user_access == 1)?' style="display: block;"':'').'>'.$this->generate_form_Html($arr_child,$p_name,$type_product).'</div>';
               //           }
               //           // обёртка для форматирования при редактировании формы
               //           if($this->user_access == 1 && $select == 0){
               //               $html .= '</div>';
               //           }
               //      }
               //      return $html;
               // }
               
               
          //////////////////////////
          //     SERVICE METHODS
          //////////////////////////
               // распечатать массив в переменную
               private function print_arr($arr){
                    ob_start();    
                         
                         echo '<pre>';
                         print_r($arr);
                         echo '</pre>';      
                         $html = ob_get_contents();
                    
                    ob_get_clean();
                    return $html;
               }
     }
?>