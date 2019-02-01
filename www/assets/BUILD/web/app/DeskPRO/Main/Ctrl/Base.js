define(['angular', 'underscore'], function(angular, _) {
  /**
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
  */
  class DeskPRO_Main_Ctrl_Base {
    static initClass() {
      this.CTRL_AS   = null;
      this.CTRL_ID   = 'DeskPRO_Main_Ctrl_Base';
      this.DEPS      = ['$rootScope'];
    }

    /**
     * Exports this controller to the ***_App (where *** is name of module like Admin or Reports) angular module
     * so it can be used.
     */
    static EXPORT_CTRL() {
      if (this.DEPS.indexOf('AppState') === -1) {
        this.DEPS.unshift('AppState');
      }
      if (this.DEPS.indexOf('Api') === -1) {
        this.DEPS.unshift('Api');
      }
      if (this.DEPS.indexOf('Api2') === -1) {
        this.DEPS.unshift('Api2');
      }
      if (this.DEPS.indexOf('Growl') === -1) {
        this.DEPS.unshift('Growl');
      }
      if (this.DEPS.indexOf('$scope') === -1) {
        this.DEPS.unshift('$scope');
      }
      if (this.DEPS.indexOf('$modal') === -1) {
        this.DEPS.unshift('$modal');
      }
      if (this.DEPS.indexOf('$q') === -1) {
        this.DEPS.unshift('$q');
      }
      if (this.DEPS.indexOf('$state') === -1) {
        this.DEPS.unshift('$state');
      }
      if (this.DEPS.indexOf('$stateParams') === -1) {
        this.DEPS.unshift('$stateParams');
      }
      if (this.DEPS.indexOf('$timeout') === -1) {
        this.DEPS.unshift('$timeout');
      }
      if (this.DEPS.indexOf('DataService') === -1) {
        this.DEPS.unshift('DataService');
      }
      if (this.DEPS.indexOf('dpInterfaceTimer') === -1) {
        this.DEPS.unshift('dpInterfaceTimer');
      }
      if (this.DEPS.indexOf('$log') === -1) {
        this.DEPS.unshift('$log');
      }

      const ctrl_def = this.DEPS.slice(0);
      ctrl_def.push(this);
      if (!window.DP_CTRL_REG) {
        window.DP_CTRL_REG = [];
      }

      window.DP_CTRL_REG.push([this.CTRL_ID, ctrl_def]);
      return this;
    }


    /**
     * The constructor will assign all passed-in dependencies to class vars
     */
    constructor(...args) {
      let arg;
      this.ctrl_is_loading = true;
      if (this.constructor.DEPS.length !== args.length) {
        console.error("Dependencies are not the same as passed args: %o != %o", this.constructor.DEPS, args);
        return;
      }

      for (let i = 0; i < args.length; i++) {
        arg = args[i];
        const arg_name = this.constructor.DEPS[i];
        if (arg_name) {
          this[arg_name] = arg;
        }
      }

      let me = this;
      for (arg of Array.from(args)) {
        if (arg && (arg._is_ds_class != null)) {
          arg.registerCtrl(this);
          this.$scope.$on('$destroy', function() {}
            //arg.unregisterCtrl(me)
          );
        }
      }

      if (this.constructor.CTRL_AS) {
        this.$scope[this.constructor.CTRL_AS] = this;
      }

      this._managed_listeners = [];
      this.$scope.Growl = this.Growl;
      this.$scope._autoload_links = [];
      this.$scope.$on('$destroy', ev => {
        return;
        if (ev.targetScope.$id !== this.$scope.$id) { return; }

        if (!this._managed_listeners.length) { return; }
        for (let info of Array.from(this._managed_listeners)) {
          info.object.removeListener(info.event_name, info.fn);
        }

        return this._managed_listeners = null;
      });

      this.$scope.isStateActive = (stateId, stateParams = null) => {
        if ((this.$state.isStateActive == null)) { return false; }
        return this.$state.isStateActive(stateId, stateParams);
      };

      this.$scope.state_path = (route, params) => {
        if (params == null) { params = {}; }
        return this.$state.href(route, params).replace(/\?.*$/, '');
      };

      // Allow showAlert(message, callback) to be called from code
      this.$scope.showAlert = (message, fn) => {
        let title;
        if (message.title) {
          ({ title } = message);
          message = title;
        } else {
          title = 'Alert';
        }

        const inst = this.showAlert(message, title);
        if (fn) {
          return inst.result.then(() => fn());
        }
      };

      this.$scope.showConfirm = (message, fnTrue) => {
        let title;
        if (message.title) {
          ({ title } = message);
          message = title;
        } else {
          title = 'Confirm';
        }

        const inst = this.showConfirm(message, title);
        if (fnTrue) {
          return inst.result.then(() => fnTrue());
        }
      };


      this.$scope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) => {
        if (ev.defaultPrevented) { return; }
        if (this._state_cont_ignore) {
          this._state_cont_ignore = false;
          return;
        }

        if (!this._state_cont_go && !window.DP_NO_DIRTYSTATE_CONFIRM && this.isDirtyState()) {
          ev.preventDefault();

          // - The window hash has changed at this point so we
          // need to reset it back to what it was
          // - But we want to ignore the change event next time
          // or else we'd pop-up unlimited number of boxes
          // about switching state even though we're "switching"
          // back to the currently active view
          const resetHash = this.$state.href(fromState, fromParams);
          this._state_cont_ignore = true;
          window.location.hash = resetHash;
          setTimeout(() => {
              return this._state_cont_ignore = false;
            }
            , 140);

          this._state_cont_state = toState.name;
          this._state_cont_state_params = toParams;
          return this._showStateConfirmLeave();
        }
      });


      this.$scope.dp_ctrl_elemnt_ping = {};
      this._saved_state = {};

      this.has_init = false;
      this.init();
      this.has_init = true;

      this.$scope.state_loading = false;
      this._has_loaded = false;
      const ret = this.initialLoad();
      me = this;
      if (ret && (ret.then != null)) {
        this.$scope.state_loading = true;
        this.dpInterfaceTimer.startControllerLoad(this);
        ret.then( () => {
          this.dpInterfaceTimer.endControllerLoad(this);
          this.$scope.state_loading = false;
          this._has_loaded = true;

          if ((this.$state.current.name.split('.').length === 2) && this.$scope._autoload_links) {
            return this.$timeout(() => {
              return this.runNextAutoload();
            });
          }
        });
      } else {
        this._has_loaded = true;
      }
    }

    /*
      * Loads the next section
      */
    runNextAutoload() {
      if (!this.$scope._autoload_links || !this.$scope._autoload_links.length) {
        return;
      }

      this.$scope._autoload_links.sort( function(a, b) {
        const o1 = a.pri || 0;
        const o2 = b.pri || 0;

        if (o1 === o2) {
          return 0;
        }
        if (o1 < o2) {
          return -1;
        } else {
          return 1;
        }
      });

      return (() => {
        const result = [];
        for (let al of Array.from(this.$scope._autoload_links)) {
          var link;
          if (!al.element.closest('body')[0]) {
            continue;
          }

          if (al.select && !al.element.is(al.select)) {
            link = al.element.find(al.select).first();
          } else {
            link = al.element;
          }

          if (!link[0]) {
            continue;
          }

          link.click();
          break;
        }
        return result;
      })();
    }

    /**
     * Ping a var. This handled differently depending on which
     * directive is watching the id.
     *
     * @param {String} id
     */
    pingElement(id) {
      return this.$scope.dp_ctrl_elemnt_ping[id] = (new Date()).getTime();
    }


    /*
      * Enables a 'spinner' state in the view which will
      * last for at least minTime time.
      *
      * If a spinner already exists, then it will be restarted.
      *
      * @param {String} id The ID of the spinner
      * @param {Integer} minTime The min time the spinner should be visible for
      * @return {promise} A promise that resolves once the spinner stops
      */
    startSpinner(id, minTime) {
      if (id == null) { id = 'saving'; }
      if (minTime == null) { minTime = 1050; }
      if (!this.$scope.dp_spin_els) { this.$scope.dp_spin_els = {}; }

      if (this.$scope.dp_spin_els[id]) {
        this.stopSpinner(id, true);
      }

      const deferred = this.$q.defer();

      var desc = {
        doneTime: false,
        doneSpin: false,
        setTimeoutDone: () => {
          desc.doneTime = true;
          if (desc._timeout) {
            this.$timeout.cancel(desc._timeout);
          }

          if (desc.doneSpin) {
            return deferred.resolve();
          }
        }
        ,
        setSpinDone() {
          desc.doneSpin = true;
          if (desc.doneTime) {
            return deferred.resolve();
          }
        }
        ,
        _promise: deferred.promise,
        _timeout: this.$timeout(() => desc.setTimeoutDone()
          , minTime)
      };

      this.$scope.dp_spin_els[id] = desc;
      return desc._promise;
    }


    /*
      * Stops a 'spinner' state in the view. This by default
      * only marks the manual spinner state as off. The timer may stil
      * be going which means the spinner will still be visible until that
      * ends too. Pass force=true to stop the spinner (disregarding the min time)
      *
      * @param {String} id The ID of the spinner
      * @param {Boolean} force True to stop the spinner even if the minTime timer is still going
    * @return {promise} A promise that resolves once the spinner stops
      */
    stopSpinner(id, force) {
      if (id == null) { id = 'saving'; }
      if (force == null) { force = false; }
      if (!(this.$scope.dp_spin_els != null ? this.$scope.dp_spin_els[id] : undefined)) {
        const d = this.$q.defer();
        d.resolve();
        return d.promise();
      }

      this.$scope.dp_spin_els[id].setSpinDone();

      if (force) {
        this.$scope.dp_spin_els[id].setTimeoutDone();
      }

      return this.$scope.dp_spin_els[id]._promise;
    }


    /**
     * A controller may override this method.
     *
     * Return true if the current state is dirty (unsaved). The user
     * will be asked to confirm leaving.
     *
     * @return {Boolean}
     */
    isDirtyState() {
      return false;
    }


    /**
     * Set dirty state checking feature on this tab. Disabled
     * means no dirty state checking is performed when the user
     * tries to leave.
     *
     * @param {Boolean} turn_off True (default) to turn off. Pass false to turn it back on
     */
    skipDirtyState(turn_off) {
      if (turn_off == null) { turn_off = true; }
      return this._state_cont_go = turn_off;
    }

    /**
     * Controllers can implement this init() method to add custom init functionality.
     */
    init() {
    }


    /**
     * Controllers can implement this initialLoad() method to load the data needed for a view
     */
    initialLoad() {
    }


    /*
      * Has the initial load finished?
      *
      * @return {Boolean}
      */
    hasLoaded() {
      return this._has_loaded;
    }


    /**
     * Given an error response from the server, apply it to the view. This is typically
     * a validation error that we want to show in the form.
     */
    applyErrorResponseToView(result) {

      // passed the full result object rather than just data
      let code;
      if ((result != null ? result.data : undefined) && (result != null ? result.config : undefined)) {
        result = result.data;
      }

      if ((result != null ? result.error_code : undefined) !== 'validation_error') {
        return;
      }

      let error_codes = [];

      if ((result.errors != null ? result.errors.error_codes : undefined) != null) {
        ({ error_codes } = result.errors);
      } else if ((result.detail != null ? result.detail.code_name : undefined) != null) {
        for (code of Array.from(result.detail.code_name.split(','))) {
          error_codes.push(code);
        }
      }

      console.log("applyErrorResponseToView error_codes: %o", error_codes);
      const handled_codes = [];

      for (let form_key of Object.keys(this.$scope || {})) {
        const form = this.$scope[form_key];
        if (form_key.indexOf('form_') !== 0) { continue; }

        for (let field_title of Object.keys(form || {})) {

          const field = form[field_title];
          if ((field.dpServerValidationKeys == null)) { continue; }
          for (code of Array.from(error_codes)) {
            for (let check_code of Array.from(field.dpServerValidationKeys)) {
              if (check_code.indexOf(code) === 0) {
                var code_safe;
                const code_segs = code.split('.');
                const last_seg = code_segs.pop();

                switch (last_seg) {
                  case 'required':
                    field.$setValidity('required', false);
                    break;
                  default:
                    if (code.indexOf('.') !== -1) {
                      code_safe = code.replace(/^.*\.(.*)$/, '$1');
                    } else {
                      code_safe = code;
                    }
                    code_safe = code_safe.replace(/\./g, '_');
                    field.$setValidity(code_safe, false);
                }

                handled_codes.push(code);
              }
            }
          }
        }
      }

      if (error_codes.length !== handled_codes.length) {
        return console.error("One or more unhandled errors: %o", error_codes);
      }
    }


    /*
      * Gets a message from a registered message.
      * Generally these are registered with the dp-message directive in a view.
      *
      * @param {String} id The message ID
      * @return {String}
      */
    getRegisteredMessage(id) {
      let content = __guard__(this.$scope != null ? this.$scope._element_messages : undefined, x => x[id]) || '';

      if (_.isFunction(content)) {
        content = content() || '';
      }

      return content;
    }


    /**
     * Calls $apply on scope only if digest isn't already being processed
     */
    ngApply(fn) {
      if (!this.$scope.$$phase && !this.$scope.$root.$$phase) {
        try {
          return this.$scope.$apply(fn);
        } catch (e) {
          return window.setTimeout(() => {
              try {
                return this.ngApply();
              } catch (e) {
                return;
              }
            }
            , 100);
        }
      }
    }


    /**
     * Configures an object for auto-release when this controller is destroyed
     *
     * @param {Admin_Main_Model_Base} obj
     */
    _configureAutoReleaseObject(obj) {
      if ((this._autoReleaseObjects == null)) {
        this._autoReleaseObjects = [];
        this.$scope.$on('$destroy', () => {
          return Array.from(this._autoReleaseObjects).map((i) =>
            i.release());
        });
      }

      return this._autoReleaseObjects.push(obj);
    }


    /**
     * Attaches a listener to an object that will be automatically removed
     * when this controller is destroyed.
     *
     * @param {Admin_Main_Model_Base} obj
     */
    addManagedListener(object, event_name, fn) {
      this._managed_listeners.push({
        object,
        event_name,
        fn
      });

      return object.addListener(event_name, fn);
    }

    /**
     * Get the URL to the template
     *
     * @return {String}
     */
    getTemplatePath(path) {
      throw new Error('getTemplatePath() method of DeskPRO base controller should be redefined in children class');
    }

    /**
     * Show an alert
     */
    _showStateConfirmLeave() {
      const parentCtrl = this;
      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Index/modal-confirm-leavetab.html'),
        controller: ['$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.continue = function() {
            parentCtrl._state_cont_go = true;
            $modalInstance.dismiss();
            return $state.go(parentCtrl._state_cont_state, parentCtrl._state_cont_state_params);
          };
        }
        ]
      });

      return inst;
    }

    /**
     * Show an alert
     *
     * @param {String} message The message to show
     * @param {String} title   The title to show
     * @return {Object}
     */
    showAlert(message, title) {

      if (title == null) { title = 'Alert'; }
      if (message.match(/^@[a-zA-Z0-9\._]+$/)) {
        message = this.getRegisteredMessage(message.substr(1));
      }

      if (title && title.match(/^@[a-zA-Z0-9\._]+$/)) {
        title = this.getRegisteredMessage(title.substr(1));
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Index/modal-alert.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.title   = title;
          $scope.message = message;

          return $scope.dismiss = () => $modalInstance.dismiss();
        }
        ]
      });

      return inst;
    }

    /*
    * Show an confirm
    *
      * @param {String} message The message to show
      * @param {String} title   The title to show
    * @return {Object}
    */
    showConfirm(message, title) {

      if (title == null) { title = 'Confirm'; }
      if (message.match(/^@[a-zA-Z0-9\._]+$/)) {
        message = this.getRegisteredMessage(message.substr(1));
      }

      if (title && title.match(/^@[a-zA-Z0-9\._]+$/)) {
        title = this.getRegisteredMessage(title.substr(1));
      }

      const inst = this.$modal.open({
        templateUrl: this.getTemplatePath('Index/modal-confirm.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.title   = title;
          $scope.message = message;

          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.confirm = () => $modalInstance.close();
        }
        ]
      });

      return inst;
    }

    getWaitEntityPromiseView(id) {
      return () => {
        return this.getWaitEntityPromise(id);
      };
    }

    getWaitEntityPromise(id) {
      if (!id) { id = 'default'; }

      if (!this._wait_ent_promise) {
        this._wait_ent_promise = {};
      }

      if (this._wait_ent_promise[id]) {
        return this._wait_ent_promise[id].promise;
      }

      this._wait_ent_promise[id] = this.$q.defer();

      const { promise } = this._wait_ent_promise[id];
      return promise;
    }

    resolveWaitEntityPromise(id) {
      if (!id) { id = 'default'; }

      if (!this._wait_ent_promise) {
        this._wait_ent_promise = {};
      }

      if (!this._wait_ent_promise[id]) {
        this._wait_ent_promise[id] = this.$q.defer();
      }

      return this._wait_ent_promise[id].resolve(true);
    }


    /*
      * Sends an API call and handle it as a stadnard form save. This starts a
      * spinner and will handle validation_errors by applyin the error reponse to the view.
      *
      * @param {String} method   POST/PUT/DELETE (also GET, but probably never used here)
      * @param {String} url      The service to call
      * @param {Object} data     The data to send
      * @param {String} spinner_name The spinner to manage automatically
    */
    sendFormSaveApiCall(method, url, data, spinner_name) {
      if (spinner_name == null) { spinner_name = 'form_saving'; }
      this.startSpinner(spinner_name);

      switch (method.toUpperCase()) {
        case 'GET':     method = 'sendGet'; break;
        case 'POST':    method = 'sendPostJson'; break;
        case 'PUT':     method = 'sendPutJson'; break;
        case 'DELETE':  method = 'sendDelete'; break;
        default: throw new Exception("Invalid method type");
      }

      const promise = this.Api[method](url, data);
      promise.then( res => {
          return this.stopSpinner(spinner_name);
        }
        , res => {
          this.stopSpinner(spinner_name, true);
          if ((res.data != null ? res.data.error_code : undefined) === 'validation_error') {
            return this.applyErrorResponseToView(res.data);
          }
        });

      return promise;
    }
  }
  DeskPRO_Main_Ctrl_Base.initClass();
  return DeskPRO_Main_Ctrl_Base;
});

function __guard__(value, transform) {
  return (typeof value !== 'undefined' && value !== null) ? transform(value) : undefined;
}