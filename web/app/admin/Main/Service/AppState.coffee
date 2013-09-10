define ->
	class AppState
		constructor: (@$rootScope) ->
			@vars = {}
			@activeState = null
			@activeStateApp = null
			@activeStateNav = null
			@activeStateList = null
			@sectionState = null

			@$rootScope.$on('$stateChangeSuccess', (event, toState, toParams, fromState, fromParams) =>
				full = toState.name

				if toParams.id?
					full += '.' + toParams.id

				@setActiveState(full)
			)

		setActiveState: (activeState) ->
			@activeState = activeState

			bits = @activeState.split('.')
			@activeStateApp = bits.shift()
			@activeStateNav = bits.shift()
			@activeStateList = bits.shift()
			@activeStatePage = null

			if bits.length
				@activeStatePage = bits.join('.')

			@$rootScope.$broadcast('dp_activeStateChange', @activeState, @activeStateApp, @activeStateNav, @activeStateList)