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
       * EXPORT_CTRL() is pre-configured to install the controller into the Admin_App module
       * with the defined dependencies (as well as AppState and $scope which are always defined).
       *
       * Note that controllers typically *register themselves* with EXPORT_CTRL(). This is converse to
       * all other types of objects (services and directives etc) which are registered through the App
       * loader.
    */

    var Admin_Ctrl_Base;
    return Admin_Ctrl_Base = (function() {
      Admin_Ctrl_Base.CTRL_AS = null;

      Admin_Ctrl_Base.CTRL_ID = 'Admin_Main_Ctrl_Base';

      Admin_Ctrl_Base.DEPS = [];

      /**
      		* Exports this controller to the Admin_App angular module
        	* so it can be used.
      */


      Admin_Ctrl_Base.EXPORT_CTRL = function() {
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


      function Admin_Ctrl_Base() {
        var arg, arg_name, args, i, me, ret, _i, _j, _len, _len1,
          _this = this;
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
        for (i = _j = 0, _len1 = args.length; _j < _len1; i = ++_j) {
          arg = args[i];
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
        this.$scope.$on('$destroy', function(ev) {
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
        });
        this.$scope.isStateActive = function(stateId, stateParams) {
          if (stateParams == null) {
            stateParams = null;
          }
          return _this.$state.isStateActive(stateId, stateParams);
        };
        this.$scope.state_path = function(route, params) {
          if (params == null) {
            params = {};
          }
          return _this.$state.href(route, params);
        };
        this.$scope.$on('$stateChangeStart', function(ev, toState, toParams, fromState, fromParams) {
          var resetHash;
          if (ev.defaultPrevented) {
            return;
          }
          if (_this._state_cont_ignore) {
            _this._state_cont_ignore = false;
            return;
          }
          if (!_this._state_cont_go && !window.DP_NO_DIRTYSTATE_CONFIRM && _this.isDirtyState()) {
            ev.preventDefault();
            resetHash = _this.$state.href(fromState, fromParams);
            _this._state_cont_ignore = true;
            window.location.hash = resetHash;
            setTimeout(function() {
              return _this._state_cont_ignore = false;
            }, 140);
            _this._state_cont_state = toState.name;
            _this._state_cont_state_params = toParams;
            return _this._showStateConfirmLeave();
          }
        });
        this.$scope.dp_ctrl_elemnt_ping = {};
        this._saved_state = {};
        this.has_init = false;
        this.init();
        this.has_init = true;
        this.$scope.state_loading = false;
        ret = this.initialLoad();
        if (ret) {
          this.$scope.state_loading = true;
          this.dpInterfaceTimer.startControllerLoad(this);
          ret.then(function() {
            _this.dpInterfaceTimer.endControllerLoad(_this);
            _this.$scope.state_loading = false;
            if (_this.$state.current.name.split('.').length === 2 && _this.$scope._autoload_links) {
              return _this.$timeout(function() {
                return _this.runNextAutoload();
              });
            }
          });
        }
      }

      /*
        	# Loads the next section
      */


      Admin_Ctrl_Base.prototype.runNextAutoload = function() {
        var al, link, _i, _len, _ref, _results;
        if (!this.$scope._autoload_links) {
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
          if (al.select) {
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


      Admin_Ctrl_Base.prototype.pingElement = function(id) {
        return this.$scope.dp_ctrl_elemnt_ping[id] = (new Date()).getTime();
      };

      /*
        	# Enables a 'spinner' state in the view which will
        	# last for at least minTime time.
        	#
        	# If a spinner already exists, then it will be restarted.
        	#
        	# @param {String} id The ID of the spinner
        	# @param {Integer} minTime The min time the spinner should be visible for
        	# @return {promise} A promise that resolves once the spinner stops
      */


      Admin_Ctrl_Base.prototype.startSpinner = function(id, minTime) {
        var deferred, desc,
          _this = this;
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
          setTimeoutDone: function() {
            desc.doneTime = true;
            if (desc._timeout) {
              _this.$timeout.cancel(desc._timeout);
            }
            if (desc.doneSpin) {
              return deferred.resolve();
            }
          },
          setSpinDone: function() {
            desc.doneSpin = true;
            if (desc.doneTime) {
              return deferred.resolve();
            }
          },
          _promise: deferred.promise,
          _timeout: this.$timeout(function() {
            return desc.setTimeoutDone();
          }, minTime)
        };
        this.$scope.dp_spin_els[id] = desc;
        return desc._promise;
      };

      /*
        	# Stops a 'spinner' state in the view. This by default
        	# only marks the manual spinner state as off. The timer may stil
        	# be going which means the spinner will still be visible until that
        	# ends too. Pass force=true to stop the spinner (disregarding the min time)
        	#
        	# @param {String} id The ID of the spinner
        	# @param {Boolean} force True to stop the spinner even if the minTime timer is still going
      		# @return {promise} A promise that resolves once the spinner stops
      */


      Admin_Ctrl_Base.prototype.stopSpinner = function(id, force) {
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


      Admin_Ctrl_Base.prototype.isDirtyState = function() {
        return false;
      };

      /**
        	* Set dirty state checking feature on this tab. Disabled
         	* means no dirty state checking is performed when the user
        	* tries to leave.
        	*
        	* @param {Boolean} turn_off True (default) to turn off. Pass false to turn it back on
      */


      Admin_Ctrl_Base.prototype.skipDirtyState = function(turn_off) {
        if (turn_off == null) {
          turn_off = true;
        }
        return this._state_cont_go = turn_off;
      };

      /**
      		* Controllers can implement this init() method to add custom init functionality.
      */


      Admin_Ctrl_Base.prototype.init = function() {};

      /**
      		* Controllers can implement this initialLoad() method to load the data needed for a view
      */


      Admin_Ctrl_Base.prototype.initialLoad = function() {};

      /**
      		* Given an error response from the server, apply it to the view. This is typically
        	* a validation error that we want to show in the form.
      */


      Admin_Ctrl_Base.prototype.applyErrorResponseToView = function(result) {
        var check_code, code, code_safe, code_segs, error_codes, field, field_title, form, form_key, handled_codes, last_seg, _i, _j, _k, _len, _len1, _len2, _ref, _ref1, _ref2;
        if ((result != null ? result.error_code : void 0) !== 'validation_error') {
          return;
        }
        error_codes = [];
        _ref = result.detail.code_name.split(',');
        for (_i = 0, _len = _ref.length; _i < _len; _i++) {
          code = _ref[_i];
          error_codes.push(code);
        }
        handled_codes = [];
        _ref1 = this.$scope;
        for (form_key in _ref1) {
          if (!__hasProp.call(_ref1, form_key)) continue;
          form = _ref1[form_key];
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
              _ref2 = field.dpServerValidationKeys;
              for (_k = 0, _len2 = _ref2.length; _k < _len2; _k++) {
                check_code = _ref2[_k];
                if (check_code.indexOf(code) === 0) {
                  code_segs = code.split('.');
                  last_seg = code_segs.pop();
                  switch (last_seg) {
                    case 'required':
                      field.$setValidity('required', false);
                      break;
                    default:
                      code_safe = code.replace(/\./g, '_');
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
        	# Gets a message from a registered message.
        	# Generally these are registered with the dp-message directive in a view.
        	#
        	# @param {String} id The message ID
        	# @return {String}
      */


      Admin_Ctrl_Base.prototype.getRegisteredMessage = function(id) {
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


      Admin_Ctrl_Base.prototype.ngApply = function(fn) {
        var e,
          _this = this;
        if (!this.$scope.$$phase && !this.$scope.$root.$$phase) {
          try {
            return this.$scope.$apply(fn);
          } catch (_error) {
            e = _error;
            return window.setTimeout(function() {
              try {
                return _this.ngApply();
              } catch (_error) {
                e = _error;
              }
            }, 100);
          }
        }
      };

      /**
      		* Configures an object for auto-release when this controller is destroyed
      		*
      		* @param {Admin_Main_Model_Base} obj
      */


      Admin_Ctrl_Base.prototype._configureAutoReleaseObject = function(obj) {
        var _this = this;
        if (this._autoReleaseObjects == null) {
          this._autoReleaseObjects = [];
          this.$scope.$on('$destroy', function() {
            var i, _i, _len, _ref, _results;
            _ref = _this._autoReleaseObjects;
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              i = _ref[_i];
              _results.push(i.release());
            }
            return _results;
          });
        }
        return this._autoReleaseObjects.push(obj);
      };

      /**
      		* Attaches a listener to an object that will be automatically removed
        	* when this controller is destroyed.
      		*
      		* @param {Admin_Main_Model_Base} obj
      */


      Admin_Ctrl_Base.prototype.addManagedListener = function(object, event_name, fn) {
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


      Admin_Ctrl_Base.prototype.getTemplatePath = function(path) {
        return DP_BASE_ADMIN_URL + '/load-view/' + path;
      };

      /**
      		* Show an alert
      */


      Admin_Ctrl_Base.prototype._showStateConfirmLeave = function() {
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


      Admin_Ctrl_Base.prototype.showAlert = function(message, title) {
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
      		# Show an confirm
      		#
        	# @param {String} message The message to show
        	# @param {String} title   The title to show
      		# @return {Object}
      */


      Admin_Ctrl_Base.prototype.showConfirm = function(message, title) {
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

      Admin_Ctrl_Base.prototype.getWaitEntityPromiseView = function(id) {
        var _this = this;
        return function() {
          return _this.getWaitEntityPromise(id);
        };
      };

      Admin_Ctrl_Base.prototype.getWaitEntityPromise = function(id) {
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

      Admin_Ctrl_Base.prototype.resolveWaitEntityPromise = function(id) {
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

      return Admin_Ctrl_Base;

    })();
  });

}).call(this);

/*
//@ sourceMappingURL=Base.js.map
*/