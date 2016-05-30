<div class="cabinet_top_menu first_line">
                <ul class="central_menu" style="padding-left: 19px;">

                    <li <?php
                    if((isset($_GET['section']) && $_GET['section'] == 'page') || $_GET['page'] =='invoice'){echo 'class="selected"';}
                    ?>>
                        <a href="<?php echo HOST; ?>/?page=invoice&section=1<?php
                            if(isset($_GET['client_id'])){
                                echo '&client_id='.$_GET['client_id'];
                            }
                         ?>">
                            <div class="border"><?=$Invoice->tabName?></div>
                        </a>
                    </li>
                </ul>
            </div>