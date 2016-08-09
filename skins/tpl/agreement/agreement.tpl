<link href="<?=HOST?>/skins/css/agreement.css" rel="stylesheet" type="text/css">


<div class="agreement">
     <div class="cap" id="agreement_tools_plank">
        <table width="100%" border="0">
          <tr>

            <td class="agreement--padding-rl"></td>
            <td class="agreement--block-link-left"><!--width="167"-->
                <?php 
                    if(isset($_GET['conrtol_num']))
                    {
                    // это срабатывает когда осуществляется переход из шага выбора договора для создания спецификации, через ссылку напротив договора
                ?>
                    <button type="button" onclick="location = '?page=agreement&section=choice&client_id=<?php echo $client_id; ?>&conrtol_num=<?php echo $_GET['conrtol_num']; ?>';">закрыть</button>
                    <!--
                    <button type="button" onclick="location = '/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=agreements&client_id=<?php echo $client_id; ?>';" style="cursor:pointer;">вернуться в Договоры</button>
                    <button type="button" onclick="location = '/admin/order_manager/?page=clients&razdel=show_client_folder&sub_razdel=calculate_table&client_id=<?php echo $client_id; ?>';" style="cursor:pointer;">вернуться в РТ</button>-->
                <?php 
                    }
                    else
                    {

                       if(isset($_GET['query_num']))
                       {
                      echo '<button type="button" onclick="location = \'?page=client_folder&query_num='.$query_num.'&client_id='.$client_id.'\';" style="cursor:pointer;">РТ</button>';
                    
                       }
                     echo '<button type="button" onclick="location = \'?page=cabinet&section=requests&subsection=no_worcked_men\';">Кабинет</button>';
                       if($dateDataObj->doc_type=='spec')
                       {
                        echo '<button type="button" onclick="location = \'?page=client_folder&section=agreements&client_id='.$client_id.'\';">Договоры</button>';
                       }
                        if($dateDataObj->doc_type=='oferta')
                       {
                        echo '<button type="button" onclick="location = \'?page=client_folder&section=agreements&doc_type=oferta&client_id='.$client_id.'\';">Оферты</button>';
                       }
            
                    }
                    echo '<button type="button" onclick="location=\'?page=invoice&client_id='.$client_id.'&section=1\';">Счета</button>';
                ?>

                <!--'.$_GET['query_num'].'=10001&client_id='.$client_id.'-->
            </td>
            <td class="agreement--block-link-center">
                <button type="button" id="get_requisites" data-client_id="<?=$client_firm['id']?>" title="<?=$client_firm['company']?>" >реквизиты</button>
                <?php if($dateDataObj->doc_type=='spec'){?>
                    <button id="create_invoice" data-client_id="<?=$_GET['client_id']?>" data-agreement_id="<?=$_GET['agreement_id']?>" data-specification_num="<?=$_GET['specification_num']?>" get_ class="bg-green">Запросить счет</button>  
                <?php }else{ ?>
                    <button id="create_invoice_from_oferta" data-client_id="<?=$_GET['client_id']?>" data-oferta_id="<?=$_GET['oferta_id']?>" get_ class="bg-green">Запросить номер</button>  
                <?php } ?>                
            </td>
            <!-- <td class="agreement--block-link-center">
                <?php /*if($dateDataObj->doc_type=='spec') echo 'Договор №'.$agreement['agreement_num'].' от '.$agreement_date.' ('.fetchOneValFromAgreementTbl(array('retrieve' => 'type_ru','coll' => 'type' ,'val' => $_GET['agreement_type'])).')';*/ ?>
            </td> -->
            <td>  
             
            </td>
            <td align="right" class="agreement--block-link-right">
                <?php 
                    if($dateDataObj->doc_type=='oferta')
                    {
                    
                     $href = '?page=agreement&section=specification_editor&client_id='.$client_id.'&oferta_id='.$oferta_id.'&dateDataObj='.htmlspecialchars('{"doc_type":"oferta"}');
                    // echo $href;
                ?>
                    <button type="button" onclick="location = '?<?php echo htmlspecialchars(addOrReplaceGetOnURL('section=oferta_full_editor')); ?>';">редактировать текст Оферты</button><button type="button" onclick="location = location.pathname + '<?php echo $href; ?>';" >редактировать Оферту</button>
                <?php 
                    }
                    if($dateDataObj->doc_type=='spec' && isset($_GET['open']) && $_GET['open']== 'specification')
                    {
                ?>

                    <button type="button" onclick="location = '?<?php echo htmlspecialchars(addOrReplaceGetOnURL('section=specification_full_editor')); ?>';">редактировать текст СП</button><button type="button" onclick=" location = location.pathname + '?page=agreement&section=specification_editor&client_id=<?php echo $client_id; ?>&specification_num=<?php echo $key; ?>&agreement_id=<?php echo $agreement_id.htmlspecialchars('&dateDataObj={"doc_type":"spec"}'); ?>' ;">редактировать СП</button><?php 
                    }
                    else if($dateDataObj->doc_type=='spec'){
            
                        // if($user_status=='1' && ((boolean)$agreement['standart'])){
                       if((boolean)$agreement['standart']){
                  ?>

                    

                        <button type="button" onclick="location = '?<?php echo addOrReplaceGetOnURL('section=agreement_full_editor'); ?>';" style="cursor:pointer;">редактировать договор</button>
                <?php 
                        }
                    }
                ?><button type="button" class="bg-yellow" onclick="conv_specification.start();print_agreement();" style="cursor:pointer;">распечатать</button>&nbsp;&nbsp;&nbsp;&nbsp;
              
            </td>
            <td class="agreement--padding-rl"></td>
          </tr>
        </table>
     </div>
     <div class="agreement_field" id="agreement_blank">
    <!-- <?php //print_r($_GET); ?><br /><br />
     <?php //print_r($our_firm); ?><br /><br />
     <?php //print_r($client_firm); ?><br /><br />
    --> 
     <?php echo $agreement_content; ?>
     <?php echo $specifications; ?>
     </div>
