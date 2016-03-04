<div id="js-main-service_center">
		
	<div id="js-main-service_center-variants">
		<div class="spacer_div"></div>
		<div id="js-main-service_center-top_menu">
			<ul>
				<li class="checked">
					<div>Артикулы</div>
				</li>
				<?=$this->group_list();?>
			</ul>
		</div>
		<div id="js-main-service_center-variants-div-table">
			<div id="wraper_classer">

			<table id="js-main-service_center-variants-table">
				<thead>
					<tr class="">
						<th></th>
						<th class="">
							<input type="checkbox" name="">
							<div class="js-psevdo_checkbox"></div>
						</th>
						<th></th>
						<th></th>
						<th></th>
						<th>все варианты</th>
						<th>услуг</th>
						<th>номенклатура</th>
						<th>тираж</th>
						<th></th>
					</tr>		
				</thead>
				<tbody>
					<?=$variants_rows;?>
					
				</tbody>			
					
			</table>
			</div>

		</div>
		<?php 
			echo '<div id="js-depending_on_the_services_and_options">'.json_encode($this->services_related).'</div>';
			echo '<div id="js-depending_on_the_options_and_services">'.json_encode($this->services_related_dop).'</div>';
		?>
			
		<div class="spacer_div"></div>
	</div>
	<div id="js-main-service_center-variants-services">
		<div id="js-main-service_center-variants-services-head">
			Управление дополнительными улугами
		</div>
		<div id="js-main-service_center-variants-services-div-table">
			<table>
				<tr>
					<th colspan="9"></th>
					<th>тираж</th>
					<th>входящая</th>
					<th>без скидки</th>
					<th>скидка</th>
					<th>со скидкой</th>
					<th></th>
				</tr>
				<tr class="itogo">
					<td colspan="9">Сувенир и дополнительные услуги, итоговая исходящая стоимость</td>
					<td></td>
					<td class="price price_in">
						<div class="for_one"><span>205.73</span>р</div>
						<div class="for_all"><span>28332.00</span>р</div>
					</td>
					<td class="price price_out">
						<div class="for_one"><span>205.73</span>р</div>
						<div class="for_all"><span>28332.00</span>р</div>
					</td>
					<td class="price discount">
						<input type="text" value="0">%
					</td>
					<td class="price price_out_width_discount">
						<div class="for_one"><span>205.73</span>р</div>
						<div class="for_all"><span>28332.00</span>р</div>
					</td>
					<td>
						<!-- <img src="skins/images/img_design/tc_del_service_all.png" alt="удалить все услуги"> -->
					</td>
				</tr>
				<tr class="spacer">
					<th colspan="10">&nbsp;</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				<tr class="variant">
					<td colspan="2">
					</td>
					<td>
						<div>
							<span>5.1</span>
							<span>вар 1</span>
						</div>
					</td>
					<td>
						3713272.70 
					</td>
					<td>
						<span>7</span>
					</td>
					<td colspan="4">
						Футболка женская LADY 220 с круглым вырезом, фиолетовая
					</td>
					<td>
						1400 шт
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<span>-2.66</span>%
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>					
					<td></td>
				</tr>
				<tr class="service_th">
					<th></th>
					<th></th>
					<th colspan="3"></th>
					<th>место печати</th>
					<th>цвета печати</th>
					<th>площадь</th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
					<th></th>
				</tr>
				<tr class="service">
					<td>1</td>
					<td class="alarm_clock"></td>
					<td colspan="3">Шелкография</td>
					<td class="note_title">Грудь</td>
					<td class="note_title">синий, чёрный, подложка</td>
					<td class="note_title">до 1260 см2 (А3)</td>
					<td class="comment is_full"></td>
					<td>1400 шт</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<span>-2.66</span>%
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>					
					<td></td>
				</tr>
				<tr class="service">
					<td>1</td>
					<td class="alarm_clock"></td>
					<td colspan="3">Шелкография</td>
					<td class="note_title">Грудь</td>
					<td class="note_title">синий, чёрный, подложка</td>
					<td class="note_title">до 1260 см2 (А3)</td>
					<td class="comment"></td>
					<td>1400 шт</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<span>-2.66</span>%
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>					
					<td></td>
				</tr>
				<tr class="service">
					<td>1</td>
					<td class="alarm_clock"></td>
					<td colspan="3">Шелкография</td>
					<td class="note_title" class="note_title">Грудь</td>
					<td class="note_title" class="note_title">синий, чёрный, подложка</td>
					<td class="note_title" class="note_title">до 1260 см2 (А3)</td>
					<td class="comment"></td>
					<td>1400 шт</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>
					<td>
						<span>-2.66</span>%
					</td>
					<td>
						<div><span>205.73</span>р</div>
						<div><span>28332.00</span>р</div>
					</td>					
					<td></td>
				</tr>
			</table>
		</div>

	</div>
</div>