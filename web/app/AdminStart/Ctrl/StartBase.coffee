define ->
	class AdminStart_Ctrl_StartBase
		@CTRL_AS   = 'Card'
		@CTRL_ID   = 'AdminStart_Ctrl_StartBase'
		@DEPS      = []

		@EXPORT_CTRL: () ->
			if @DEPS.indexOf('Api') == -1
				@DEPS.unshift('Api')
			if @DEPS.indexOf('AppState') == -1
				@DEPS.unshift('AppState')
			if @DEPS.indexOf('$scope') == -1
				@DEPS.unshift('$scope')
			if @DEPS.indexOf('$q') == -1
				@DEPS.unshift('$q')
			if @DEPS.indexOf('$timeout') == -1
				@DEPS.unshift('$timeout')

			ctrl_def = @DEPS.slice(0)
			ctrl_def.push(@)
			if not window.DP_CTRL_REG
				window.DP_CTRL_REG = []

			window.DP_CTRL_REG.push([@CTRL_ID, ctrl_def])
			return this

		constructor: (args...) ->
			@ctrl_is_loading = true
			if @constructor.DEPS.length != args.length
				console.error("Dependencies are not the same as passed args: %o != %o", @constructor.DEPS, args)
				return

			for arg, i in args
				arg_name = @constructor.DEPS[i]
				if arg_name
					@[arg_name] = arg

			if @constructor.CTRL_AS
				@$scope[@constructor.CTRL_AS] = @


			@has_init = false
			@init()
			@has_init = true