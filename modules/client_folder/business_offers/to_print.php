<?php 
    header('Content-type: text/html; charset=utf-8');
	ini_set('error_reporting', E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
	
	include_once($_SERVER['DOCUMENT_ROOT']."/libs/php/classes/aplStdClass.php");
	include_once(ROOT."/libs/php/classes/art_img_class.php");
	include_once(ROOT."/libs/php/classes/com_pred_class.php");
	
?>
<script src="http://<?php echo $_SERVER['HTTP_HOST']; ?>/os/libs/js/convert_spec_offer_class.js" type="application/javascript"></script>
<script type="text/javascript">
  var error_report = '';
	window.onerror = function(msg,url,line){
		error_report += msg + ' line:' + line + ' ' + url +'\r\n';
		return true;
	}
    /*window.onload(
	    // так не работает
	    conv_spec_offer.start()
	   //window.print();
	);*/
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
	
	
?>

<script type="text/javascript">
   conv_spec_offer.start();
   window.print();
</script>
<?php	

	exit;

?>
