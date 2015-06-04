<?php 
    header('Content-type: text/html; charset=utf-8');
	error_reporting(E_ALL);
	
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/art_img_class.php");
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/com_pred_class.php");
?>
<script type="text/javascript">
    window.onload(window.print());
</script>

<?php	
    //echo $_SERVER['DOCUMENT_ROOT'].'/os/libs/php/common.php';
	
    include($_SERVER['DOCUMENT_ROOT'].'/os/libs/php/common.php');
	//echo; 
	list($version,$param) = explode('{@}',$_GET['data']);
	if($version == 'new'){
	    include($_SERVER['DOCUMENT_ROOT'].'/os/libs/mysqli.php');
	    include($_SERVER['DOCUMENT_ROOT'].'/os/libs/config.php');
	    
	    list($id,$client_id,$manager_id) = explode('-',$param);
	    echo Com_pred::open_in_blank($id,$client_id,$manager_id);
	}
	if($version == 'old') echo Com_pred::open_old_kp($param);
	exit;
	
?>
