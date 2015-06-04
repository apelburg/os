<tr class="noselect">
    <td class="edge">&nbsp;</td>
    <td class="name"><div><a href="?page=clients&section=client_folder&subsection=calculate_table&client_id=<?php echo $item['id']; ?>" target="_blank"><?php echo $item['company']; ?></a></div></td>
    <td class="rating">
        <div style="position:relative;width:50px;">
          <div class="lower_plank" style="position:absolute;top:0px;left:0px;">
            <img src="./skins/images/img_design/suppliers_rating_plank.png"/>
          </div>
          <div class="" style="position:absolute;top:0px;left:0px;width:50px;">
                <div style="overflow:hidden;width:<?php echo ($item['rate']*12); ?>px;">
                     <img src="./skins/images/img_design/suppliers_rating_plank_on.png"/>
                </div>
          </div>
        </div>
    </td>
    <td>
        <div class="cell"><?php echo $curators; ?></div>
    </td>
    <td>
        <div class="cell">активность</div>
    </td>
    <td>
        <div class="cell"><?php echo $contacts; ?></div>
    </td>
    <td>
        <div class="cell"><?php echo $phones; ?></div>
    </td>
    <td>
        <div class="cell"><?php echo $emails; ?></div>
    </td>
    <td class="edge">&nbsp;</td>
</tr>