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
		@CTRL_AS   = null
		@CTRL_ID   = 'Admin_Main_Ctrl_Base'
		@CTRL_TYPE = null
		@DEPS      = []

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
			if @DEPS.indexOf('$modal') == -1
				@DEPS.push('$modal')
			if @DEPS.indexOf('$q') == -1
				@DEPS.push('$q')
			if @DEPS.indexOf('$state') == -1
				@DEPS.push('$state')

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

			@_managed_listeners = []
			@$scope.$on('$destroy', (ev) =>
				return
				return if ev.targetScope.$id != @$scope.$id

				if not @_managed_listeners.length then return
				for info in @_managed_listeners
					info.object.removeListener(info.event_name, info.fn)

				@_managed_listeners = null
			)

			@$scope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) =>
					if ev.defaultPrevented then return
					if @_state_cont_ignore
						@_state_cont_ignore = false
						return

					if not @_state_cont_go and @checkDirtyState()
						@AppState.setLoadingState('dp_section_page', false)
						ev.preventDefault();

						# - The window hash has changed at this point so we
						# need to reset it back to what it was
						# - But we want to ignore the change event next time
						# or else we'd pop-up unlimited number of boxes
						# about switching state even though we're "switching"
						# back to the currently active view
						resetHash = @$state.href(fromState, fromParams)
						@_state_cont_ignore = true
						window.location.hash = resetHash
						setTimeout(=>
							@_state_cont_ignore = false
						, 140)

						@_state_cont_state = toState.name
						@_state_cont_state_params = toParams
						@_showStateConfirmLeave()
			)

			@has_init = false
			@init()
			@has_init = true

			ret = @initialLoad()
			if ret
				ret.then(=>
					@disableViewLoadingState()
				)
			else
				@disableViewLoadingState()


		###*
		* A controller may override this method.
		*
		* Return true if the current state is dirty (unsaved). The user
		* will be asked to confirm leaving.
		*
		* @return {Boolean}
		###
		checkDirtyState: ->
			return false


		###*
    	* Set dirty state checking feature on this tab. Disabled
     	* means no dirty state checking is performed when the user
    	* tries to leave.
    	*
    	* @param {Boolean} turn_off True (default) to turn off. Pass false to turn it back on
    	###
		skipDirtyState: (turn_off = true) ->
			@_state_cont_go = turn_off


		###*
		* Show this page as "loading"
		###
		enableViewLoadingState: ->
			if not @constructor.CTRL_TYPE == 'any'
				@AppState.setLoadingState('dp_section_list', true)
				@AppState.setLoadingState('dp_section_page', true)
			else if @constructor.CTRL_TYPE == 'list'
				@AppState.setLoadingState('dp_section_list', true)
			else if @constructor.CTRL_TYPE == 'page'
				@AppState.setLoadingState('dp_section_page', true)


		###*
		* Stop the loading indicator in this pane
		###
		disableViewLoadingState: ->
			if not @constructor.CTRL_TYPE == 'any'
				@AppState.setLoadingState('dp_section_list', false)
				@AppState.setLoadingState('dp_section_page', false)
			else if @constructor.CTRL_TYPE == 'list'
				@AppState.setLoadingState('dp_section_list', false)
			else if @constructor.CTRL_TYPE == 'page'
				@AppState.setLoadingState('dp_section_page', false)


		###*
		* Controllers can implement this init() method to add custom init functionality.
		###
		init: ->
			return


		###*
		* Controllers can implement this initialLoad() method to load the data needed for a view
		###
		initialLoad: ->
			return


		###*
		* Given an error response from the server, apply it to the view. This is typically
    	* a validation error that we want to show in the form.
		###
		applyErrorResponseToView: (result) ->
			if result?.error_code != 'validation_error' then return

			error_codes = []
			error_codes.push(result.detail.code_name)

			handled_codes = []

			for own form_key, form of @$scope
				if form_key.indexOf('form_') != 0 then continue

				for own field_title, field of form
					if not field.dpServerValidationKeys? then continue
					for code in error_codes
						for check_code in field.dpServerValidationKeys
							if check_code.indexOf(code) == 0
								code_segs = code.split('.')
								last_seg = code_segs.pop();

								switch last_seg
									when 'required'
										field.$setValidity('required', false)
									else
										code_safe = code.replace(/\./g, '_')
										field.$setValidity(code_safe, false)

								handled_codes.push(code)

			if error_codes.length != handled_codes.length
				console.error("One or more unhandled errors: %o", error_codes)

		###*
		* Calls $apply on scope only if digest isn't already being processed
		###
		ngApply: (fn) ->
			if !@$scope.$$phase && !@$scope.$root.$$phase
				try
					@$scope.$apply(fn);
				catch e
					window.setTimeout(=>
						try
							@ngApply()
						catch e
							return
					, 100)


		###*
		* Configures an object for auto-release when this controller is destroyed
		*
		* @param {Admin_Main_Model_Base} obj
		###
		_configureAutoReleaseObject: (obj) ->
			if not @_autoReleaseObjects?
				@_autoReleaseObjects = []
				@$scope.$on('$destroy', =>
					for i in @_autoReleaseObjects
						i.release()
				)

			@_autoReleaseObjects.push(obj)


		###*
		* Attaches a listener to an object that will be automatically removed
    	* when this controller is destroyed.
		*
		* @param {Admin_Main_Model_Base} obj
		###
		addManagedListener: (object, event_name, fn) ->
			@_managed_listeners.push({
				object: object,
				event_name: event_name,
				fn: fn
			})

			object.addListener(event_name, fn)

		###*
		* Get the URL to the template
		*
		* @return {String}
		###
		getTemplatePath: (path) ->
			return DP_BASE_ADMIN_URL+'/load-view/' + path


		###*
		* Show an alert
		###
		_showStateConfirmLeave: ->
			parentCtrl = @
			inst = @$modal.open({
				templateUrl: @getTemplatePath('Index/modal-confirm-leavetab.html'),
				controller: ['$scope', '$modalInstance', '$state', ($scope, $modalInstance, $state) ->
					$scope.dismiss = ->
						$modalInstance.dismiss();

					$scope.continue = ->
						parentCtrl._state_cont_go = true
						$modalInstance.dismiss();
						$state.go(parentCtrl._state_cont_state, parentCtrl._state_cont_state_params)
				]
			});

			return inst

		###*
		* Show an alert
		*
    	* @param {String} message The message to show
    	* @param {String} title   The title to show
		* @return {Object}
		###
		showAlert: (message, title = 'Alert') ->
			inst = @$modal.open({
				templateUrl: @getTemplatePath('Index/modal-alert.html'),
				controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
					$scope.title   = title
					$scope.message = message

					$scope.dismiss = ->
						$modalInstance.dismiss();
				]
			});

			return inst