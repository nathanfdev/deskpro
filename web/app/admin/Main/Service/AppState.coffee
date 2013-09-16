define ->
	class AppState
		constructor: (@$rootScope, @$state) ->
			@vars = {}
			@activeState = null
			@activeStateApp = null
			@activeStateNav = null
			@activeStateList = null
			@sectionState = null

			@$rootScope.$on('$viewContentLoaded', () =>
				if @$state.current
					current_state_id = $state.current.name
					if $state.params.id
						current_state_id += '.' + $state.params.id

					@setActiveState(current_state_id)

					if @$state.current.with_list_view
						$(document.body).addClass('with-section-list')
					else
						$(document.body).removeClass('with-section-list')

					if window.parent?.DP_FRAME_OVERLAY_admin
						window.parent?.DP_FRAME_OVERLAY_admin.setHash((window.location.hash+'').substring(1))
			)

			@$rootScope.$on('$stateChangeSuccess', (event, toState, toParams, fromState, fromParams) =>
				full = toState.name

				if toParams.id?
					full += '.' + toParams.id

				@setActiveState(full)

				if @$state.current.with_list_view
					$(document.body).addClass('with-section-list')
				else
					$(document.body).removeClass('with-section-list')

				if window.parent?.DP_FRAME_OVERLAY_admin
					window.parent?.DP_FRAME_OVERLAY_admin.setHash((window.location.hash+'').substring(1))
			)

		isStateActive: (stateId) ->
			return false if not stateId or not @activeState

			stateIdRegex = '^'
			stateIdRegex += stateId.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&")
			stateIdRegex += '\\b'

			if @activeState.match(new RegExp(stateIdRegex))
				return true
			else
				return false

		setActiveState: (activeState) ->
			if @activeState == activeState
				return

			@activeState = activeState

			bits = @activeState.split('.')
			@activeStateApp = bits.shift()
			@activeStateNav = bits.shift()
			@activeStateList = bits.shift()
			@activeStatePage = null

			if bits.length
				@activeStatePage = bits.join('.')

			@$rootScope.$broadcast('dp_activeStateChange', @activeState, @activeStateApp, @activeStateNav, @activeStateList)