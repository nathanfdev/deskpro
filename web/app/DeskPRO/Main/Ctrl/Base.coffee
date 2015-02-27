define ['angular'], (angular) ->
  ###*
  * The base controller class is mainly to make it easier to define controllers with angular.
    *
    * At the bottom of controller files, the controllers register themselves by calling
    * the class method EXPORT_CTRL().
    *
    * EXPORT_CTRL() is pre-configured to install the controller into the ***_App module
    * with the defined dependencies (as well as AppState and $scope which are always defined).
    *
    * Note that controllers typically *register themselves* with EXPORT_CTRL(). This is converse to
    * all other types of objects (services and directives etc) which are registered through the App
    * loader.
  ###
  class DeskPRO_Main_Ctrl_Base
    @CTRL_AS   = null
    @CTRL_ID   = 'DeskPRO_Main_Ctrl_Base'
    @DEPS      = []

    ###*
    * Exports this controller to the ***_App (where *** is name of module like Admin or Reports) angular module
      * so it can be used.
    ###
    @EXPORT_CTRL: () ->
      if @DEPS.indexOf('AppState') == -1
        @DEPS.unshift('AppState')
      if @DEPS.indexOf('Api') == -1
        @DEPS.unshift('Api')
      if @DEPS.indexOf('Growl') == -1
        @DEPS.unshift('Growl')
      if @DEPS.indexOf('$scope') == -1
        @DEPS.unshift('$scope')
      if @DEPS.indexOf('$modal') == -1
        @DEPS.unshift('$modal')
      if @DEPS.indexOf('$q') == -1
        @DEPS.unshift('$q')
      if @DEPS.indexOf('$state') == -1
        @DEPS.unshift('$state')
      if @DEPS.indexOf('$stateParams') == -1
        @DEPS.unshift('$stateParams')
      if @DEPS.indexOf('$timeout') == -1
        @DEPS.unshift('$timeout')
      if @DEPS.indexOf('DataService') == -1
        @DEPS.unshift('DataService')
      if @DEPS.indexOf('dpInterfaceTimer') == -1
        @DEPS.unshift('dpInterfaceTimer')
      if @DEPS.indexOf('$log') == -1
        @DEPS.unshift('$log')

      ctrl_def = @DEPS.slice(0)
      ctrl_def.push(@)
      if not window.DP_CTRL_REG
        window.DP_CTRL_REG = []

      window.DP_CTRL_REG.push([@CTRL_ID, ctrl_def])
      return this


    ###*
    * The constructor will assign all passed-in dependencies to class vars
    ###
    constructor: (args...) ->
      @ctrl_is_loading = true
      if @constructor.DEPS.length != args.length
        console.error("Dependencies are not the same as passed args: %o != %o", @constructor.DEPS, args)
        return

      for arg, i in args
        arg_name = @constructor.DEPS[i]
        if arg_name
          @[arg_name] = arg

      me = @
      for arg in args
        if arg and arg._is_ds_class?
          arg.registerCtrl(@)
          @$scope.$on('$destroy', ->
            #arg.unregisterCtrl(me)
          )

      if @constructor.CTRL_AS
        @$scope[@constructor.CTRL_AS] = @

      @_managed_listeners = []
      @$scope.Growl = @Growl
      @$scope._autoload_links = []
      @$scope.$on('$destroy', (ev) =>
        return
        return if ev.targetScope.$id != @$scope.$id

        if not @_managed_listeners.length then return
        for info in @_managed_listeners
          info.object.removeListener(info.event_name, info.fn)

        @_managed_listeners = null
      )

      @$scope.isStateActive = (stateId, stateParams = null) =>
        return @$state.isStateActive(stateId, stateParams)

      @$scope.state_path = (route, params = {}) =>
        return @$state.href(route, params).replace(/\?.*$/, '')

      # Allow showAlert(message, callback) to be called from code
      @$scope.showAlert = (message, fn) =>
        if message.title
          title = message.title
          message = title
        else
          title = 'Alert'

        inst = @showAlert(message, title)
        if fn
          inst.result.then(=> fn() )

      @$scope.showConfirm = (message, fnTrue) =>
        if message.title
          title = message.title
          message = title
        else
          title = 'Confirm'

        inst = @showConfirm(message, title)
        if fnTrue
          inst.result.then(=> fnTrue() )

      ###
      @$scope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) =>
        if ev.defaultPrevented then return
        if @_state_cont_ignore
          @_state_cont_ignore = false
          return

        if not @_state_cont_go and not window.DP_NO_DIRTYSTATE_CONFIRM and @isDirtyState()
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
        ###

      @$scope.dp_ctrl_elemnt_ping = {}
      @_saved_state = {}

      @has_init = false
      @init()
      @has_init = true

      @$scope.state_loading = false
      @_has_loaded = false
      ret = @initialLoad()
      me = @
      if ret
        @$scope.state_loading = true
        @dpInterfaceTimer.startControllerLoad(@)
        ret.then( =>
          @dpInterfaceTimer.endControllerLoad(@)
          @$scope.state_loading = false
          @_has_loaded = true

          if @$state.current.name.split('.').length == 2 and @$scope._autoload_links
            @$timeout(=>
              @runNextAutoload()
            )
        )
      else
        @_has_loaded = true

    ###
      # Loads the next section
      ###
    runNextAutoload: ->
      if not @$scope._autoload_links or not @$scope._autoload_links.length
        return

      @$scope._autoload_links.sort( (a, b) ->
        o1 = a.pri || 0
        o2 = b.pri || 0

        if o1 == o2
          return 0
        if o1 < o2
          return -1
        else
          return 1
      )

      for al in @$scope._autoload_links
        if not al.element.closest('body')[0]
          continue

        if al.select and not al.element.is(al.select)
          link = al.element.find(al.select).first()
        else
          link = al.element

        if not link[0]
          continue

        link.click()
        break

    ###*
    * Ping a var. This handled differently depending on which
      * directive is watching the id.
      *
      * @param {String} id
    ###
    pingElement: (id) ->
      @$scope.dp_ctrl_elemnt_ping[id] = (new Date()).getTime()


    ###
      # Enables a 'spinner' state in the view which will
      # last for at least minTime time.
      #
      # If a spinner already exists, then it will be restarted.
      #
      # @param {String} id The ID of the spinner
      # @param {Integer} minTime The min time the spinner should be visible for
      # @return {promise} A promise that resolves once the spinner stops
      ###
    startSpinner: (id = 'saving', minTime = 1050) ->
      if not @$scope.dp_spin_els then @$scope.dp_spin_els = {}

      if @$scope.dp_spin_els[id]
        @stopSpinner(id, true)

      deferred = @$q.defer()

      desc = {
        doneTime: false,
        doneSpin: false,
        setTimeoutDone: =>
          desc.doneTime = true
          if desc._timeout
            @$timeout.cancel(desc._timeout)

          if desc.doneSpin
            deferred.resolve()
        ,
        setSpinDone: =>
          desc.doneSpin = true
          if desc.doneTime
            deferred.resolve()
        ,
        _promise: deferred.promise,
        _timeout: @$timeout(=>
          desc.setTimeoutDone()
        , minTime)
      }

      @$scope.dp_spin_els[id] = desc
      return desc._promise


    ###
      # Stops a 'spinner' state in the view. This by default
      # only marks the manual spinner state as off. The timer may stil
      # be going which means the spinner will still be visible until that
      # ends too. Pass force=true to stop the spinner (disregarding the min time)
      #
      # @param {String} id The ID of the spinner
      # @param {Boolean} force True to stop the spinner even if the minTime timer is still going
    # @return {promise} A promise that resolves once the spinner stops
      ###
    stopSpinner: (id = 'saving', force = false) ->
      if not @$scope.dp_spin_els?[id]
        d = @$q.defer()
        d.resolve()
        return d.promise()

      @$scope.dp_spin_els[id].setSpinDone()

      if force
        @$scope.dp_spin_els[id].setTimeoutDone()

      return @$scope.dp_spin_els[id]._promise


    ###*
    * A controller may override this method.
    *
    * Return true if the current state is dirty (unsaved). The user
    * will be asked to confirm leaving.
    *
    * @return {Boolean}
    ###
    isDirtyState: ->
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
    * Controllers can implement this init() method to add custom init functionality.
    ###
    init: ->
      return


    ###*
    * Controllers can implement this initialLoad() method to load the data needed for a view
    ###
    initialLoad: ->
      return


    ###
      # Has the initial load finished?
      #
      # @return {Boolean}
      ###
    hasLoaded: ->
      return @_has_loaded


    ###*
    * Given an error response from the server, apply it to the view. This is typically
      * a validation error that we want to show in the form.
    ###
    applyErrorResponseToView: (result) ->

      # passed the full result object rather than just data
      if result?.data and result?.config
        result = result.data

      if result?.error_code != 'validation_error'
        return

      error_codes = []

      if result.errors?.error_codes?
        error_codes = result.errors.error_codes
      else if result.detail?.code_name?
        for code in result.detail.code_name.split(',')
          error_codes.push(code)

      console.log("applyErrorResponseToView error_codes: %o", error_codes)
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
                    if code.indexOf('.') != -1
                      code_safe = code.replace(/^.*\.(.*)$/, '$1')
                    else
                      code_safe = code
                    code_safe = code_safe.replace(/\./g, '_')
                    field.$setValidity(code_safe, false)

                handled_codes.push(code)

      if error_codes.length != handled_codes.length
        console.error("One or more unhandled errors: %o", error_codes)


    ###
      # Gets a message from a registered message.
      # Generally these are registered with the dp-message directive in a view.
      #
      # @param {String} id The message ID
      # @return {String}
      ###
    getRegisteredMessage: (id) ->
      content = @$scope?._element_messages?[id] || ''

      if _.isFunction(content)
        content = content() || ''

      return content


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
      throw new Error('getTemplatePath() method of DeskPRO base controller should be redefined in children class')

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

      if message.match(/^@[a-zA-Z0-9\._]+$/)
        message = @getRegisteredMessage(message.substr(1))

      if title and title.match(/^@[a-zA-Z0-9\._]+$/)
        title = @getRegisteredMessage(title.substr(1))

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

    ###
    # Show an confirm
    #
      # @param {String} message The message to show
      # @param {String} title   The title to show
    # @return {Object}
    ###
    showConfirm: (message, title = 'Confirm') ->

      if message.match(/^@[a-zA-Z0-9\._]+$/)
        message = @getRegisteredMessage(message.substr(1))

      if title and title.match(/^@[a-zA-Z0-9\._]+$/)
        title = @getRegisteredMessage(title.substr(1))

      inst = @$modal.open({
        templateUrl: @getTemplatePath('Index/modal-confirm.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.title   = title
          $scope.message = message

          $scope.dismiss = ->
            $modalInstance.dismiss();

          $scope.confirm = ->
            $modalInstance.close();
        ]
      });

      return inst

    getWaitEntityPromiseView: (id) ->
      return =>
        @getWaitEntityPromise(id)

    getWaitEntityPromise: (id) ->
      if not id then id = 'default'

      if not @_wait_ent_promise
        @_wait_ent_promise = {}

      if @_wait_ent_promise[id]
        return @_wait_ent_promise[id].promise

      @_wait_ent_promise[id] = @$q.defer()

      promise = @_wait_ent_promise[id].promise
      return promise

    resolveWaitEntityPromise: (id) ->
      if not id then id = 'default'

      if not @_wait_ent_promise
        @_wait_ent_promise = {}

      if not @_wait_ent_promise[id]
        @_wait_ent_promise[id] = @$q.defer()

      @_wait_ent_promise[id].resolve(true)


    ###
      # Sends an API call and handle it as a stadnard form save. This starts a
      # spinner and will handle validation_errors by applyin the error reponse to the view.
      #
      # @param {String} method   POST/PUT/DELETE (also GET, but probably never used here)
      # @param {String} url      The service to call
      # @param {Object} data     The data to send
      # @param {String} spinner_name The spinner to manage automatically
    ###
    sendFormSaveApiCall: (method, url, data, spinner_name = 'form_saving') ->
      @startSpinner(spinner_name)

      switch method.toUpperCase()
        when 'GET'     then method = 'sendGet'
        when 'POST'    then method = 'sendPostJson'
        when 'PUT'     then method = 'sendPutJson'
        when 'DELETE'  then method = 'sendDelete'
        else throw new Exception("Invalid method type")

      promise = @Api[method](url, data)
      promise.then( (res) =>
        @stopSpinner(spinner_name)
      , (res) =>
        @stopSpinner(spinner_name, true)
        if res.data?.error_code == 'validation_error'
          @applyErrorResponseToView(res.data)
      )

      return promise