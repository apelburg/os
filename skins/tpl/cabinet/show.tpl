<!-- begin skins/tpl/cabinet/show.tpl -->  
<link href="./skins/css/cabinet.css" rel="stylesheet" type="text/css">
<link href="./skins/css/checkboxes.css" rel="stylesheet" type="text/css">
<link href="./skins/css/menuClick.css" rel="stylesheet" type="text/css">

<link href="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jquery_ui/jquery.datetimepicker.js"></script>
<script type="text/javascript" src="./libs/js/classes/Base64Class.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/jsCabinet.js"></script>

<!-- комментарии к запросу START -->
<link href="./skins/css/comments.css" rel="stylesheet" type="text/css">
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/comments.js"></script>
 <!-- комментарии к запросу END -->
 

<div class="table" id="cabinet">
	<div class="row">
		<div class="cell" id="cabinet_left_coll_menu">
			<?php echo (isset($CABINET->CLASS->filtres_html) && is_array($CABINET->CLASS->filtres_html))?'<div id="cabinet_filtres">Фильтры:</div>':'';?>
			<div id="cabinet_top_menu1"></div>
			<ul id="cabinet_left_menu">				
				<?php echo $CABINET->menu_left_Html; ?>
			</ul>
		</div>
		<div class="cell" id="cabinet_central_panel">
			<?php 
				if(isset($CABINET->CLASS->filtres_html) && is_array($CABINET->CLASS->filtres_html)){
					echo '<div id="cabinet_filtres_list"><ul>';
					foreach ($CABINET->CLASS->filtres_html as $key => $value) {
						echo $value;
					}					
					echo '</ul></div>';
				}
			?>
			
			<?php echo $CABINET->menu_top_center_Html; ?>
				
			<div id="cabinet_general_content">				
				<?php echo $content; ?> 				
				</table>
			</div>
		</div>
	</div>
</div>
<!-- end skins/tpl/cabinet/show.tpl -->
 
