<!-- <?php echo __FILE__; ?> -- START-->

<link href="skins/css/invoice.css" rel="stylesheet" type="text/css">
<link href="skins/css/comments.css" rel="stylesheet" type="text/css">
<link href="skins/css/menuClick.css" rel="stylesheet" type="text/css">

<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.urlVar.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/menuClick.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/ZeroClipboard.js"></script>

<script src="<?php  echo HOST; ?>/libs/js/js_coffee_library.js" rel="stylesheet" type="application/javascript"></script>
<script src="<?php  echo HOST; ?>/libs/js/invoice.js" rel="stylesheet" type="application/javascript"></script>
<!-- подключаем date range -->
<script type="text/javascript" src="//cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap/latest/css/bootstrap.css" />

<!-- Include Date Range Picker -->
<script type="text/javascript" src="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.js"></script>
<link rel="stylesheet" type="text/css" href="//cdn.jsdelivr.net/bootstrap.daterangepicker/2/daterangepicker.css" />
<div id="invoice-button-top"></div>
<script language="JavaScript">
	/**
	 * Запуск модуля счета / склад
	 */

	$(document).ready(function(){
		$('#js-main-invoice-table').sklad()
	});

</script>
<?php
	if (isset($_GET['client_id'])){
		include_once __DIR__."../../clients/client_list/default_menu.tpl";
	}

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