define ->
	class Admin_Main_Directive_ActiveStateMark
		construct: ->
			@restrict = 'A'

		link: (scope, element, attrs) ->
			scope.$on('dp_activeStateChange', (ev, newStateId) ->
				stateId = attrs.dpStateMark
				return if not stateId

				stateIdRegex = '^'
				stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
				stateIdRegex += '\\b'

				if newStateId.match(new RegExp(stateIdRegex))
					element.addClass('state-on')
				else
					element.removeClass('state-on')
			, true);