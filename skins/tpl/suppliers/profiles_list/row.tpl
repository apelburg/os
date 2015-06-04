<table class="row_tbl">
  <tr>
    <td class="name" width="25" style="padding-top:6px; border-right:none;"><input name="masterBtn" type="checkbox" value="<?php echo $item['id']; ?>"  <?php echo in_array($item['id'],$prev_checked)?'checked':''; ?>></td>
    <td class="name"  style="border-right:none;"><div><a href="#"><?php echo $item['name']; ?></a></div></td>
  </tr>
</table>