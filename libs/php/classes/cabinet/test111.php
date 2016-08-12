<?php
// выводит строки услуг для дизайнеров и операторов
					private function get_service_content_for_production($position, $services_arr, $html_row_1, $html_row_2){
						if(empty($this->Services_list_arr)){// если массив услуг пуст - заполняем его
							$this->Services_list_arr = $this->get_all_services_Database();
						}

						$gen_html = '';
						$n = 0;

						$service_count = count($services_arr);
						
						// перебираем услуги по позиции
						foreach ($services_arr as $key => $service) {
							// получаем  json
							$this->print_details_dop_Json = (trim($service['print_details_dop'])=="")?'{}':$service['print_details_dop'];
							// декодируем json  в массив
							$this->print_details_dop = json_decode($this->print_details_dop_Json, true);



							// получаем наименование услуги
							$this->Service_name = (isset($this->Services_list_arr[ $service['uslugi_id'] ]['name'])?$this->Services_list_arr[ $service['uslugi_id'] ]['name']:'данная услуга в базе не найдена');

							$html = '';
							$html .= ($n>0)?'<tr class="position_for_production row__'.($key+2).'" data-id="'.$position['id'].'" '.$this->open_close_tr_style.'>':'';
								// место
								

								// операция
								$html .= '<td class="show_backlight show_dialog_tz_for_production" data-id="'.$service['id'].'">';
									$html .= $this->Service_name;


									// перебираем производственные услуги к которым дизайнер/оператор будет готовить макет или дизайн
									foreach ($this->services_production as $key_production_service => $production_service) {
										$html .= '<div class="seat_number_logo">';
											$html .= 'место'.($key_production_service+1).' ('.$this->Services_list_arr[ $production_service['uslugi_id'] ]['name'].'): ';
											$html .= $production_service['logotip'];
										$html .='</div>';	
									}

									// выводим ТЗ
									//$html .= '<br>'.$service['tz'];

								$html .= '</td>';

								
								$html .= '<td class="show_backlight">';
									// подрядчик печати 	
									$html .= $position['suppliers_name'];
									// пленки / клише
									$html .= $this->get_film_and_cliches();
								$html .= '</td>';

								// // плёнки / клише
								// $html .= '<td class="show_backlight">';
								// 	$html .= $this->get_statuslist_film_photos($service['film_photos_status'],$service['id']);
								// $html .= '</td>';
								

								// дата сдачи
								$html .= '<td class="show_backlight">';
									$html .= '<span class="greyText">'.$this->Order['date_of_delivery_of_the_order'].'</span>';
								$html .= '</td>';
								
								// дата работы
								$html .= '<td class="show_backlight">';
									//$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" class="calendar_date_work">';
								$html .= '<input type="text" name="calendar_date_work"  value="'.(($service['date_work']=='00.00.0000')?'нет':$service['date_work']).'" data-id="'.$service['id'].'" disabled style="width:70px;text-align:center">';
								$html .= '</td>';

								// исполнитель услуги
								$html .= '<td class="show_backlight">';
									$html .= $this->get_production_userlist_Html($service['performer_id'],$service['id']);
								$html .= '</td>';

								// статус готовности
								$html .= '<td class="show_backlight">';
									$html .= $this->get_statuslist_uslugi_Dtabase_Html($service['uslugi_id'],$service['performer_status'],$service['id'], $service['performer']);
								$html .= '</td>';

								// // % готовности
								// $html .= '<td class="show_backlight percentage_of_readiness" contenteditable="true" data-service_id="'.$service['id'].'">';
								// 	$html .= $service['percentage_of_readiness'];
								// $html .= '</td>';
							$html .= ($n>0)?'</tr>':'';

							if($n==0){// это дополнительные колонки в уже сформированную строку
								// оборачиваем колонки в html переданный в качестве параметра
								$gen_html .= $html_row_1 . $html . $html_row_2;
							}else{
								$gen_html .= $html;
							}
							$n++;
						}
						return $gen_html ;
					}