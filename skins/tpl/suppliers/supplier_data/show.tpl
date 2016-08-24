<script type="text/javascript" src="libs/js/js_coffee_library.js"></script>
<link href="skins/css/js_coffee_library.css" rel="stylesheet" type="text/css">
<script src="<?php  echo HOST; ?>/libs/js/supplier.js" rel="stylesheet" type="application/javascript"></script>

<!-- <?php echo __FILE__; ?> -- START-->
<div id="content_general_header">
    <table>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>Компания/контактное лицо</td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td>Телефоны:</td>
                    </tr>
                </table>
            </td>
            <td>Сайт/почта/интернет</td>
        </td>
    </td>
    </tr>
    </table>
</div>
<div id="content_general">
    <div class="white_bg">
        <?php echo $supplier_content; ?>
        <?php echo $supplier_content_contact_faces; ?>
        <?php echo $supplier_content_dop_info; ?>
        <?php echo $dialog_windows; ?>
    </div>
</div>
<!-- <?php echo __FILE__; ?> -- END-->