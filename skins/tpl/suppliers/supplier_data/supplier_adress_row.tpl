                    <tr>
                        <td>Адрес <?php echo $adress_name_arr[$adress['adress_type']]; ?></td>
                        <td>                            
                            <div class="edit_row edit_adress_row del_text" data-tableName="CLIENT_ADRES_TBL" data-editType="input" data-adress-id="<?php echo $adress['id']; ?>" data-button-name-window="save"><?php 
                                    //echo "$adress_number<br>";
                                
                                        // $adress['id'];
                                        // $adress['parent_id'];
                                        // $adress['table_name'];
                                        // $adress['adress_type'];
                                    $str = "";
                                    $str .= (!empty($adress['postal_code'])?$adress['postal_code']:'');
                                    $str .= (!empty($adress['city'])?(($str!="")?', ':'').$adress['city']:'');
                                    $str .= (!empty($adress['street'])?(($str!="")?', ':'').$adress['street']:'');
                                    $str .= (!empty($adress['house_number'])?(($str!="")?', ':'').'дом №'.$adress['house_number']:'');
                                    $str .= (!empty($adress['korpus'])?(($str!="")?', ':'').'кор. '.$adress['korpus']:'');
                                    $str .= (!empty($adress['office'])?(($str!="")?', ':'').'офис '.$adress['office']:'');
                                    $str .= (!empty($adress['liter'])?(($str!="")?', ':'').'лит.  '.$adress['liter']:''); 
                                    $str .= (!empty($adress['bilding'])?(($str!="")?', ':'').'строение '.$adress['bilding']:'');                                   
                                    $str .= (!empty($adress['note'])?'<br><span class="adress_note">'.$adress['note'].'</span>':'');
                                    echo $str;
                                    /*
                                    echo "<pre>";
                                    print_r($adress);
                                    echo "</pre>"; 
                                    */

                                ?></div>
                        </td>
                    </tr>