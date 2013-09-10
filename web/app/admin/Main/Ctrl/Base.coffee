define ['angular', 'Admin/App'], (angular) ->
	###*
	* The base controller class is mainly to make it easier to define controllers with angular.
    *
    * At the bottom of controller files, the controllers register themselves by calling
    * the class method EXPORT_CTRL().
    *
    * EXPORT_CTRL() is pre-configured to install the controller into the Admin_App module
    * with the defined dependencies (as well as AppState and $scope which are always defined).
    *
    * Note that controllers typically *register themselves* with EXPORT_CTRL(). This is converse to
    * all other types of objects (services and directives etc) which are registered through the App
    * loader.
	###
	class Admin_Ctrl_Base
		@CTRL_AS = null
		@CTRL_ID = 'Admin_Main_Ctrl_Base'
		@DEPS    = []

		###*
		* Exports this controller to the Admin_App angular module
    	* so it can be used.
		###
		@EXPORT_CTRL: () ->
			if @DEPS.indexOf('AppState') == -1
				@DEPS.push('AppState')
			if @DEPS.indexOf('Api') == -1
				@DEPS.push('Api')
			if @DEPS.indexOf('$scope') == -1
				@DEPS.push('$scope')

			ctrl_def = @DEPS.slice(0)
			ctrl_def.push(@)
			angular.module('Admin_App').controller(@CTRL_ID, ctrl_def);
			return this


		###*
		* The constructor will assign all passed-in dependencies to class vars
		###
		constructor: (args...) ->
			if @constructor.DEPS.length != args.length
				console.error("Dependencies are not the same as passed args: %o != %o", @constructor.DEPS, args)
				return

			for arg, i in args
				arg_name = @constructor.DEPS[i]
				@[arg_name] = arg

			me = @
			for arg, i in args
				if arg._is_ds_class?
					arg.registerCtrl(@)
					@$scope.$on('$destroy', ->
						#arg.unregisterCtrl(me)
					)

			if @constructor.CTRL_AS
				@$scope[@constructor.CTRL_AS] = @

			@has_init = false
			@init()
			@has_init = true


		###*
		* Controllers can implement this init() method to add custom init functionality.
		###
		init: ->
			return


		###*
		* Calls $apply on scope only if digest isn't already being processed
		###
		ngApply: (fn) ->
			if !@$scope.$$phase && !@$scope.$root.$$phase
				@$scope.$apply(fn);