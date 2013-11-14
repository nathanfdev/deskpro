define ->
	###
    # Description
    # -----------
    #
    # This adds an element that can be flashed from the controller when it is pinged.
    #
    # Example View
    # ------------
    # <span dp-ping-flash="order_saved">Saved</button>
    #
    # Example Controller
    # ------------------
    # someAction: ->
    #     @pingElement('order_saved')
    #
    # @see Admin_Main_Ctrl_Base.pingElement()
    ###
	Admin_Main_Directive_DpPingFlash = [ ->
		return {
			restrict: 'A',
			scope: false,
			link: (scope, element, attrs) ->
				element.addClass('dp-ping-flash')
				id = 'dp_ctrl_elemnt_ping.' + attrs['dpPingFlash'];

				scope.$watch(id, (newVal) ->
					if not newVal then return
					element.stop().fadeIn(500, ->
						window.setTimeout(->
							if (element)
								element.stop().fadeOut(400)
						, 400)
					)
				)
				return
		}
	]

	return Admin_Main_Directive_DpPingFlash