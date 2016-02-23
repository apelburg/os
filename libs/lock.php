<?php
    session_start();
    
	if(!isset($_SESSION['access'])){
         if(!isset($_POST['password']) || ( $_POST['password'] == '' || $_POST['login']== '')){
	         unset($_SESSION['access']);
	         include('./skins/tpl/sequrity.tpl');
		     exit;
	     }else{		 		 
		     $query = "SELECT*FROM `".MANAGERS_TBL."` WHERE `nickname` = '".$_POST['login']."' AND `pass` = '".md5($_POST['password'])."'";
	         $result = mysql_query($query,$db);
	         if(!$result)exit(mysql_error());
	         if(mysql_num_rows($result)>0 && $_POST['session_id'] === session_id()){
				 $_SESSION['access']['user_id'] = mysql_result($result,0,'id');
				 $_SESSION['access']['email'] = mysql_result($result,0,'email');
				 $_SESSION['access']['access'] = mysql_result($result,0,'access');

			 }
			 
			 header('Location:'.$_SERVER['REQUEST_URI']);
		 }
    }
	
	if(isset($_GET['out'])){
			unset($_SESSION['access']);
			header('Location:/os/');	
 	}

   
?>