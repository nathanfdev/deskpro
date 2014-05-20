define ->
	###
    # Description
    # -----------
    #
    # Just like ng-show but works specifically on spinner IDs.
    #
    # Example
    # -------
    # <span dp-show-spinning="saving_dep" class="spinner">Saving</span>
    # <span dp-hide-spinning="saving_dep"><button>Click here to save</button></span>
    #
    # Controller:
    # @startSpinner('saving_dep')
    # ...
    # @stopSpinner('enableSpinner')
	###
	DeskPRO_Directive_DpShowSpinning = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				id = attrs['dpShowSpinning']
				scopeName = 'dp_spin_els.' + id

				update = ->
					if not scope.dp_spin_els?[id]
						element.hide()
					else if scope.dp_spin_els[id].doneTime and scope.dp_spin_els[id].doneSpin
						element.hide()
					else
						element.show()

				update()

				scope.$watch(scopeName+'.doneSpin', ->
					update()
				)
				scope.$watch(scopeName+'.doneTime', ->
					update()
				)
		}
	]

	return DeskPRO_Directive_DpShowSpinning