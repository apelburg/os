<!-- <?php echo __FILE__; ?> -- START-->
<link href="<?php  echo HOST; ?>/libs/js/invoice.js" rel="stylesheet" type="text/css">
<link href="skins/css/invoice.css" rel="stylesheet" type="text/css">


<div class="cabinet_top_menu first_line">
    <ul class="central_menu" style="padding-left: 19px;">
        <li class="selected">
            <a href="#">
                <div class="border">Счета</div>
            </a>
        </li>    
    </ul>
</div>
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
				<th>дата</th>
				<th>в-т</th>
			</tr>
		</thead>
		<tbody>
			<tr class="bill-row">
				<!-- № дата -->
				<td>
					<div class="bill-row--number">
						<span>
							<span>0254</span> 26.01.16
						</span>
					</div>
					<div class="bill-row--type">счёт</div>
				</td>
				<!-- 1c -->
				<td>
					<div class="bill-row--ones checked">
						<input type="checkbox" name="" value="">
					</div>					
				</td>
				<!-- выручка, платежи -->
				<td>
					<div>3254265.00</div>
					<div>976279.00</div>
				</td>
				<!-- клиент -->
				<td>
					<div class="bill-row--price-profit">TEST COMPANY  (тестовая)</div>
					<div class="bill-row--price-payment">OOO TEST COMPANY  (тестовая новые реквизиты)</div>
				</td>
				<!-- заказ, менеджер -->
				<td>
					<div class="bill-row--order-number"></div>
					<div class="bill-row--meneger--full-name"></div>
				</td>
				<!-- иконка флаг -->
				<td>
					<div class="bill-row--icons-flag checked"></div>
				</td>
				<!-- клиент -->
				<td>
					<div class="bill-row--client--name"></div>
					<div class="bill-row--client--requsits"></div>
				</td>
				<!-- себестоимость -->
				<td>
					<div class="bill-row--price-start"></div>
					<div class="bill-row--price-our-pyment"></div>
				</td>
				<!-- исконка глаз -->
				<td  class="bill-row--ice checked">
					<div></div>
				</td>
				<!-- прибыль -->
				<td>
					<div class="bill-row--price-our-profit"></div>
				</td>
				<!-- иконка калькулятор -->
				<td>
					<div class="bill-row--icons-calculator checked"></div>
				</td>
				<!-- ТТН -->
				<!-- № -->
				<td class="bill-row--ttn--number">
					<div></div>
				</td>
				<!-- дата -->
				<td class="bill-row--ttn--date">
					<div></div>
				</td>
				<!-- в-т -->
				<td class="bill-row--ttn--vt">
					<div></div>
				</td>
				<!-- СПФ -->
				<!-- дата -->
				<td class="bill-row--spf--number">
					<div></div>
				</td>
				<!-- в-т -->
				<td class="bill-row--spf--vt">
					<div></div>
				</td>
				<!-- статус заказа -->
				<td class="bill-row--status">
					<div></div>
				</td>
				<!-- иконки -->
				<td>
					<!-- иконка оповещений -->
					<div class="bill-row--din-din checked"></div>
					<!-- иконка комментариев -->
					<div class="bill-row--comment isfull"></div>
				</td>
			</tr>
		</tbody>
	</table>
</div>
<!-- <?php echo __FILE__; ?> -- END