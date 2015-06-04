<!-- ./skins/tpl/admin/sequrity.tpl begin --> 
<table width='100%' height='100%' border='0' align='center' valign='middle'>
	<tr>
		<td align='center' valign='middle'>
		<form action='' method='POST'>
		<table cellspacing='0' cellpadding='7' border='0'>
		  <tr>
		     <td style='background-color:#D4D0C8;border-top:#C0C0C0 outset 2px; border-left:#C0C0C0 outset 2px;'>&nbsp;логин</td>
			 <td style='background-color:#D4D0C8;border-top:#C0C0C0 outset 2px; border-right:#C0C0C0 outset 2px;'>
             <input type='text' name="login">
             </td>
		  </tr>
		  <tr>
			<td style='background-color:#D4D0C8;border-bottom:#C0C0C0 outset 2px; border-left:#C0C0C0 outset 2px;'>&nbsp;пароль</td>
			<td style='background-color:#D4D0C8;border-bottom:#C0C0C0 outset 2px; border-right:#C0C0C0 outset 2px;'>
            <input type='password' name='password' value=''>
            </td>
		  </tr>
		  <tr>
		    <td><input type='hidden' name='session_id' value='<?php echo session_id(); ?>'></td>
			<td align='right'><button type='submit'>войти</button></td>
	      </tr>
        </table>
		</form>
		</td>
	</tr>
</table>
<!--  ./skins/tpl/admin/sequrity.tpl end -->