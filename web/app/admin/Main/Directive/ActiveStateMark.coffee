define ->
	class Admin_Main_Directive_ActiveStateMark
		construct: ->
			@restrict = 'A'

		link: (scope, element, attrs) ->
			scope.$on('dp_activeStateChange', (ev, newStateId) ->
				stateId = attrs.dpStateMark
				return if not stateId

				if newStateId.indexOf(stateId) == 0
					element.addClass('state-on')
				else
					element.removeClass('state-on')
			, true);