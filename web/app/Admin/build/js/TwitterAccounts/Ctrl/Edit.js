(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TwitterAccounts_Ctrl_Edit;
    Admin_TwitterAccounts_Ctrl_Edit = (function(_super) {
      __extends(Admin_TwitterAccounts_Ctrl_Edit, _super);

      function Admin_TwitterAccounts_Ctrl_Edit() {
        return Admin_TwitterAccounts_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TwitterAccounts_Ctrl_Edit.CTRL_ID = 'Admin_TwitterAccounts_Ctrl_Edit';

      Admin_TwitterAccounts_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TwitterAccounts_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_TwitterAccounts_Ctrl_Edit.prototype.init = function() {
        this.twitterAccountData = this.DataService.get('TwitterAccounts');
        return this.twitter_account = null;
      };

      Admin_TwitterAccounts_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.twitterAccountData.loadEditTwitterAccountData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.twitter_account = data.twitter_account;
            return _this.form = data.form;
          };
        })(this));
        return promise;
      };

      Admin_TwitterAccounts_Ctrl_Edit.prototype.saveForm = function() {
        var agent, is_new, key, promise, value, _ref;
        this.twitter_account.persons = [];
        _ref = this.selected_agents;
        for (key in _ref) {
          if (!__hasProp.call(_ref, key)) continue;
          value = _ref[key];
          if (value) {
            agent = _.findWhere(this.agents, {
              id: parseInt(key)
            });
            if (agent) {
              this.twitter_account.persons.push(agent.id);
            }
          }
        }
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.twitter_account.id;
        promise = this.twitterAccountData.saveFormModel(this.twitter_account, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('twitter.accounts.gocreate');
            }
          };
        })(this));
      };

      return Admin_TwitterAccounts_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TwitterAccounts_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
