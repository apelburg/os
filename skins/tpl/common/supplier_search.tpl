

                <script type="text/javascript">
                    $(document).keydown(function(e) {
                        if(e.keyCode == 13){//enter
                                if($('#search_query').is(':focus')){
                                $('#search_query').parent().next().click();
                                }//отправка поиска на enter


                                }
                    });

                    // #search_query
                    $(function() {
                        $('#search_query').autocomplete({
                            minLength: 2,
                            source: function(request, response){
                                console.log(request)
                                $.ajax({
                                    type: "POST",
                                    dataType: "json",
                                    data:{
                                        AJAX: 'shearch_supplier_autocomlete', // показать
                                        search: request.term // поисковая фраза
                                    },
                                    success: function( data ) {
                                        response( data );

                                    }
                                });
                            },

                            select: function( event, ui ) {
                                $( "#search_query" ).val(ui.item.value);
                                $('#search_query').parent().parent().submit();
                            }
                        });

                        $( "#search_query" ).data( "ui-autocomplete" )._renderItem = function( ul, item ) { // для jquery-ui 1.10+
                            return $("<li></li>")
                                    .data("ui-autocomplete-item", item) // для jquery-ui 1.10+
                                    //.append( "<a>" + item.label + "<span> (" + item.desc + ")</span></a>" )
                                    .append( item.label )
                                    .appendTo(ul);
                        };

                    })
                </script>
                <div class="search_div">
                    <form>

                        <div class="search_cap">Поиск:</div>
                        <div class="search_field">
                            <input id="search_query" placeholder="<?php echo $searchPlaceholder; ?>" type="text" onclick="delete_alert_win();" name="search" value="<?php echo (isset($_GET['search']))? $_GET['search'] : ''; ?>">
                            <?php
                        	if(isset($_GET['search']))unset($_GET['search']);

							foreach ($_GET as $key => $value) {
                            if($key == 'suppliers'){
                                $value = '';
                            }
                            if($key == 'section'){
                                $value = 'suppliers_list';
                            }
                            echo '<input type="hidden" value="'.$value.'" name="'.$key.'">';
                            }
                            if(!isset($_GET['section'])){
                            echo '<input type="hidden" value="suppliers_list" name="section">';
                            }

                            ?>
                            <div class="undo_btn">
                                <a href="#"  onclick="return  clear_search_input();">&#215;</a>
                            </div>
                        </div>
                        <input type="submit" onclick="submit.form()" style="border: 0;
    width: 53px;
    height: 27px;" value="" class="search_button" >
                        <div class="clear_div"></div>
                    </form>
                </div>