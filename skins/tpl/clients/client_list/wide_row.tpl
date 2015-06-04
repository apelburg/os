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
    <td class="<?php echo $curators_class; ?>">
        <div <?php echo $curators_click; ?> class="cell"><?php echo $curators; ?></div>
        <?php echo $curators_full_list; ?>
    </td>
    <td class="activity">
        <div class="cell">активность</div>
    </td>
    <td class="<?php echo $contacts_class; ?>">
        <div <?php echo $contacts_click; ?> class="cell"><?php echo $contacts; ?></div>
        <?php echo $contacts_full_list; ?>
    </td>
    <td class="<?php echo $phones_class; ?>">
        <div <?php echo $phones_click; ?> class="cell"><?php echo $phones; ?></div>
        <?php echo $phones_full_list; ?>
    </td>
    <td class="<?php echo $emails_class; ?>">
        <div <?php echo $emails_click; ?> class="cell"><?php echo $emails; ?></div>
        <?php echo $emails_full_list; ?>
    </td>
    <td class="edge">&nbsp;</td>
</tr>