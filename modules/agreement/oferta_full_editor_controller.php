<?php 
   
    
	include_once($_SERVER['DOCUMENT_ROOT']."/os/libs/php/classes/agreement_class.php");
    $oferta_data = Agreement::fetch_oferta_common_data($oferta_id);
    /*echo '<pre>'; print_r($oferta_data); echo '</pre>';*/

	$path = $_SERVER['DOCUMENT_ROOT'].'/admin/order_manager/data/agreements/'.$client_id.'/'.substr($oferta_data['date_time'],0,4).'/offerts/'.$oferta_data['our_requisit_id'].'_'.$oferta_data['client_requisit_id'].'/'.$oferta_data['id'].'.tpl';
	 
    if(isset($_POST['submit_ok']))
	{
		put_content($path,$_POST['page_content']);
	}
	
	
  
 
    $page_content = get_content($path); 

?>

<?php include ('./skins/tpl/agreement/tinymse_js_block.html'); ?>
<style> .main_menu_tbl{ display:none; } </style>
<div style="margin:auto;width:1200px;">
    <div style="margin:10px 20px;">
        <button type="button" onclick="location = '?<?php echo htmlspecialchars(addOrReplaceGetOnURL('section=agreement_editor')); ?>';" style="cursor:pointer;">назад</button>
    </div>
    <div style="margin:0px 20px;">
    <form action="" method="post">
    <textarea id='elm1' name='page_content' style="width:1150px; height:600px;"><?php echo $page_content; ?></textarea>
    <input type='reset' value='сброс'>&nbsp;&nbsp;&nbsp;&nbsp;<input type='submit' name='submit_ok' value='изменить'>
    </form>
    </div>
</div>
