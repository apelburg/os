<?php 

	require_once(ROOT."/libs/php/classes/agreement_class.php");
	
	Agreement::delete_oferta($client_id,$oferta_id);
	//// echo $oferta_id; exit;
	header('Location:'.$_SERVER['HTTP_REFERER'] );

?>
