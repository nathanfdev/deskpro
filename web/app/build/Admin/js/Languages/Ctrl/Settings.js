(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Languages_Ctrl_Settings;
    Admin_Languages_Ctrl_Settings = (function(_super) {
      __extends(Admin_Languages_Ctrl_Settings, _super);

      function Admin_Languages_Ctrl_Settings() {
        return Admin_Languages_Ctrl_Settings.__super__.constructor.apply(this, arguments);
      }

      Admin_Languages_Ctrl_Settings.CTRL_ID = 'Admin_Languages_Ctrl_Settings';

      Admin_Languages_Ctrl_Settings.CTRL_AS = 'EditCtrl';

      Admin_Languages_Ctrl_Settings.prototype.init = function() {
        return this.form = {
          lang_auto_install: false,
          tickets_move_from: 0,
          tickets_move_to: 0,
          users_move_from: 0,
          users_move_to: 0
        };
      };

      Admin_Languages_Ctrl_Settings.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          setting: '/settings/values/core.lang_auto_install',
          lang: '/langs'
        }).then((function(_this) {
          return function(res) {
            var pack, _i, _len, _ref;
            _this.form.lang_auto_install = parseInt(res.data.setting.value) ? true : false;
            _this.langChoices = [];
            _ref = res.data.lang.packs;
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              pack = _ref[_i];
              if (pack.is_installed) {
                _this.langChoices.push({
                  id: pack.installed_language_id,
                  title: pack.title
                });
              }
            }
            _this.form.tickets_move_to = res.data.lang.default_lang_id;
            return _this.form.users_move_to = res.data.lang.default_lang_id;
          };
        })(this));
        return promise;
      };

      Admin_Languages_Ctrl_Settings.prototype.saveSettings = function() {
        this.startSpinner('saving_settings');
        return this.Api.sendPost('/settings/values/core.lang_auto_install', {
          value: this.form.lang_auto_install ? '1' : '0'
        }).then((function(_this) {
          return function() {
            return _this.stopSpinner('saving_settings');
          };
        })(this));
      };

      Admin_Languages_Ctrl_Settings.prototype.doMassTicketMove = function() {
        return this.showConfirm('@confirm_move_tickets').result.then((function(_this) {
          return function() {
            var postData;
            _this.startSpinner('saving_tickets');
            postData = {
              from_lang: _this.form.tickets_move_from,
              to_lang: _this.form.tickets_move_to
            };
            return _this.Api.sendPost('/langs/tools/mass-update-tickets', postData).then(function() {
              return _this.stopSpinner('saving_tickets');
            });
          };
        })(this));
      };

      Admin_Languages_Ctrl_Settings.prototype.doMassUserMove = function() {
        return this.showConfirm('@confirm_move_users').result.then((function(_this) {
          return function() {
            var postData;
            _this.startSpinner('saving_users');
            postData = {
              from_lang: _this.form.users_move_from,
              to_lang: _this.form.users_move_to
            };
            return _this.Api.sendPost('/langs/tools/mass-update-users', postData).then(function() {
              return _this.stopSpinner('saving_users');
            });
          };
        })(this));
      };

      return Admin_Languages_Ctrl_Settings;

    })(Admin_Ctrl_Base);
    return Admin_Languages_Ctrl_Settings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Settings.js.map
