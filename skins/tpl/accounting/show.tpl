<!-- <?php echo __FILE__; ?> -- START-->

<link href="skins/css/accounting.css" rel="stylesheet" type="text/css">
<link href="skins/css/comments.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.urlVar.js"></script>

<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/js_coffee_library.js"></script>
<script src="<?php  echo HOST; ?>/libs/js/accounting.js" rel="stylesheet" type="application/javascript"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/menuClick.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/ZeroClipboard.js"></script>
<div id="invoice-button-top"></div>
<script language="JavaScript">
	/**
	 * Запуск модуля Учёт
	 */
	$(document).ready(function(){
		$('#js-accounting').accountingCalculation();
		<?php

			if($Accounting->user_access == 1 || $Accounting->user_access == 2){
				echo "$('#js-accounting').accountingOptions();";
			}
		?>


		section = $.urlVar('section');
		if(section == undefined){
			$('#js-general-accounting-menu ul li').eq(0).click();
		}else{
			$('#js-general-accounting-menu ul li').each(function(){
				if(Number(section) == $(this).data().index){
					$(this).click()
				}
			})
		}

	});

</script>
<div id="js-accounting">
	<div id="js-main-accounting-div">
	</div>
</div>
<!-- <?php echo __FILE__; ?> -- END