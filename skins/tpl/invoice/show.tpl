<!-- <?php echo __FILE__; ?> -- START-->
	<script src="<?php  echo HOST; ?>/libs/js/invoice.js" rel="stylesheet" type="application/javascript"></script>
<link href="skins/css/invoice.css" rel="stylesheet" type="text/css">
<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery.urlVar.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/menuClick.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/ZeroClipboard.js"></script>
<div id="invoice-button-top"></div>

<div class="cabinet_top_menu first_line">
    <ul class="central_menu" style="padding-left: 19px;">
        <li class="selected">
            <a href="#">
                <div class="border">Счета</div>
            </a>
        </li>    
    </ul>
</div>

<div id="js-menu-invoice" class="cabinet_top_menu" style="background-color:#92b73e">
    <ul class="central_menu" style="padding-left: 19px;padding-right: 19px;">

        
    </ul>
</div>
<div id="js-main-invoice-div">
	<table id="js-main-invoice-table">
		<thead>
			<tr>
				<th rowspan="2">№, дата</th>
				<th rowspan="2">1C</th>
				<th rowspan="2">выручка,<br> платежи</th>
				<th rowspan="2">заказ<br> менеджер</th>
				<th rowspan="2" class="flag"></th>
				<th rowspan="2">клиент: название и юр. лицо</th>
				<th rowspan="2">себестоимость</th>
				<th rowspan="2" class="ice"></th>
				<th rowspan="2">прибыль</th>
				<th rowspan="2" class="calculator"></th>
				<th colspan="3">ТТН</th>
				<th colspan="2">СПФ</th>
				<th rowspan="2">статус заказа</th>
				<th rowspan="2" class="dindin"></th>
			</tr>
			<tr>
				<th id="defttn1">№</th>
				<th id="defttn2">дата</th>
				<th id="defttn3">в-т</th>
				<th>№</th>
				<th>в-т</th>
			</tr>
		</thead>
		<tbody>
			
		</tbody>
	</table>
</div>
<!-- <?php echo __FILE__; ?> -- END