(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_TicketSettings_Ctrl_TicketSettings;
    Admin_TicketSettings_Ctrl_TicketSettings = (function(_super) {
      __extends(Admin_TicketSettings_Ctrl_TicketSettings, _super);

      function Admin_TicketSettings_Ctrl_TicketSettings() {
        return Admin_TicketSettings_Ctrl_TicketSettings.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketSettings_Ctrl_TicketSettings.CTRL_ID = 'Admin_TicketSettings_Ctrl_TicketSettings';

      Admin_TicketSettings_Ctrl_TicketSettings.CTRL_AS = 'TicketSettings';

      Admin_TicketSettings_Ctrl_TicketSettings.DEPS = [];

      Admin_TicketSettings_Ctrl_TicketSettings.prototype.init = function() {
        return this.settings = null;
      };

      Admin_TicketSettings_Ctrl_TicketSettings.prototype.initialLoad = function() {
        var data_promise;
        data_promise = this.Api.sendDataGet({
          'settings': '/ticket_settings'
        }).then((function(_this) {
          return function(res) {
            var k, settings, v, _ref;
            settings = res.data.settings.ticket_settings;
            _ref = settings.agent_defaults;
            for (k in _ref) {
              if (!__hasProp.call(_ref, k)) continue;
              v = _ref[k];
              if (!v) {
                settings.agent_defaults[k] = "0";
              }
            }
            _this.$scope.settings = settings;
            return _this.settings = angular.copy(_this.$scope.settings);
          };
        })(this));
        this.headerSortList = {
          axis: 'y',
          handle: '.drag-handle',
          update: (function(_this) {
            return function(ev, data) {
              var $list, newOrder;
              $list = data.item.closest('ul');
              newOrder = [];
              $list.find('li').each(function() {
                return newOrder.push($(this).data('value'));
              });
              return _this.$scope.settings.from_email_headers = newOrder;
            };
          })(this)
        };
        return this.$q.all([data_promise]);
      };

      Admin_TicketSettings_Ctrl_TicketSettings.prototype.isDirtyState = function() {
        if (!this.settings) {
          return false;
        }
        if (!angular.equals(this.settings, this.$scope.settings)) {
          return true;
        } else {
          return false;
        }
      };

      Admin_TicketSettings_Ctrl_TicketSettings.prototype.save = function() {
        var postData, promise;
        postData = {
          ticket_settings: this.$scope.settings
        };
        this.startSpinner('saving');
        return promise = this.Api.sendPostJson('/ticket_settings', postData).success((function(_this) {
          return function() {
            _this.settings = angular.copy(_this.$scope.settings);
            return _this.stopSpinner('saving').then(function() {
              return _this.Growl.success(_this.getRegisteredMessage('saved_settings'));
            });
          };
        })(this)).error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
      };

      return Admin_TicketSettings_Ctrl_TicketSettings;

    })(Admin_Ctrl_Base);
    return Admin_TicketSettings_Ctrl_TicketSettings.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TicketSettings.js.map
