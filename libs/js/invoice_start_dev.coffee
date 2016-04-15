theBill = 
	self : {
		user:
			name:'',
			acceess:0
		invice_rows:{}
		},
	###
	 init
	###
	init : (u=0) ->
		# this = $('#js-main-invoice');

		
		console.log ";;"
	# создание меню
	create_top_menu:()->
		
	###
	 update
	###
	update:()->
		console.log "theBill update"
	###
	 remove
	###
	remove : () ->
		console.log "theBill remove"
		
$(document).ready(()->
	theBil.init()
)

