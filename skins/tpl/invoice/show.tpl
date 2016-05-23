<!-- <?php echo __FILE__; ?> -- START-->
	<script src="<?php  echo HOST; ?>/libs/js/invoice.js" rel="stylesheet" type="application/javascript"></script>
<link href="skins/css/invoice.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.urlVar.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/menuClick.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/ZeroClipboard.js"></script>
<div id="invoice-button-top"></div>
<script language="JavaScript">
	/**
	 * Запуск модуля счета / склад
	 */

	$(document).ready(function(){
		<?php
			if($Invoice->user['access'] == 7){
				?>$('#js-main-invoice-table').sklad()<?php
			}else{
				?>$('#js-main-invoice-table').invoice()<?php
			}
		?>
	});

</script>
<?php
	include_once __DIR__."../../clients/client_list/default_menu.tpl";

?>

<div id="js-menu-invoice" class="cabinet_top_menu" style="background-color:#92b73e">
    <ul class="central_menu" style="padding-left: 19px;padding-right: 19px;">

        
    </ul>
</div>
<div id="js-main-invoice-div">
	<table id="js-main-invoice-table">
		<thead>

		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>
<!-- <?php echo __FILE__; ?> -- END