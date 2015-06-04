<!-- begin skins/tpl/suppliers/suppliers_list/show.tpl -->  
   <script type="text/javascript" src="libs/js/assosiatingScrolledTable.js"></script>
   <script type="text/javascript" src="libs/js/supplier_card.js"></script>  
   <link href="skins/css/client_card.css" rel="stylesheet" type="text/css">     
   <div class="subjects_list">
        <?php echo $top_plank; ?>
        <?php echo $header_tbl; ?>
        <div id="scroll_container"  style="background-color:#FFFFFF">    
            <table class="<?php echo $main_tbl_class; ?>" scrolled="body">
                <?php echo $header_tr; ?>
                <?php echo $rows; ?>
            </table>
            <!--<div style="height:14px"></div>-->
        </div>
   </div> 
<!-- end skins/tpl/suppliers/suppliers_list/show.tpl -->