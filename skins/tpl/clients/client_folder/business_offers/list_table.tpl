<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/Base64Class.js"></script>
<script type="text/javascript" src="<?php  echo HOST; ?>/libs/js/classes/kpManagerClass.js"></script>
<script type="text/javascript">
   tableDataManager.url = '?page=clients&section=client_folder&subsection=calculate_table&client_id=<?php echo $client_id; ?>';
</script>
<table class="clients_common_output_table"  tbl="managed">
    <tr class="header">
        <td style="width:  7px;border:none;">&nbsp;</td>
        <td style="width:90px;border-left:none;">Дата созадания</td>
        <td style="width:90px;">№ запроса</td>
        <td style="width:300px;">Краткое описание</td>
        <td style="width:180px;">Для контактного лица:</td>
        <td style="width:250px;">Действия</td>
        <td style="width:auto;">Комментарии</td>
        <td style="width:110px;">Дата отправки</td>
        <td style="width:60px;border-right:none;">Удалить</td>
        <td style="width:  7px;border:none;">&nbsp;</td>
    </tr>
    <tr>
        <td  class="interval" colspan="9"></td>
    </tr>
    <?php  echo $rows; ?>
    <tr>
        <td  class="interval" colspan="9"></td>
    </tr>
</table>