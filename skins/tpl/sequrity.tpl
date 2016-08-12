<?php
    function getSessionId(){
        if(!isset($_SESSION)){
            session_start();
        }
        return session_id();
    }
?>


<!-- <?php echo __FILE__; ?> -- START-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>Форма авторизации</title>


        <meta http-equiv="Content-Type" content="text/html; charset=utf8" >
        <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=0" >

        <link href="../skins/css/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css">
        <link href="../skins/css/main.css" rel="stylesheet" type="text/css">
        <script async type="text/javascript" src="./libs/js/Base64Class.js"></script>

        <!-- JQuery -->
        <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
        <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
        <!-- response -->
        <script async type="text/javascript" src="../libs/js/standard_response_handler.js"></script>

        <link href="../favicon.ico" rel="shortcut icon" type="image/x-icon" />




        <style type="text/css" media="screen">
            #autorisationWindow {
                position: fixed;
                display: none;
                right: 3%;
                width: 280px;
                z-index: 9999;
                background-color: #fff;
                margin-right: 1px;
                margin-left: -140px;
                left: 50%;
            }
        </style>
        <script type="text/javascript">

            $(document).ready(function(e) {
                //ловим нажатие на enter при фокусе на полях ввода
                $("#autorisationWindow input").keypress(function(e){
                    if(e.keyCode==13){
                        $("#autorisationWindow").parent().find("input").removeAttr('style');
                        submit_authorisation();
                    }
                });
            });
            $(document).keydown(function(e) {
                // при нажатии на ESC возврат на главную
                if(e.keyCode == 27){
                    window.location.href = "http://<?=$_SERVER['HTTP_HOST'];?>/"
                }
            });

            $(document).on('click','#autorisationButton',function(){
                $(this).parent().parent().parent().parent().find("input,textarea").removeAttr('style');
                submit_authorisation();
            });


            function submit_authorisation(){
                $.post("../lock.php",
                    {
                    login: $('#autorisation_login').val(),
                    password: $('#autorisation_password').val(),
                    session_id: $('#autorisation_session_id').val()
                    },
                    function(data){

                        if(data=="OKEY"){
                             window.location.href = "http://<?=$_SERVER['HTTP_HOST'];?>/os/?page=clients&section=clients_list"
                        }else{
                            var obj = jQuery.parseJSON(data);
                            var set = 0;
                            var message = '';
                            for (var i in obj) {
                                var row = obj[i];
                                if(row.error==2){
                                    message = row.message;
                                    $('#autorisationWindow').find('.error').fadeIn().html(message);
                                }else{
                                    $('#'+row.input_id).css({'border-color':'red'});
                                    message = row.message;
                                    set++;
                                }
                            }
                            if(set>1){
                                $('#autorisationWindow').find('.error').fadeIn().html('Неверно заполнена форма');
                            }else if(set==1){
                                $('#autorisationWindow').find('.error').fadeIn().html(message);
                            }
                        }
                    });
            }
        </script>
    </head>
    <body >
        <div id="bgForModalWindowsInMobile" style="position:fixed; height:100%; width:100%; ">
            <div class="modalWindowsClass" id="autorisationWindow" style="display:block; top:50%;margin-top:-128px;border-top: 3px solid #9C9B9C;">
                <div class="modalWindowsClassContent">
                    <div class="headWindow">
                        Авторизация
                    </div>
                    <div class="error" style="display:none">Неверно заполнена форма</div>
                    <div class="formWindow">
                        <form method="post" action="../lock.php">
                            <input value="<?=getSessionId();?>" name="session_id" id="autorisation_session_id" type="hidden">
                            <input autofocus type="text" onClick="" autocapitalize="off" autocomplete='off' spellcheck='false' autocorrect='off' name="login" id="autorisation_login" placeholder="логин">
                            <input type="password" onClick="$(this).focus();$(this).focus();" name="password" id="autorisation_password" autocomplete='off' spellcheck='false' autocorrect='off' placeholder="пароль">
                            <a href="#" class="recoveryPasswordLink" style="display:none">Забыли пароль?</a>
                            <div class="table" style="width:100%">
                                <div class="row">
                                    <div class="cell"></div>
                                    <div class="cell"><input type="button" value="" id="autorisationButton"></div>
                                    <div class="cell"></div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
<!-- <?=__FILE__;?> -- END-->