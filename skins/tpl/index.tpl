<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta charset="UTF-8" />    
<link href="./skins/css/styles.css" rel="stylesheet" type="text/css">
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
<link href="./skins/css/styles_sample.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="libs/js/jquery.1.10.2.min.js"></script>
<script type="text/javascript" src="libs/js/jquery_ui/jquery-ui.min.js"></script>
<link href="libs/js/jquery_ui/jquery-ui.theme.css" rel="stylesheet" type="text/css">
<link href="libs/js/jquery_ui/jquery-ui.structure.css" rel="stylesheet" type="text/css">
<?php
// echo phpinfo();
if(isset($_GET['page']) && ($_GET['page']=="samples" || $_GET['page']=="clients")){
echo PHP_EOL; //PHP_EOL - константа переноса строки используется вместо /r/n для кроссплатформенности
// echo '<link href="./skins/css/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css">' . PHP_EOL;
echo '<link href="./skins/css/checkboxes.css" rel="stylesheet" type="text/css">' . PHP_EOL;
//echo '<script type="text/javascript" src="libs/js/accounting.js"></script>' . PHP_EOL; //денежный формат числа
if(isset($_GET['sample_page']) && $_GET['sample_page']=='received'){
echo '<script src="libs/js/jquery.printPage.js" type="text/javascript"></script>';
}
}

if(isset($_GET['page']) && $_GET['page']=="clients"){
echo '<script type="text/javascript" src="libs/js/client_folders.js"></script>' . PHP_EOL;
echo '<link href="./skins/css/main.css" rel="stylesheet" type="text/css">' . PHP_EOL;
}
?>
<script type="text/javascript" src="libs/js/common.js"></script>
<script type="text/javascript" src="libs/js/geometry.js"></script>
<script type="text/javascript" src="libs/js/upWindowMenu.js"></script>
<script type="text/javascript" src="libs/js/sample.js"></script>
<title>Онлайн сервис</title>
</head>

<body>
<div class="main_container">
    <table class="main_menu_tbl noselect">
        <tr>
            <td>
                <div>
                    <a href="?page=clients&section=clients_list" class="<?php if($page=='clients')echo'selected'; ?>">Клиенты</a>
                
                    <a href="?page=suppliers&section=suppliers_list" class="<?php if($page=='suppliers')echo'selected'; ?>">Поставщики</a>
                
                    <a href="?page=planner" class="<?php if($page=='planner')echo'selected'; ?>">Планы</a>
                
                    <a href="?page=orders" class="<?php if($page=='orders')echo'selected'; ?>">Заказы</a>
                
                    <a href="?page=documents" class="<?php if($page=='documents')echo'selected'; ?>">Документы</a>
                
                    <a href="?page=samples&sample_page=start" class="<?php if($page=='samples')echo'selected'; ?>">Образцы</a>
                
                    <a href="?page=delivery" class="<?php if($page=='delivery')echo'selected'; ?>">Доставка</a>
                
                    <a href="?page=design" class="<?php if($page=='design')echo'selected'; ?>">Дизайн</a>
                
                    <a href="?page=empty2" class="<?php if($page=='empty2')echo'selected'; ?>">Производство</a>
                
                    <a href="?page=empty3" class="<?php if($page=='empty3')echo'selected'; ?>">Уведомления</a>

                    <a href="?page=cabinet&section=requests&subsection=no_worcked" class="<?php if($page=='cabinet')echo'selected'; ?>">Кабинет</a>
                    
                    <a href="?page=client_folder" class="<?php if($page=='client_folder')echo'selected'; ?>">Папка клиента</a>
                    
                    <a href="?page=empty4" class="<?php if($page=='empty4')echo'selected'; ?>">Ещё</a>
                
                    <!--<a href="?page=_test_rt" class="<?php if($page=='empty4')echo'selected'; ?>">test_rt</a>-->
                </div>
            </td>
            <td style="width:auto;">&nbsp;
                
            </td>
            <td style="width:250px;padding:0px;">
                <table class="authentication_plank_tbl">
                    <tr>
                        <td style="width:auto;text-align:right;">
                            <div style="overflow:hidden;">
                                <nobr><?php echo $position.': '.$user_name.' '.$user_last_name; ?></nobr>
                            </div>
                        </td>
                        <td style="padding:0px 2px;">
                            <div>
                                <a href="#" onclick="return show_hide_div('authentication_menu_div');"><img src="./skins/images/img_design/flag.png"></a>
                            </div>
                            <div class="authentication_menu_container">
                               <div class="authentication_menu_div" id="authentication_menu_div">
                                    <?php echo $authentication_menu_dop_items; ?>
                                    <div class="cap2"><nobr><a href="?out">выйти из приложения <!--<span class="cross">&#215</span>--></a></nobr></div>
                                  
                               </div>
                            </div>
                            
                            
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
	
    <?php 
    if(isset($_GET['page']) && $_GET['page']=='samples'){include('sample/bar_tbl.tpl');}
    echo $content; 
    ?>
</div>
<div style="position:absolute;right:0px;bottom:0px;"><a href="#" onclick="alert(error_report);return false;">ошибки</a></div>
</body>
</html>
