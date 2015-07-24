<?php

    // ОБЩЕЕ ПРАВИЛО БЕЗОПАСНОСТИ КОТОРОЕ ДОЛЖНО СОБЛЮДАТЬСЯ - любой раздел ОС должен загружаться через router.php
	// который должен начинаться с кода проверки прав доступа:
	//
	//      // ** БЕЗОПАСНОСТЬ **
	//      // проверяем выдан ли доступ на вход на эту страницу
	//      // если нет $ACCESS['название раздела']['access'] или она равна FALSE прерываем работу скирпта 
	//      if(!@$ACCESS['название раздела']['access']) exit($ACCESS_NOTICE);
	//      // ** БЕЗОПАСНОСТЬ **
 
	
	
	// В начале была мысль разделить данные о правах доступа и данные о структуре интерфейса для юзера находящегося в системе
   	// но потом пришла мысль что всетаки стоит их объеденить в одном месте и попробовать использовать следующую логику:
	// если юзеру что-то разрешено делать или куда-то заходить то соответсвенно ему нужно выводить соответсвующие детали 
	// интерфейса, если ему это делать нельзя значит и соответсвующие детали интерфейса не должны выводиться 
	// например список клиентов - под администратором(или каким-то другим пользователем) в списке должны быть элементы позволяющие 
	// производить удаление, соответсвенно у администратора должны быть и права на удаления - соответсвенно здесь можно проверять 
	// один и тот же источник - $ACCESS['clients']['full_clients_delete'], вобщем пока попробуем с одним массивом 
	// если будет чего-то не хватать можно добавлять элементы начинающиеся с "show'
	
	// АЛГОРИТМ выгрузки элементов интерфейса - проверяем есть ли права на выполнение операции предостваляемой данным элементом
	// то тогда выводим пример кнопки удаления клиентов if($ACCESS['clients']['full_clients_delete']){ выполняем действия по генерации и выводу }
	// АЛГОРИТМ доступа к выполнению операции - проверяем есть ли права на выполнение данной операции 
	// if($ACCESS['clients']['full_clients_delete']){ выполняем действия по удалению } 
	
	
	// ПРИ разработке ИНТЕРФЕЙСОВ разделов надо предусматривать 
	// базовый уровень интерфеса BASE_INTERFASE_LEVEL для всех - при выгрузке которого не происходит проверок на доступ
	// и расширенный EXTENDED_INTERFASE_LEVEL для юзеров с правами - выгрузка которого происходит только после проверки доступа

   
    //--------------------------------------------------------------------------\\
    //                            РАЗДАЧА ДОСТУПОВ                              \\  
	//--------------------------------------------------------------------------\\		
	 $ACCESS_NOTICE = 'доступ отсутсвует';
	                       
	//***************   ШАБЛОНЫ  ДОСТУПОВ  ***************\\
	// АДМИНИСТРАТОР			  
    $ACCESS_SHABLON[1] = array(
	                    'admin'=> array('access'=> true),
						'agreement'=> array('access'=> true),
    					'option'=> array('access'=> true),
				        'clients'=> array(
										'access'=> true,
										'full_clients_delete'=> true,
										'show___foo___'=> false
										),
					    'client_folder'=> array(
										'access'=> true,
										'show___foo___'=> false,
										'section'=> array( // 1
										    'rt'=> array('access'=>true),
											'business_offers'=> array('access'=>true),
											'agreements'=> array('access'=>true),
											'planner'=> array('access'=>true),
											'order_art_edit'=> array('access'=>true),
											'order_tbl'=> array(
												'access'=>true,
												'change_status_glob'=> array('access'=>true),
												'ttn_see'=> array('access'=>true),
												'change_status_uslug'=> array('access'=>true),
												'change_status_men'=> array('access'=>true),
												'change_status_snab'=> array('access'=>true)
												)
											)	
										),					   
				        'cabinet'=> array(
										'access'=> true,
										'section'=> array( // 1
											'important'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'all' => array('access'=>true)
													)
												),
											'requests'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'no_worcked_men' => array('access'=>true),
													'no_worcked_snab' => array('access'=>true),
													'in_work' => array('access'=>true),
													'send_to_snab' => array('access'=>true),
													'calk_snab' => array('access'=>true),
													'ready_KP' => array('access'=>true),
													'denied' => array('access'=>true),
													'all' => array('access'=>true),
													'history' => array('access'=>true)
													)
												), 
											'paperwork'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'create_spec' => array('access'=>true),
													'signed' => array('access'=>true),
													'expense' => array('access'=>true)
													)
												), 
											'orders'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'start' => array('access'=>true),
													'purchase' => array('access'=>true),
													'design' => array('access'=>true),
													'production' => array('access'=>true),
													'ready_for_shipment' => array('access'=>true),
													'paused' => array('access'=>true),
													'all' => array('access'=>true)
													)
												),
											'for_shipping'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'order_of_documents' => array('access'=>true),
													'arrange_delivery' => array('access'=>true),
													'delivery' => array('access'=>true),
													'pclosing_documents' => array('access'=>true)
													)
												),
											'closed'=> array(
												'access'=>true, 
												'subsection'=>array(  // 2
													'all' => array('access'=>true),
													)
												),
											'simples'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'all' => array('access'=>true),
													)
												)
											)
										),
					   'suppliers'=> array(
										'access'=> true,
										'full_suppliers_delete'=> true
										),
					   'samples'=> array(
										'access'=> true,
										'__foo___'=> true
										),
					   '_test_rt'=> array(
										'access'=> true,
										'__foo___'=> true
										),
					   'default'=> array(
										'access'=> true
										),
				       );
	// БУХГАЛТЕР						  
	$ACCESS_SHABLON[2] = array(
				       'clients'=> array(
										'access'=> true,
										'full_clients_delete'=> true,
										'show___foo___'=> false
										),					   				   
				       'cabinet'=> array(
										'access'=> true,
										'section'=> array( // 1
											'paperwork'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'all' => array('access'=>true)
													)
												), 
											'orders'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'in_work' => array('access'=>true),
													'paused' => array('access'=>true),
													'all' => array('access'=>true)
													)
												),
											'for_shipping'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'ready_for_shipment' => array('access'=>true),
													'otgrugen' => array('access'=>true)
													)
												),
											'closed'=> array(
												'access'=>true, 
												'subsection'=>array(  // 2
													'all' => array('access'=>true),
													)
												),
											)
										)
				       );
	// Снабжение
	$ACCESS_SHABLON[8] = array(
						'client_folder'=> array(
										'access'=> true,
										'show___foo___'=> false,
										'section'=> array( // 1
										    'rt'=> array('access'=>true),
											'business_offers'=> array('access'=>true),
											'order_art_edit'=> array('access'=>true),
											'order_tbl'=> array(
												'access'=>true,
												'ttn_see'=> array('access'=>false),												
												'change_status_glob'=> array('access'=>false),
												'change_status_uslug'=> array('access'=>false),
												'change_status_men'=> array('access'=>false),
												'change_status_snab'=> array('access'=>true)
												)
											)	
										),	
				       'cabinet'=> array(
										'access'=> true,
										'section'=> array( // 1
											'important'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'all' => array('access'=>true)
													)
												),
											'requests'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'no_worcked_snab' => array('access'=>true),
													'in_work' => array('access'=>true),
													'calk_snab' => array('access'=>true),
													'denied' => array('access'=>true),
													'paused' => array('access'=>true),
													'all' => array('access'=>true),
													'history' => array('access'=>true)
													)
												),
											'orders'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'start' => array('access'=>true),
													'purchase' => array('access'=>true),
													'design' => array('access'=>true),
													'production' => array('access'=>true),
													'ready_for_shipment' => array('access'=>true),
													'paused' => array('access'=>true),
													'all' => array('access'=>true)
													)
												),
											'closed'=> array(
												'access'=>true, 
												'subsection'=>array(  // 2
													'all' => array('access'=>true),
													)
												)
											)
											
										)
				       );
		// Снабжение
	$ACCESS_SHABLON[4] = array(
				       'cabinet'=> array(
										'access'=> true,
										'section'=> array( // 1
											'orders'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'all' => array('access'=>true)
													)
												)
											)
										)
				       );
	// МЕНЕДЖЕР					  
	$ACCESS_SHABLON[5] = array(
						'cabinet'=> array(
										'access'=> true,
										'section'=> array( // 1
											'important'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'all' => array('access'=>true)
													)
												),											
											'requests'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'no_worcked_men' => array('access'=>true),
													'in_work' => array('access'=>true),
													'in_work_snab' => array('access'=>true),
													'calk_snab' => array('access'=>true),
													'denied' => array('access'=>true),
													'paused' => array('access'=>true),
													'history' => array('access'=>true)
													)
												),
											'paperwork'=> array(
												'access'=>true,
												'subsection'=>array( // 2
													'create_spec' => array('access'=>true),
													'signed' => array('access'=>true),
													'expense' => array('access'=>true)
													)
												), 
											'orders'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'in_work' => array('access'=>true),
													'paused' => array('access'=>true),
													'all' => array('access'=>true)
													)
												),
											'for_shipping'=> array(
												'access'=>true,
												'subsection'=>array(  // 2
													'ready_for_shipment' => array('access'=>true),
													'otgrugen' => array('access'=>true)
													)
												),
											'closed'=> array(
												'access'=>true, 
												'subsection'=>array(  // 2
													'all' => array('access'=>true),
													)
												),
											)
										),
						'client_folder'=> array(
										'access'=> true,
										'show___foo___'=> false,
										'section'=> array( // 1
										    'rt'=> array('access'=>true),
											'business_offers'=> array('access'=>true),
											'order_art_edit'=> array('access'=>true),
											'order_tbl'=> array(
												'access'=>true,
												'change_status_glob'=> array('access'=>true),
												'ttn_see'=> array('access'=>true),
												'change_status_uslug'=> array('access'=>true),
												'change_status_men'=> array('access'=>true),
												'change_status_snab'=> array('access'=>true)
												)
											)	
										),
				       'clients'=> array(
										'access'=> true,
										'full_clients_delete'=> false
										),
					   'suppliers'=> array(
										'access'=> true,
										'full_suppliers_delete'=> false
										),
				       );
					   
    //--------------------------------------------------------------------------\\
    //                  ЗАГРУЗКА ПРАВ ДАННОГО ПОЛЬЗОВАТЕЛЯ                      \\  
	//--------------------------------------------------------------------------\\						   

	// загружаем в $ACCESS права соответсвующие данному в уровню доступа 
	//**
	if(isset($_SESSION['access']['access'])) $ACCESS = $ACCESS_SHABLON[$_SESSION['access']['access']];

		   
					 
			////**--**		    
			//echo'<pre>$ACCESS<br>';print_r($ACCESS); echo'<pre>'; 
			
			//**--**		    
			//echo'<pre>$_SESSION[access]<br>';print_r($_SESSION['access']); echo'<pre>'; 
?>