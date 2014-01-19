define [
	'DeskPRO/Util/Util',
	'DeskPRO/Util/Strings'
], (Util, Strings) ->
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
	DeskPRO_Directive_DpStateMark = ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				myStateId   = attrs.dpStateMark
				myStateIdRe = new RegExp(Strings.escapeRegex(myStateId))
				myStateData = if attrs.dpStateMark then scope.$eval(attrs.dpStateMark) else null

				# This sets the active state immediately on click
				# which makes the UI feel faster
				element.on('click', ->
					element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active')
					element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active')
					element.addClass('state-on active')
				)

				updateMarker = ->
					currentStateId = $state.current.name

					isOn = false
					if myStateData
						if currentStateId.match(myStateIdRe) and Util.equals(myStateData, $state.params)
							isOn = true
					else
						if $state.params.id
							currentStateId += '.' + $state.params.id
						if $state.params.type
							currentStateId += '.' + $state.params.type

						if currentStateId.match(myStateIdRe)

							###
 						# This is workaround for situations when we have both routes like 'chat.setup' and 'setup'
 						# In this case both the elements will be highlighted
 						#
 						# If you will need to understand what is done uncomment following lines of code:
 						#
 						# console.log currentStateId, myStateIdRe
 						# console.log currentStateId.split('.')[0], myStateIdRe.toString().split('.')[0]
 						# console.log myStateIdRe.toString().split('.')[0].indexOf(currentStateId.split('.')[0])
							###

							firstStateOccurrence = currentStateId.split('.')[0]
							firstRegExpOccurrence = myStateIdRe.toString().split('.')[0]
							occurrenceFound = firstRegExpOccurrence.indexOf(firstStateOccurrence)

							if occurrenceFound > -1 then isOn = true

					if isOn
						element.addClass('state-on active')
						if element.closest('[dp-nav-subnav]')
							element.closest('[dp-nav-subnav]').show().closest('li').addClass('sublist-open')
					else
						element.removeClass('state-on active')

				$rootScope.$on('$stateChangeSuccess', ->
					updateMarker()
				);

				updateMarker()
		}
	]

	return DeskPRO_Directive_DpStateMark
