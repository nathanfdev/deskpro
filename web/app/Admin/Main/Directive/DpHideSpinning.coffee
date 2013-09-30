define ->
	###
    # Description
    # -----------
    #
    # Check out dp-show-spinning, this is the opposite.
	###
	Admin_Main_Directive_DpHideSpinning = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				id = attrs['dpHideSpinning']
				scopeName = '_spin_els.' + id

				update = ->
					if not scope._spin_els?[id]
						element.show()
					else if scope._spin_els[id].doneTime and scope._spin_els[id].doneSpin
						element.show()
					else
						element.hide()

				update()

				scope.$watch(scopeName+'.doneSpin', ->
					update()
				)
				scope.$watch(scopeName+'.doneTime', ->
					update()
				)
		}
	]

	return Admin_Main_Directive_DpHideSpinning