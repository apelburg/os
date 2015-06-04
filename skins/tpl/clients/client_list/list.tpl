<script type="text/javascript" src="libs/js/assosiatingScrolledTable.js"></script>       

    <?php echo $header_tbl; ?>
    <div id="scroll_container" style="background-color:#FFFFFF;">    
        <table class="<?php echo $main_tbl_class; ?>" scrolled="body" width="100%">
            <?php echo $header_tr; ?>
            <?php echo $rows; ?>
        </table>
    <!--<div style="height:14px"></div>-->
    </div>
