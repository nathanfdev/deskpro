(function() {
  var __slice = [].slice,
    __hasProp = {}.hasOwnProperty;

  define(['angular'], function(angular) {

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
    var DeskPRO_Main_Ctrl_Base;
    return DeskPRO_Main_Ctrl_Base = (function() {
      DeskPRO_Main_Ctrl_Base.CTRL_AS = null;

      DeskPRO_Main_Ctrl_Base.CTRL_ID = 'DeskPRO_Main_Ctrl_Base';

      DeskPRO_Main_Ctrl_Base.DEPS = [];


      /**
      		* Exports this controller to the ***_App (where *** is name of module like Admin or Reports) angular module
        	* so it can be used.
       */

      DeskPRO_Main_Ctrl_Base.EXPORT_CTRL = function() {
        var ctrl_def;
        if (this.DEPS.indexOf('AppState') === -1) {
          this.DEPS.unshift('AppState');
        }
        if (this.DEPS.indexOf('Api') === -1) {
          this.DEPS.unshift('Api');
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
        ctrl_def = this.DEPS.slice(0);
        ctrl_def.push(this);
        if (!window.DP_CTRL_REG) {
          window.DP_CTRL_REG = [];
        }
        window.DP_CTRL_REG.push([this.CTRL_ID, ctrl_def]);
        return this;
      };


      /**
      		* The constructor will assign all passed-in dependencies to class vars
       */

      function DeskPRO_Main_Ctrl_Base() {
        var arg, arg_name, args, i, me, ret, _i, _j, _len, _len1;
        args = 1 <= arguments.length ? __slice.call(arguments, 0) : [];
        this.ctrl_is_loading = true;
        if (this.constructor.DEPS.length !== args.length) {
          console.error("Dependencies are not the same as passed args: %o != %o", this.constructor.DEPS, args);
          return;
        }
        for (i = _i = 0, _len = args.length; _i < _len; i = ++_i) {
          arg = args[i];
          arg_name = this.constructor.DEPS[i];
          if (arg_name) {
            this[arg_name] = arg;
          }
        }
        me = this;
        for (_j = 0, _len1 = args.length; _j < _len1; _j++) {
          arg = args[_j];
          if (arg && (arg._is_ds_class != null)) {
            arg.registerCtrl(this);
            this.$scope.$on('$destroy', function() {});
          }
        }
        if (this.constructor.CTRL_AS) {
          this.$scope[this.constructor.CTRL_AS] = this;
        }
        this._managed_listeners = [];
        this.$scope._autoload_links = [];
        this.$scope.$on('$destroy', (function(_this) {
          return function(ev) {
            var info, _k, _len2, _ref;
            return;
            if (ev.targetScope.$id !== _this.$scope.$id) {
              return;
            }
            if (!_this._managed_listeners.length) {
              return;
            }
            _ref = _this._managed_listeners;
            for (_k = 0, _len2 = _ref.length; _k < _len2; _k++) {
              info = _ref[_k];
              info.object.removeListener(info.event_name, info.fn);
            }
            return _this._managed_listeners = null;
          };
        })(this));
        this.$scope.isStateActive = (function(_this) {
          return function(stateId, stateParams) {
            if (stateParams == null) {
              stateParams = null;
            }
            return _this.$state.isStateActive(stateId, stateParams);
          };
        })(this);
        this.$scope.state_path = (function(_this) {
          return function(route, params) {
            if (params == null) {
              params = {};
            }
            return _this.$state.href(route, params);
          };
        })(this);
        this.$scope.showAlert = (function(_this) {
          return function(message, fn) {
            var inst, title;
            if (message.title) {
              title = message.title;
              message = title;
            } else {
              title = 'Alert';
            }
            inst = _this.showAlert(message, title);
            if (fn) {
              return inst.result.then(function() {
                return fn();
              });
            }
          };
        })(this);
        this.$scope.showConfirm = (function(_this) {
          return function(message, fnTrue) {
            var inst, title;
            if (message.title) {
              title = message.title;
              message = title;
            } else {
              title = 'Confirm';
            }
            inst = _this.showConfirm(message, title);
            if (fnTrue) {
              return inst.result.then(function() {
                return fnTrue();
              });
            }
          };
        })(this);

        /*
        			@$scope.$on('$stateChangeStart', (ev, toState, toParams, fromState, fromParams) =>
        				if ev.defaultPrevented then return
        				if @_state_cont_ignore
        					@_state_cont_ignore = false
        					return
        
        				if not @_state_cont_go and not window.DP_NO_DIRTYSTATE_CONFIRM and @isDirtyState()
        					ev.preventDefault();
        
        					 * - The window hash has changed at this point so we
        					 * need to reset it back to what it was
        					 * - But we want to ignore the change event next time
        					 * or else we'd pop-up unlimited number of boxes
        					 * about switching state even though we're "switching"
        					 * back to the currently active view
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
         */
        this.$scope.dp_ctrl_elemnt_ping = {};
        this._saved_state = {};
        this.has_init = false;
        this.init();
        this.has_init = true;
        this.$scope.state_loading = false;
        this._has_loaded = false;
        ret = this.initialLoad();
        me = this;
        if (ret) {
          this.$scope.state_loading = true;
          this.dpInterfaceTimer.startControllerLoad(this);
          ret.then((function(_this) {
            return function() {
              _this.dpInterfaceTimer.endControllerLoad(_this);
              _this.$scope.state_loading = false;
              _this._has_loaded = true;
              if (_this.$state.current.name.split('.').length === 2 && _this.$scope._autoload_links) {
                return _this.$timeout(function() {
                  return _this.runNextAutoload();
                });
              }
            };
          })(this));
        } else {
          this._has_loaded = true;
        }
      }


      /*
        	 * Loads the next section
       */

      DeskPRO_Main_Ctrl_Base.prototype.runNextAutoload = function() {
        var al, link, _i, _len, _ref, _results;
        if (!this.$scope._autoload_links || !this.$scope._autoload_links.length) {
          return;
        }
        this.$scope._autoload_links.sort(function(a, b) {
          var o1, o2;
          o1 = a.pri || 0;
          o2 = b.pri || 0;
          if (o1 === o2) {
            return 0;
          }
          if (o1 < o2) {
            return -1;
          } else {
            return 1;
          }
        });
        _ref = this.$scope._autoload_links;
        _results = [];
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          al = _ref[_i];
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
        return _results;
      };


      /**
      		* Ping a var. This handled differently depending on which
        	* directive is watching the id.
        	*
        	* @param {String} id
       */

      DeskPRO_Main_Ctrl_Base.prototype.pingElement = function(id) {
        return this.$scope.dp_ctrl_elemnt_ping[id] = (new Date()).getTime();
      };


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

      DeskPRO_Main_Ctrl_Base.prototype.startSpinner = function(id, minTime) {
        var deferred, desc;
        if (minTime == null) {
          minTime = 1050;
        }
        if (!this.$scope.dp_spin_els) {
          this.$scope.dp_spin_els = {};
        }
        if (this.$scope.dp_spin_els[id]) {
          this.stopSpinner(id, true);
        }
        deferred = this.$q.defer();
        desc = {
          doneTime: false,
          doneSpin: false,
          setTimeoutDone: (function(_this) {
            return function() {
              desc.doneTime = true;
              if (desc._timeout) {
                _this.$timeout.cancel(desc._timeout);
              }
              if (desc.doneSpin) {
                return deferred.resolve();
              }
            };
          })(this),
          setSpinDone: (function(_this) {
            return function() {
              desc.doneSpin = true;
              if (desc.doneTime) {
                return deferred.resolve();
              }
            };
          })(this),
          _promise: deferred.promise,
          _timeout: this.$timeout((function(_this) {
            return function() {
              return desc.setTimeoutDone();
            };
          })(this), minTime)
        };
        this.$scope.dp_spin_els[id] = desc;
        return desc._promise;
      };


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

      DeskPRO_Main_Ctrl_Base.prototype.stopSpinner = function(id, force) {
        var d, _ref;
        if (force == null) {
          force = false;
        }
        if (!((_ref = this.$scope.dp_spin_els) != null ? _ref[id] : void 0)) {
          d = this.$q.defer();
          d.resolve();
          return d.promise();
        }
        this.$scope.dp_spin_els[id].setSpinDone();
        if (force) {
          this.$scope.dp_spin_els[id].setTimeoutDone();
        }
        return this.$scope.dp_spin_els[id]._promise;
      };


      /**
      		* A controller may override this method.
      		*
      		* Return true if the current state is dirty (unsaved). The user
      		* will be asked to confirm leaving.
      		*
      		* @return {Boolean}
       */

      DeskPRO_Main_Ctrl_Base.prototype.isDirtyState = function() {
        return false;
      };


      /**
        	* Set dirty state checking feature on this tab. Disabled
         	* means no dirty state checking is performed when the user
        	* tries to leave.
        	*
        	* @param {Boolean} turn_off True (default) to turn off. Pass false to turn it back on
       */

      DeskPRO_Main_Ctrl_Base.prototype.skipDirtyState = function(turn_off) {
        if (turn_off == null) {
          turn_off = true;
        }
        return this._state_cont_go = turn_off;
      };


      /**
      		* Controllers can implement this init() method to add custom init functionality.
       */

      DeskPRO_Main_Ctrl_Base.prototype.init = function() {};


      /**
      		* Controllers can implement this initialLoad() method to load the data needed for a view
       */

      DeskPRO_Main_Ctrl_Base.prototype.initialLoad = function() {};


      /*
        	 * Has the initial load finished?
        	 *
        	 * @return {Boolean}
       */

      DeskPRO_Main_Ctrl_Base.prototype.hasLoaded = function() {
        return this._has_loaded;
      };


      /**
      		* Given an error response from the server, apply it to the view. This is typically
        	* a validation error that we want to show in the form.
       */

      DeskPRO_Main_Ctrl_Base.prototype.applyErrorResponseToView = function(result) {
        var check_code, code, code_safe, code_segs, error_codes, field, field_title, form, form_key, handled_codes, last_seg, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2, _ref3, _ref4;
        if ((result != null ? result.data : void 0) && (result != null ? result.config : void 0)) {
          result = result.data;
        }
        if ((result != null ? result.error_code : void 0) !== 'validation_error') {
          return;
        }
        error_codes = [];
        if (((_ref = result.errors) != null ? _ref.error_codes : void 0) != null) {
          error_codes = result.errors.error_codes;
        } else if (((_ref1 = result.detail) != null ? _ref1.code_name : void 0) != null) {
          _ref2 = result.detail.code_name.split(',');
          for (_i = 0, _len = _ref2.length; _i < _len; _i++) {
            code = _ref2[_i];
            error_codes.push(code);
          }
        }
        console.log("applyErrorResponseToView error_codes: %o", error_codes);
        handled_codes = [];
        _ref3 = this.$scope;
        for (form_key in _ref3) {
          if (!__hasProp.call(_ref3, form_key)) continue;
          form = _ref3[form_key];
          if (form_key.indexOf('form_') !== 0) {
            continue;
          }
          for (field_title in form) {
            if (!__hasProp.call(form, field_title)) continue;
            field = form[field_title];
            if (field.dpServerValidationKeys == null) {
              continue;
            }
            for (_j = 0, _len1 = error_codes.length; _j < _len1; _j++) {
              code = error_codes[_j];
              _ref4 = field.dpServerValidationKeys;
              for (_k = 0, _len2 = _ref4.length; _k < _len2; _k++) {
                check_code = _ref4[_k];
                if (check_code.indexOf(code) === 0) {
                  code_segs = code.split('.');
                  last_seg = code_segs.pop();
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
      };


      /*
        	 * Gets a message from a registered message.
        	 * Generally these are registered with the dp-message directive in a view.
        	 *
        	 * @param {String} id The message ID
        	 * @return {String}
       */

      DeskPRO_Main_Ctrl_Base.prototype.getRegisteredMessage = function(id) {
        var content, _ref;
        content = ((_ref = this.$scope) != null ? _ref._element_messages[id] : void 0) || '';
        if (_.isFunction(content)) {
          content = content() || '';
        }
        return content;
      };


      /**
      		* Calls $apply on scope only if digest isn't already being processed
       */

      DeskPRO_Main_Ctrl_Base.prototype.ngApply = function(fn) {
        var e;
        if (!this.$scope.$$phase && !this.$scope.$root.$$phase) {
          try {
            return this.$scope.$apply(fn);
          } catch (_error) {
            e = _error;
            return window.setTimeout((function(_this) {
              return function() {
                try {
                  return _this.ngApply();
                } catch (_error) {
                  e = _error;
                }
              };
            })(this), 100);
          }
        }
      };


      /**
      		* Configures an object for auto-release when this controller is destroyed
      		*
      		* @param {Admin_Main_Model_Base} obj
       */

      DeskPRO_Main_Ctrl_Base.prototype._configureAutoReleaseObject = function(obj) {
        if (this._autoReleaseObjects == null) {
          this._autoReleaseObjects = [];
          this.$scope.$on('$destroy', (function(_this) {
            return function() {
              var i, _i, _len, _ref, _results;
              _ref = _this._autoReleaseObjects;
              _results = [];
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                i = _ref[_i];
                _results.push(i.release());
              }
              return _results;
            };
          })(this));
        }
        return this._autoReleaseObjects.push(obj);
      };


      /**
      		* Attaches a listener to an object that will be automatically removed
        	* when this controller is destroyed.
      		*
      		* @param {Admin_Main_Model_Base} obj
       */

      DeskPRO_Main_Ctrl_Base.prototype.addManagedListener = function(object, event_name, fn) {
        this._managed_listeners.push({
          object: object,
          event_name: event_name,
          fn: fn
        });
        return object.addListener(event_name, fn);
      };


      /**
      		* Get the URL to the template
      		*
      		* @return {String}
       */

      DeskPRO_Main_Ctrl_Base.prototype.getTemplatePath = function(path) {
        throw new Error('getTemplatePath() method of DeskPRO base controller should be redefined in children class');
      };


      /**
      		* Show an alert
       */

      DeskPRO_Main_Ctrl_Base.prototype._showStateConfirmLeave = function() {
        var inst, parentCtrl;
        parentCtrl = this;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Index/modal-confirm-leavetab.html'),
          controller: [
            '$scope', '$modalInstance', '$state', function($scope, $modalInstance, $state) {
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope["continue"] = function() {
                parentCtrl._state_cont_go = true;
                $modalInstance.dismiss();
                return $state.go(parentCtrl._state_cont_state, parentCtrl._state_cont_state_params);
              };
            }
          ]
        });
        return inst;
      };


      /**
      		* Show an alert
      		*
        	* @param {String} message The message to show
        	* @param {String} title   The title to show
      		* @return {Object}
       */

      DeskPRO_Main_Ctrl_Base.prototype.showAlert = function(message, title) {
        var inst;
        if (title == null) {
          title = 'Alert';
        }
        if (message.match(/^@[a-zA-Z0-9\._]+$/)) {
          message = this.getRegisteredMessage(message.substr(1));
        }
        if (title && title.match(/^@[a-zA-Z0-9\._]+$/)) {
          title = this.getRegisteredMessage(title.substr(1));
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Index/modal-alert.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.title = title;
              $scope.message = message;
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
        return inst;
      };


      /*
      		 * Show an confirm
      		 *
        	 * @param {String} message The message to show
        	 * @param {String} title   The title to show
      		 * @return {Object}
       */

      DeskPRO_Main_Ctrl_Base.prototype.showConfirm = function(message, title) {
        var inst;
        if (title == null) {
          title = 'Confirm';
        }
        if (message.match(/^@[a-zA-Z0-9\._]+$/)) {
          message = this.getRegisteredMessage(message.substr(1));
        }
        if (title && title.match(/^@[a-zA-Z0-9\._]+$/)) {
          title = this.getRegisteredMessage(title.substr(1));
        }
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('Index/modal-confirm.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.title = title;
              $scope.message = message;
              $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
              return $scope.confirm = function() {
                return $modalInstance.close();
              };
            }
          ]
        });
        return inst;
      };

      DeskPRO_Main_Ctrl_Base.prototype.getWaitEntityPromiseView = function(id) {
        return (function(_this) {
          return function() {
            return _this.getWaitEntityPromise(id);
          };
        })(this);
      };

      DeskPRO_Main_Ctrl_Base.prototype.getWaitEntityPromise = function(id) {
        var promise;
        if (!id) {
          id = 'default';
        }
        if (!this._wait_ent_promise) {
          this._wait_ent_promise = {};
        }
        if (this._wait_ent_promise[id]) {
          return this._wait_ent_promise[id].promise;
        }
        this._wait_ent_promise[id] = this.$q.defer();
        promise = this._wait_ent_promise[id].promise;
        return promise;
      };

      DeskPRO_Main_Ctrl_Base.prototype.resolveWaitEntityPromise = function(id) {
        if (!id) {
          id = 'default';
        }
        if (!this._wait_ent_promise) {
          this._wait_ent_promise = {};
        }
        if (!this._wait_ent_promise[id]) {
          this._wait_ent_promise[id] = this.$q.defer();
        }
        return this._wait_ent_promise[id].resolve(true);
      };


      /*
        	 * Sends an API call and handle it as a stadnard form save. This starts a
        	 * spinner and will handle validation_errors by applyin the error reponse to the view.
        	 *
        	 * @param {String} method   POST/PUT/DELETE (also GET, but probably never used here)
        	 * @param {String} url      The service to call
        	 * @param {Object} data     The data to send
        	 * @param {String} spinner_name The spinner to manage automatically
       */

      DeskPRO_Main_Ctrl_Base.prototype.sendFormSaveApiCall = function(method, url, data, spinner_name) {
        var promise;
        if (spinner_name == null) {
          spinner_name = 'form_saving';
        }
        this.startSpinner(spinner_name);
        switch (method.toUpperCase()) {
          case 'GET':
            method = 'sendGet';
            break;
          case 'POST':
            method = 'sendPostJson';
            break;
          case 'PUT':
            method = 'sendPutJson';
            break;
          case 'DELETE':
            method = 'sendDelete';
            break;
          default:
            throw new Exception("Invalid method type");
        }
        promise = this.Api[method](url, data);
        promise.then((function(_this) {
          return function(res) {
            return _this.stopSpinner(spinner_name);
          };
        })(this), (function(_this) {
          return function(res) {
            var _ref;
            _this.stopSpinner(spinner_name, true);
            if (((_ref = res.data) != null ? _ref.error_code : void 0) === 'validation_error') {
              return _this.applyErrorResponseToView(res.data);
            }
          };
        })(this));
        return promise;
      };

      return DeskPRO_Main_Ctrl_Base;

    })();
  });

}).call(this);

//# sourceMappingURL=Base.js.map
