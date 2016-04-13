<!-- <?php echo __FILE__; ?> -- START-->
	<script src="<?php  echo HOST; ?>/libs/js/invoice.js" rel="stylesheet" type="application/javascript"></script>
<link href="skins/css/invoice.css" rel="stylesheet" type="text/css">
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
<div id="invoceData" style="display:none">test</div>
<div class="cabinet_top_menu first_line" style="background-color:#92b73e">
    <ul class="central_menu" style="padding-left: 19px;padding-right: 19px;">
        <li class="selected">
            <a href="#">
                <div class="border">Запрос</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Готовые</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Част. оплаченные</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Оплаченные</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Запрос ТТН</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Готовые ТТН</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Част. отгрузка</div>
            </a>
        </li>   
        <li class="">
            <a href="#">
                <div class="border">Отгрузка</div>
            </a>
        </li>    
        <li class="">
            <a href="#">
                <div class="border">Закрытые</div>
            </a>
        </li>    
        <li class="" style="float:right">
            <a href="#">
                <div class="border">Все</div>
            </a>
        </li>    
        
    </ul>
</div>
<div id="js-main-invoice">
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
				<th>№</th>
				<th>дата</th>
				<th>в-т</th>
				<th>№</th>
				<th>в-т</th>
			</tr>
		</thead>
		<tbody>
			<tr class="invoice-row">
				<!-- № дата -->
				<td>
					<div class="invoice-row--number">
						<span>
							<span>0254</span> 26.01.16
						</span>
					</div>
					<div class="invoice-row--type">счёт</div>
				</td>
				<!-- 1c -->
				<td class="invoice-row--checkboxtd checked">
					<div class="invoice-row--checkboxtd-div">
					</div>					
				</td>
				<!-- выручка, платежи -->
				<td>
					<div class="invoice-row--price-profit">3254265.00</div>
					<div class="invoice-row--price-payment">976279.00</div>
				</td>
				<!-- заказ, менеджер -->
				<td>
					<div class="invoice-row--order-number"></div>
					<div class="invoice-row--meneger--full-name"></div>
				</td>
				<!-- иконка флаг -->
				<td class="invoice-row--icons-flag checked"></td>
				<!-- клиент -->
				<td>
					<div class="invoice-row--client--name">TEST COMPANY  (тестовая)</div>
					<div class="invoice-row--client--requsits">OOO TEST COMPANY  (тестовая новые реквизиты)</div>
				</td>
				<!-- себестоимость -->
				<td>
					<div class="invoice-row--price-start">1 235 256.00</div>
					<div class="invoice-row--price-our-pyment">100 000</div>
				</td>
				<!-- исконка глаз -->
				<td  class="invoice-row--ice checked"></td>
				<!-- прибыль -->
				<td>
					<div class="invoice-row--price-our-profit">15 500.00</div>
					<div class="invoice-row--price-our-profit-percent">-15%</div>
				</td>
				<!-- иконка калькулятор -->
				<td class="invoice-row--icons-calculator checked"></td>
				<!-- ТТН -->
				<!-- № -->
				<td class="invoice-row--ttn--number">
					<div>запрос</div>
				</td>
				<!-- дата -->
				<td class="invoice-row--ttn--date">
					<div>12.13.16</div>
				</td>
				<!-- в-т -->
				<td class="invoice-row--ttn--vt invoice-row--checkboxtd">
					<div class="invoice-row--checkboxtd-div">
					</div>					
				</td>
				<!-- СПФ -->
				<!-- дата -->
				<td class="invoice-row--spf--number">
					<div>12.13.16</div>
				</td>
				<!-- в-т -->
				<td class="invoice-row--spf--vt invoice-row--checkboxtd checked">
					<div class="invoice-row--checkboxtd-div">
					</div>					
				</td>
				<!-- статус заказа -->
				<td class="invoice-row--status">
					<div>
						не отгружен
					</div>
				</td>
				<!-- иконки -->
				<td>
					<!-- иконка оповещений -->
					<div class="invoice-row-icon invoice-row--din-din checked">&nbsp;</div>
					<!-- иконка комментариев -->
					<div class="invoice-row-icon invoice-row--comment isfull">&nbsp;</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- <?php echo __FILE__; ?> -- END