</div>
<script type="text/javascript" src="libs/js/convert_specification_class.js"></script>
<script type="text/javascript" src="libs/js/textRedactor.js"></script>
<script type="text/javascript" src="libs/js/geometry.js"></script>
<script type="text/javascript">
   textRedactor.install(['?page=agreement&update_agreement_finally_sheet_ajax=1','?page=agreement&update_specification_common_fields_ajax=1']);
</script>

<script type="text/javascript">
    $(document).on('click', '#get_requisites', function(event) {
        var url = 'http://<?=$_SERVER['HTTP_HOST']?>/os/?page=clients&section=client_folder&subsection=client_card_table&client_id=<?=$_GET['client_id'];?>';

        var id = $(this).attr('data-client_id');
        var title = $(this).attr('title');
        $.post(url, {
            AJAX: "show_requesit",
            id:id,
            title:title
        }, function(data, textStatus, xhr) {
            standard_response_handler(data);
        },'json');  
    });

    $(document).on('click', '#create_invoice', function(e){
        var url = 'http://<?=$_SERVER['HTTP_HOST']?>/os/?page=invoice';
        var client_id = $(this).attr('data-client_id');
        var specification_num = $(this).attr('data-specification_num');
        var agreement_id = $(this).attr('data-agreement_id');
        $.post(url, {
            AJAX: "create_invoice",
            client_id:client_id,
            agreement_id:agreement_id,
            specification_num:specification_num,
            doc:'spec'
        }, function(data, textStatus, xhr) {
            standard_response_handler(data);
        },'json');
    })
    $(document).on('click', '#create_invoice_from_oferta', function(e){
        var url = 'http://<?=$_SERVER['HTTP_HOST']?>/os/?page=invoice';
        var client_id = $(this).attr('data-client_id');
        var oferta_id = $(this).attr('data-oferta_id');
        $.post(url, {
            AJAX: "create_invoice",
            client_id:client_id,
            oferta_id:oferta_id,
            doc:'oferta'
        }, function(data, textStatus, xhr) {
            standard_response_handler(data);
        },'json');  
    })
</script>