<table class="tbl" type="extra_panel">
    <tr>
        <td style="width: auto;">
            <div class="item_article" style="width:97%;">
                <input type="text" value="<?php echo $rt_article; ?><?php //echo $rt_id; ?>" />
            </div>
        </td>
        
        <td style="width:40px;">
            <div class="item_article_dop" style="width:40px;">
                <a id="" href="#">откл</a>
            </div>
        </td>
        <td style="width:100px;">
            <div class="item_article_dop" style="width:100px;">
                <?php echo $rt_supplier; ?>
            </div>
        </td>
        <td style="width:30px;">
            <div class="item_article_dop" style="width:30px;">
                <a id="" href="/?page=description&id=<?php echo get_base_art_id($rt_article); ?>" target="_blank" onmouseover="change_href(this);return false;"><img src="./skins/images/img_design/basic_site_link.png" border="0" /></a>
            </div>
        </td>
    </tr>
</table>     
<div class="item_name" managed="text" bd_row_id="<?php echo $rt_id; ?>" bd_field="name" type="name">
    <?php echo trim($rt_name); ?>
</div>