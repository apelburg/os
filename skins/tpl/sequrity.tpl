<!-- <?php echo __FILE__; ?> -- START-->
<?php 
	// if(!isset($_GET['innerWidth'])){
		// header("Location: http://".$_SERVER['HTTP_HOST']."/");
	// }
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
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
<head>
    <title><?php if(isset($seo_data['title']))echo $seo_data['title']; ?></title>
    <meta name="description" content="<?php  if(isset($seo_data['description']))echo $seo_data['description']; ?>" >
    <meta name="keywords" content="<?php  if(isset($seo_data['keywords']))echo $seo_data['keywords']; ?>" >
    <meta http-equiv="Content-Type" content="text/html; charset=utf8" >
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=0" >
    <meta property="fb:admins" content="100008881151849"/>
    <meta property="fb:admins" content="100005181874874"/>
    <meta property="fb:app_id" content="946485612050955"/>
    <meta http-equiv="Expires" content="Wed, 30 Jun 2015 08:21:57 GMT" />
    <link href="../skins/css/jquery-ui-1.10.4.custom.css" rel="stylesheet" type="text/css">
    
    <?php if(isset($_SESSION['access']['access']) && $_SESSION['access']['access']==1){
        echo '<link href="../skins/css/main.css?v1.1" rel="stylesheet" type="text/css">';
    }else{
        echo '<link href="../skins/css/main.css" rel="stylesheet" type="text/css">';
    } ?>

        
    <script async type="text/javascript" src="../libs/js/common.js"></script> 
    <script async type="text/javascript" src="./libs/js/Base64Class.js"></script>
    <script async type="text/javascript" src="../admin/order_manager/libs/js/common.js"></script>
	<script async type="text/javascript" src="../admin/order_manager/libs/js/up_window_consructor.js"></script>
    <script async type="text/javascript" src="../libs/js/class_special_offers.js"></script>
    <script async type="text/javascript" src="../libs/js/geometry.js"></script>
    <!-- JQuery -->
    <script src="https://code.jquery.com/jquery-2.1.3.min.js"></script>
    <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
    <!-- response -->
    <script async type="text/javascript" src="../libs/js/standard_response_handler.js"></script>
    
    
    <!-- генеральный стиль, включает в себя шапку, левое меню и подвал -->
    <link href="../favicon.ico" rel="shortcut icon" type="image/x-icon" /><!-- new year 2015 -->
    <?php echo '<link href="../skins/css/new_year_2015.css?v1.1" rel="stylesheet" type="text/css">'; ?>

</head>
<script type="text/javascript">
$(document).keydown(function(e) {   
	if(e.keyCode == 27){
		window.location.href = "http://<?=$_SERVER['HTTP_HOST'];?>/"
	}
            // alert(e.keyCode);
            // alert($('#tableSeach input,#MobilSeach').is(':focus'));
            // if($('#tableSeach input,#MobilSeach').is(':focus')){
            //     alert($('#tableSeach input,#MobilSeach').is(':focus'));
            //     $('#seach_form').submit();
            // }
});

function enter_clic_for_autorisation(){
		//ловим нажатие на enter при фокусе на полях ввода
		$("#autorisationWindow input").keypress(function(e){
		if(e.keyCode==13){
			$("#autorisationWindow").parent().find("input").removeAttr('style');
			submit_autorisation();
		}
	});	
}
$(document).ready(function(e) {
    $('#autorisationButton').click(function(){
		$(this).parent().parent().parent().parent().find("input,textarea").removeAttr('style');
		submit_autorisation();	
	});
	enter_clic_for_autorisation();
});

function autorisation_qute(){
	//alert(HOST);
	$.post(HOST,
		{ 
		out: 1
		},
		function(data){
			//alert(data)
			if(data=="output_is_performed"){				
				change_mesage_for_for_user('no_auth');
				$('#messageWindow .imgReloudSecpic').click();
				
				$("#headButtonLogin").removeClass('disp0').addClass('disp1');
				$("#headButtonKabinet").removeAttr('style').removeClass('disp1').addClass('disp0');
				$("#kabinetWindow").fadeOut('fast',function(){
					window.modalId = 0;					
					$("#kabinetWindow .modalWindowsClassContent").html();
					setTimeout(location.reload(),5000);
				});	
			}
		});	
}


function submit_autorisation(){
	$.post(HOST+"/lock_new.php",
		{ 
		login: $('#autorisation_login').val(),
		password: $('#autorisation_password').val(),
		session_id: $('#autorisation_session_id').val()
		},
		function(data){
			
			if(data=="OKEY"){
				 window.location.href = "http://<?=$_SERVER['HTTP_HOST'];?>/os/"
			}else{				
				//$('#autorisationWindow').find('.error').fadeOut('fast')
				//alert("Это json с сервера:\n\n"+data);
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

<!-- /счетчики --> 

<body >
<div id="bgForModalWindowsInMobile" style="position:fixed; height:100%; width:100%; ">
	<div class="modalWindowsClass" id="autorisationWindow" style="display:block; top:50%;margin-top:-128px;border-top: 3px solid #9C9B9C;">
    	<!-- <img class="topImage" alt="" src="../skins/images/general/windows/bgUpWin.png" /> -->
        <div class="modalWindowsClassContent">
        <div class="headWindow">
                Авторизация
            </div>
            <div class="error" style="display:none">Неверно заполнена форма</div>
            <div class="formWindow">
                <form method="post" action="../lock_new.php">
	                	<?php
	                    if(!isset($_SESSION)){
	                        session_start();
	                    }
                     echo '<input value="'.session_id().'" name="session_id" id="autorisation_session_id" type="hidden">'; 
                     ?>
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
                    <a href="#" class="registrationLink" style="display:none">Регистрация</a>
                </form>
            </div>
    </div>
</div>
</div>
<div><div>
</body>
</html>
<!-- <?php echo __FILE__; ?> -- END-->