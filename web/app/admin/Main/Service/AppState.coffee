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
				@setActiveState(toState.name)
			)

		setActiveState: (activeState) ->
			@activeState = activeState

			bits = @activeState.split('.')
			@activeStateApp = bits.shift()
			@activeStateNav = bits.shift()
			@activeStateList = bits.shift()

			@$rootScope.$broadcast('dp_activeStateChange', @activeState, @activeStateApp, @activeStateNav, @activeStateList)