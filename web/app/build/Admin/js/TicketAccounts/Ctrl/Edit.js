(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/TicketAccounts/FormModel/EditTicketAccountModel'], function(Admin_Ctrl_Base, EditTicketAccountModel) {
    var Admin_TicketAccounts_Ctrl_Edit;
    Admin_TicketAccounts_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketAccounts_Ctrl_Edit, _super);

      function Admin_TicketAccounts_Ctrl_Edit() {
        return Admin_TicketAccounts_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketAccounts_Ctrl_Edit.CTRL_ID = 'Admin_TicketAccounts_Ctrl_Edit';

      Admin_TicketAccounts_Ctrl_Edit.CTRL_AS = 'TicketAccountsEdit';

      Admin_TicketAccounts_Ctrl_Edit.DEPS = ['Api', 'Growl', 'TicketAccountsData', '$stateParams', '$modal', 'dpObTypesDefTicketActions'];

      Admin_TicketAccounts_Ctrl_Edit.prototype.init = function() {
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.$scope.actionOptionTypes = [];
        this.$scope.actions_form = {};
        this.$scope.message_count = null;
        this.accountId = parseInt(this.$stateParams.id || 0);
        this.didPassTest = false;
        this.testMessageCount = 0;
        this.didConfirmExistingMessages = false;
        return this.test_email = {
          to: window.DP_PERSON_EMAIL,
          from: '',
          subject: 'Test email',
          message: 'This is a test. If you see this email in your inbox, your outgoing email account are correct.'
        };
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, types, _i, _len, _results;
        types = ['web', 'web.user'];
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types, {
          dynamicOptions: this.customActions
        });
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_i = 0, _len = setActionOptions.length; _i < _len; _i++) {
          opt = setActionOptions[_i];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.initialLoad = function() {
        var data_promise, dep_promise, final_promise, get, proms, trigger_data_promise, trigger_promise;
        dep_promise = this.DataService.get('TicketDeps').loadList().then((function(_this) {
          return function(list) {
            return _this.deps = list;
          };
        })(this));
        get = {
          customActions: '/ticket_triggers/get-custom-actions'
        };
        if (this.accountId) {
          get.trigger = "/ticket_triggers/email_accounts/" + this.accountId;
        }
        trigger_promise = this.Api.sendDataGet(get).then((function(_this) {
          return function(result) {
            var action, rowId, _i, _len, _ref, _ref1, _ref2, _ref3, _ref4, _results;
            _this.customActions = result.data.customActions.action_defs;
            if (((_ref = result.data) != null ? (_ref1 = _ref.trigger) != null ? _ref1.trigger : void 0 : void 0) != null) {
              _this.trigger = result.data.trigger.trigger;
              _this.triggerId = _this.trigger.id;
              if ((_ref2 = _this.trigger.actions) != null ? (_ref3 = _ref2.actions) != null ? _ref3.length : void 0 : void 0) {
                _this.$scope.actions_form = {};
                _ref4 = _this.trigger.actions.actions;
                _results = [];
                for (_i = 0, _len = _ref4.length; _i < _len; _i++) {
                  action = _ref4[_i];
                  rowId = _.uniqueId('action');
                  _results.push(_this.$scope.actions_form[rowId] = action);
                }
                return _results;
              }
            } else {
              _this.trigger = {};
              return _this.triggerId = 0;
            }
          };
        })(this));
        trigger_data_promise = this.actionsTypeDef.loadDataOptions();
        proms = [trigger_promise, trigger_data_promise, dep_promise];
        if (!this.accountId) {
          this.account = {
            is_enabled: true
          };
          this.trigger = {};
        } else {
          data_promise = this.Api.sendDataGet({
            'email_account': '/email_accounts/' + this.accountId
          }).then((function(_this) {
            return function(result) {
              _this.account = result.data.email_account.email_account;
              _this.trigger = result.data.email_account.trigger;
              _this.form_model = new EditTicketAccountModel(_this.account);
              return _this.$scope.form = _this.form_model.form;
            };
          })(this));
          proms.push(data_promise);
        }
        final_promise = this.$q.all(proms);
        final_promise.then((function(_this) {
          return function() {
            _this.form_model = new EditTicketAccountModel(_this.account, _this.deps, _this.trigger);
            if (!_this.accountId) {
              _this.form_model.form.incoming_account_type = '';
              _this.form_model.form.outgoing_account_type = 'smtp';
            }
            _this.$scope.form = _this.form_model.form;
            return _this.updateCriteriaOptionTypes();
          };
        })(this));
        return final_promise;
      };


      /*
        	 * Saves the current form
        	 *
        	 * @return {promise}
       */

      Admin_TicketAccounts_Ctrl_Edit.prototype.saveAccount = function() {
        var is_new, postData, promise, triggerSaver;
        if (!this.account.id && !this.new_is_confirmed) {
          this.showNewAccountConfirm();
          return;
        }
        postData = this.form_model.getFormData();
        triggerSaver = (function(_this) {
          return function() {
            var act, _, _ref;
            postData = {
              actions: []
            };
            if (_this.$scope.actions_form) {
              _ref = _this.$scope.actions_form;
              for (_ in _ref) {
                if (!__hasProp.call(_ref, _)) continue;
                act = _ref[_];
                if (act.type) {
                  postData.actions.push(act);
                }
              }
            }
            return _this.Api.sendPostJson('/ticket_triggers/email_accounts/' + _this.account.id, postData);
          };
        })(this);
        this.startSpinner('saving_account');
        if (this.account.id) {
          is_new = false;
          promise = this.Api.sendPostJson('/email_accounts/' + this.account.id, postData);
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/email_accounts', postData);
        }
        promise.success((function(_this) {
          return function(result) {
            _this.account.id = result.email_account_id || _this.account.id;
            _this.account.is_enabled = _this.$scope.form.is_enabled;
            return triggerSaver().then(function() {
              _this.stopSpinner('saving_account', true).then(function() {
                return _this.Growl.success(_this.getRegisteredMessage('saved_account'));
              });
              _this.form_model.apply();
              _this.TicketAccountsData.updateModel(_this.account);
              _this.skipDirtyState();
              if (is_new) {
                return _this.$state.go('tickets.ticket_accounts.gocreate');
              } else {
                return _this.$state.go('tickets.ticket_accounts');
              }
            });
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving_account', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };


      /*
        	 * Test current account settings
        	 *
        	 * @return {promise}
       */

      Admin_TicketAccounts_Ctrl_Edit.prototype.loadAccountTest = function() {
        return this.Api.sendPostJson('/email_accounts/test-account', this.form_model.getFormData()).success((function(_this) {
          return function(result) {
            return _this.didPassTest = result.is_success;
          };
        })(this));
      };


      /*
        	 * Test current outgoing settings with message details from @test_email object.
        	 *
        	 * @return {promise}
       */

      Admin_TicketAccounts_Ctrl_Edit.prototype.loadOutgoingAccountTest = function() {
        var form_data;
        form_data = this.form_model.getFormData();
        form_data.test_email = this.test_email;
        return this.Api.sendPostJson('/email_accounts/test-outgoing-account', form_data);
      };


      /*
        	 * Show the test account modal
       */

      Admin_TicketAccounts_Ctrl_Edit.prototype.testAccountModal = function() {
        var inst, me;
        me = this;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/test-account-modal.html'),
          controller: [
            '$scope', '$modalInstance', (function(_this) {
              return function($scope, $modalInstance) {
                var testNow;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.showLog = function() {
                  return $scope.showing_log = true;
                };
                testNow = function() {
                  $scope.showing_log = false;
                  $scope.is_testing = true;
                  return _this.loadAccountTest().success(function(result) {
                    $scope.is_testing = false;
                    $scope.is_success = result.is_success;
                    $scope.log = result.log;
                    $scope.message_count = result.message_count;
                    return me.$scope.message_count = result.message_count;
                  }).error(function() {
                    $scope.showing_log = true;
                    $scope.is_testing = false;
                    $scope.is_success = false;
                    $scope.log = "Server Error";
                    $scope.message_count = 0;
                    return me.$scope.message_count = null;
                  });
                };
                testNow();
                return $scope.testNow = function() {
                  return testNow();
                };
              };
            })(this)
          ]
        });
      };

      Admin_TicketAccounts_Ctrl_Edit.prototype.showNewAccountConfirm = function() {
        var inst, me;
        me = this;
        inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/new-account-confirm.html'),
          controller: [
            '$scope', '$modalInstance', (function(_this) {
              return function($scope, $modalInstance) {
                $scope.message_count = me.$scope.message_count;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                return $scope.confirm = function() {
                  return $modalInstance.close(true);
                };
              };
            })(this)
          ]
        });
        return inst.result.then((function(_this) {
          return function(r) {
            if (r) {
              _this.new_is_confirmed = true;
              return _this.saveAccount();
            }
          };
        })(this));
      };


      /*
        	 * Show the test account modal
       */

      Admin_TicketAccounts_Ctrl_Edit.prototype.testOutgoingModal = function() {
        var inst, me;
        me = this;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('TicketAccounts/test-outgoing-modal.html'),
          resolve: {
            test_email: (function(_this) {
              return function() {
                _this.test_email.from = _this.form_model.form.address;
                return _this.test_email;
              };
            })(this)
          },
          controller: [
            '$scope', '$modalInstance', 'test_email', (function(_this) {
              return function($scope, $modalInstance, test_email) {
                var resetTest, testNow;
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.showLog = function() {
                  return $scope.showing_log = true;
                };
                $scope.test_email = test_email;
                testNow = function() {
                  $scope.testing_started = true;
                  $scope.showing_log = false;
                  $scope.is_testing = true;
                  if (!test_email.from) {
                    test_email.from = me.form_model.form.address;
                  }
                  return me.loadOutgoingAccountTest().success(function(result) {
                    $scope.is_testing = false;
                    $scope.is_success = result.is_success;
                    $scope.log = result.log;
                    return $scope.message_count = result.message_count;
                  }).error(function() {
                    $scope.showing_log = true;
                    $scope.is_testing = false;
                    $scope.is_success = false;
                    $scope.log = "Server Error";
                    return $scope.message_count = 0;
                  });
                };
                resetTest = function() {
                  return $scope.testing_started = false;
                };
                $scope.testNow = function() {
                  return testNow();
                };
                return $scope.resetTest = function() {
                  return resetTest();
                };
              };
            })(this)
          ]
        });
      };

      return Admin_TicketAccounts_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketAccounts_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
