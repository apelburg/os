<tr class="planner_rows">
   <td>&nbsp;</td>
   <td>
      <div class="date_in_format"><?php echo $write_date_in_format; ?></div>
      <div class="time_in_format"><?php echo $write_time_in_format; ?></div>
   </td>
   <td>
      <div class="date_in_format"><?php echo @$author; ?></div>
   </td>
   <td>
      <div class="date_in_format"><?php echo $exec_date_in_format; ?></div>
      <div class="time_in_format"><?php echo $exec_time_in_format; ?></div>
   </td>
   <td><?php echo $pl_type; ?></td>
   <td><?php echo $pl_cont_face; ?></td>
   <td><?php echo $pl_plan; ?></td>
   <td colspan="2"><?php echo $pl_result; ?></td>
   <td><?php echo $pl_emotion_mark; ?></td>
<!--   <td><a href="?<?php echo addOrReplaceGetOnURL('plan_id='.$pl_id.'&set_plan_status=new') ?>">восстановить</a></td>-->
</tr>