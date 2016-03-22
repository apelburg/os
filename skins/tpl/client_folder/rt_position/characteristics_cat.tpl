<!-- <?php echo __FILE__; ?> -- START-->
								<strong>Характеристики изделия:</strong>
								<div class="table" id="characteristics_art">
									<div class="row">
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell"><?php
													$supplier_link = identify_supplier_href($this->position['art']);
														if(trim($supplier_link) != ''){
															echo '<a target="_blank" href="'.$supplier_link.'">Поставщик</a>&nbsp;&nbsp; ';
														}
														echo $link_of_the_site;?></div>
													<div class="cell"  id="js--edit_article" data-id="<?=$this->position['id'];?>"><?php echo $this->position['art']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Номенклатура</div>
													<div class="cell"><?php echo $this->position['name']; ?></div>
												</div>
												<div class="row">
													<div class="cell">Бренд</div>
													<div class="cell"><?php
															echo $this->info['brand']; 
														?></div>
												</div>
												<div class="row">
													<div class="cell">Резерв</div>
													<div class="cell">
														<input type="text" id="rezerv_save" data-id="<?php echo $this->position['id']; ?>" value="<?php echo base64_decode($this->position['number_rezerv']); ?>" placeholder="№ резерва">
														</div>
												</div>
											</div>
										</div>
										<div class="cell">
											<div class="table">
												<div class="row">
													<div class="cell">Цвет</div>
													<div class="cell"><?php 
													echo implode(", ", $this->color); ?></div>
												</div>
												<div class="row">
													<div class="cell">Материал</div>
													<div class="cell"><?php echo implode(", ", $this->material); ?></div>
												</div>
												<div class="row">
													<div class="cell">вид нанесения</div>
													<div class="cell"><?php echo $this->get_print_names_string(); ?></div>
												</div>
											</div>
										</div>
									</div>
								</div>								
<!-- <?php echo __FILE__; ?> -- END-->					