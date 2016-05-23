<div class="cabinet_top_menu first_line">
                <ul class="central_menu" style="padding-left: 19px;">
                    <li <?php 
                    if(!isset($_GET['section']) || isset($_GET['section']) && ($_GET['section'] == 'requests' || $_GET['section'] =='rt_position')){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php echo HOST; ?>/?page=cabinet&section=requests&subsection=query_worcked_men<?php
                            if(isset($_GET['client_id'])){
                                echo '&client_id='.$_GET['client_id'];
                            }
                         ?>">
                            <div class="border">Запросы</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'business_offers'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=business_offers&query_num=<?php
                            if(isset($_GET['client_id'])){
                                echo '&client_id='.$_GET['client_id'];
                            }
                            if(isset($_GET['query_num'])){
                                echo '&query_num='.$_GET['query_num'];
                            }
                         ?>" style="color:#FFFFFF;">
                            <div class="border">Коммерческие предложения</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'agreements' 
                        && isset($_GET['doc_type']) && $_GET['doc_type'] == 'agreement'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=agreement&client_id=<?php  echo $_GET['client_id']; ?>" style="color:#FFFFFF;">
                            <div class="border">Договоры</div>
                        </a>
                    </li>
                    <li <?php 
                    if(isset($_GET['section']) && $_GET['section'] == 'agreements' 
                        && isset($_GET['doc_type']) && $_GET['doc_type'] == 'oferta'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php  echo HOST; ?>/?page=client_folder&section=agreements&doc_type=oferta&client_id=<?php  echo $_GET['client_id']; ?>" style="color:#FFFFFF;">
                            <div class="border">Оферты</div>
                        </a>
                    </li>
                    <li <?php 
                    if($_GET['section'] == 'page' || $_GET['page'] =='invoice'){echo 'class="selected"';} 
                    ?>>
                        <a href="<?php echo HOST; ?>/?page=invoice&section=1<?php
                            if(isset($_GET['client_id'])){
                                echo '&client_id='.$_GET['client_id'];
                            }
                         ?>">
                            <div class="border">Счета</div>
                        </a>
                    </li>
                </ul>
            </div>