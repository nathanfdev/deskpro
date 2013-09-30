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
	Admin_Main_Directive_DpShowSpinning = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				id = attrs['dpShowSpinning']
				scopeName = '_spin_els.' + id

				update = ->
					if not scope._spin_els?[id]
						element.hide()
					else if scope._spin_els[id].doneTime and scope._spin_els[id].doneSpin
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

	return Admin_Main_Directive_DpShowSpinning