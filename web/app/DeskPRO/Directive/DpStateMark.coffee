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
    # You can prefix the string with a comma-separated list of target route paramters. For example, if a route
    # takes 'id' and 'type', you can specify the match param like:
    #
    #     dp-state-mark="id,type:my.example.type.123"
    #
    # And the match will be done against <route_name>.<id>.<type>
    #
    # If the params ends with a '!', such as:
    #
    #     dp-state-mark="category_title!:my.example.type.123"
    #
    # ... then the value is hashed with Strings.murmurhash3.
    #
    # Example View
    # ------------
    # <li dp-state-mark="tickets.ticket_deps">Ticket Departments</li>
    ###
	DeskPRO_Directive_DpStateMark = ['$state', ($state) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				myStateId   = attrs.dpStateMark
				currentStateVars = null
				hashParams = {}

				# Parse out comma-separated list of route params
				m = myStateId.match(/^(.*?):(.*?)$/)
				if m
					myStateId = m[2]
					currentStateVars = []
					for p in m[1].split(',')
						if p.substr(-1) == '!'
							p = p.substr(0, p.length-1)
							hashParams[p] = true
						currentStateVars.push(p)

				myStateIdRe = Strings.escapeRegex(myStateId)
				myStateIdRe1 = new RegExp('^' + myStateIdRe + '\\.') # prefix "abc.zyx."
				myStateIdRe2 = new RegExp('^' + myStateIdRe + '$')   # full   "abc.xyz.1"

				# This sets the active state immediately on click
				# which makes the UI feel faster
				element.on('click', ->
					element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active')
					element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active')
					element.addClass('state-on active')
				)

				updateMarker = ->
					checkStateId = $state.current.name
					checkStateId2 = null

					if $state.current.data?.stateMarkId
						checkStateId2 = $state.current.data.stateMarkId

					isOn = false

					for currentStateId in [checkStateId, checkStateId2]
						if not currentStateId then continue

						if currentStateVars
							for v in currentStateVars
								if $state.params[v]?
									if hashParams[v]
										currentStateId += '.' + Strings.murmurhash3($state.params[v])
									else
										currentStateId += '.' + $state.params[v]
								else
									currentStateId += '.0'
						else
							if $state.params['id']?
								currentStateId += '.' + $state.params['id']

						if currentStateId.match(myStateIdRe1) or currentStateId.match(myStateIdRe2)
							isOn = true

						if isOn
							element.addClass('state-on active')
							if element.closest('[dp-nav-subnav]')
								element.closest('[dp-nav-subnav]').show().closest('li').addClass('sublist-open')
							break
						else
							element.removeClass('state-on active')

				scope.$on('$stateChangeSuccess', ->
					updateMarker()
				);

				updateMarker()
		}
	]

	return DeskPRO_Directive_DpStateMark
