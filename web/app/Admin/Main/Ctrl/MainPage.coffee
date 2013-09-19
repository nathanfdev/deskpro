define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
	class Admin_Main_Ctrl_MainPage extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_MainPage'
		@DEPS      = ['$rootScope', 'AppState']

		init: ->
			@$scope.loading = {
				dp_section_page: false,
				dp_section_list: false
			}

			@AppState.addListener('statechange_dp_section_page', (val) =>
				@$scope.loading.dp_section_page = val
			)
			@AppState.addListener('statechange_dp_section_list', (val) =>
				@$scope.loading.dp_section_list = val
			)

			@$rootScope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) =>
				if ev.defaultPrevented then return

				id_segs = toState.name.split('.')
				if id_segs.length < 2 then return

				to_group = id_segs.shift()
				to_list  = id_segs.shift()
				to_page  = id_segs.shift()

				if toParams.id?
					to_page += '.' + toParams.id

				from_group = from_list = from_page = null

				if fromState
					id_segs = fromState.name.split('.')
					if id_segs.length >= 2
						from_group = id_segs.shift()
						from_list  = id_segs.shift()
						from_page  = id_segs.shift()

						if fromParams.id?
							from_page += '.' + fromParams.id

				if to_page and to_page != from_page
					@AppState.setLoadingState('dp_section_page', true)

				if to_list and to_list != from_list
					@AppState.setLoadingState('dp_section_list', true)
			)

	Admin_Main_Ctrl_MainPage.EXPORT_CTRL()