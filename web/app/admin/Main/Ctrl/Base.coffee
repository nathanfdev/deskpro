define ['angular', 'Admin/App'], (angular) ->
	class Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Main_Ctrl_Base'
		@MODULE_ID = 'Admin_App'
		@DEPS      = []

		@EXPORT_CTRL: () ->
			if @DEPS.indexOf('AppState') == -1
				@DEPS.push('AppState')
			if @DEPS.indexOf('$scope') == -1
				@DEPS.push('$scope')

			ctrl_def = @DEPS.slice(0)
			ctrl_def.push(@)
			return angular.module(@MODULE_ID).controller(@CTRL_ID, ctrl_def);

		constructor: (args...) ->
			if @constructor.DEPS.length != args.length
				console.error("Dependencies are not the same as passed args: %o != %o", @constructor.DEPS, args)
				return

			for arg, i in args
				arg_name = @constructor.DEPS[i]
				@[arg_name] = arg

			@has_init = false
			@baseTypeInit()
			@init()
			@has_init = true

		baseTypeInit: ->
			return

		init: ->
			return
