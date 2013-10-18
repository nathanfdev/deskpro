define ->
	###
    # Description
    # -----------
    #
    # This directive adds a "state-on" and "active" classname to the element when the specified
    # route section is enabled.
    #
    # Sections can be named specifically or generally:
    #
    # * tickets.ticket_deps.edit.18
    # * tickets.ticket_deps.edit
    # * tickets.ticket_deps
    # * tickets
    #
    # If you specifiy a generic state name, then all states "under" that state will cause the on-state.
    #
    # If a is three-levels deep (e.g., nav > list > edit) then the 'id' param is appended and used as the last segment.
    #
    # Example View
    # ------------
    # <li dp-state-mark="tickets.ticket_deps">Ticket Departments</li>
    ###
	Admin_Main_Directive_DpStateMark = ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->

				# This sets the active state immediately on click
				# which makes the UI feel faster
				element.on('click', ->
					element.closest('#dp_section_nav').find('.state-on').removeClass('state-on active')
					element.closest('#dp_section_list').find('.state-on').removeClass('state-on active')

					element.addClass('state-on active')
				)

				checkState = (stateId, newStateId) ->
					return if not stateId or not newStateId
					stateIdRegex = '^'
					stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
					stateIdRegex += '\\b'

					if newStateId.match(new RegExp(stateIdRegex))
						return true
					else
						return false

				if $state.current?.name
					current_state_id = $state.current.name
					if $state.params.id
						current_state_id += '.' + $state.params.id
					else if $state.params.type
						current_state_id += '.' + $state.params.type

					if checkState(attrs.dpStateMark, current_state_id)
						element.addClass('state-on active')
						element.closest('.sub-nav').show().closest('li').addClass('sublist-open')

				$rootScope.$on('dp_activeStateChange', (ev, newStateId) ->
					if checkState(attrs.dpStateMark, newStateId)
						element.addClass('state-on active')
					else
						element.removeClass('state-on active')
				, true);
		}
	]

	return Admin_Main_Directive_DpStateMark
