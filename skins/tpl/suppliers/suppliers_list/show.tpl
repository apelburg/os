<!-- <?php echo __FILE__; ?> -- START-->

    <script type="text/javascript" src="libs/js/assosiatingScrolledTable.js"></script>
    <script type="text/javascript" src="libs/js/supplier_card.js"></script>

    <script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/js_coffee_library.js"></script>
    <script src="<?php  echo HOST; ?>/libs/js/supplier.js" rel="stylesheet" type="application/javascript"></script>

    <style type="text/css">
        .notice_window_box{margin-top:120px;}
		.window{margin:auto;width:400px;border:#888 solid 1px;text-align:center;padding:20px 20px 40px 20px;font-family:verdana;font-size:14px;border-radius: 4px;-moz-border-radius: 4px;-webkit-border-radius:4px;}
		.window h3{ color:#777 }
		.window h4{ color:#777 }
		.window a{ background-color:#ddd; text-decoration:none; color:#000; padding:2px 2px 4px 2px; border-radius: 4px;-moz-border-radius: 4px;-webkit-border-radius:4px; border:#aaa solid 1px; float:right}
	    .row{margin-left:100px;height: 36px;/*margin:4px 0px 4px 40px;padding:4px 0px 2px 0px;*/text-align: left;}
    </style>
    <link href="skins/css/client_card.css" rel="stylesheet" type="text/css">
    <div class="subjects_list">
        <?php echo $top_plank; ?>
        <?php echo $header_tbl; ?>
        <div id="scroll_container"  style="background-color:#FFFFFF">
            <table class="<?php echo $main_tbl_class; ?>" scrolled="body">
                <?php echo $header_tr; ?>
                <?php echo $rows; ?>
                <?php if(isset($notice_window)) echo $notice_window; ?>
            </table>
            <!--<div style="height:14px"></div>-->
        </div>
    </div>
<!-- <?php echo __FILE__; ?> -- END-->