<?php 
    header('Content-type: text/html; charset=utf-8');
	error_reporting(E_ALL);
	
	include_once(ROOT."/libs/php/classes/art_img_class.php");
	include_once(ROOT."/libs/php/classes/com_pred_class.php");
?>
<script type="text/javascript">
    window.onload(window.print());
</script>

<?php	
    //echo ROOT.'/libs/php/common.php';
	
    include(ROOT.'/libs/php/common.php');
	//echo; 
	list($version,$param) = explode('{@}',$_GET['data']);
	if($version == 'new'){
	    include(ROOT.'/libs/mysqli.php');
	    include(ROOT.'/libs/config.php');
	    
	    list($id,$client_id,$manager_id) = explode('-',$param);
	    echo Com_pred::open_in_blank($id,$client_id,$manager_id,false);
	}
	if($version == 'old') echo Com_pred::open_old_kp($param);
	exit;
	
?>
