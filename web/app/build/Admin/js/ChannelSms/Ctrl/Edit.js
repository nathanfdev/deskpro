(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_ChannelSms_Ctrl_Edit;
    Admin_ChannelSms_Ctrl_Edit = (function(_super) {
      __extends(Admin_ChannelSms_Ctrl_Edit, _super);

      function Admin_ChannelSms_Ctrl_Edit() {
        return Admin_ChannelSms_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_ChannelSms_Ctrl_Edit.CTRL_ID = 'Admin_ChannelSms_Ctrl_Edit';

      Admin_ChannelSms_Ctrl_Edit.CTRL_AS = 'ChannelSmsEdit';

      Admin_ChannelSms_Ctrl_Edit.DEPS = ['Api', 'Growl', 'SmsAccountsData', '$stateParams', '$modal', 'dpObTypesDefTicketActions'];

      Admin_ChannelSms_Ctrl_Edit.prototype.init = function() {
        return this.accountId = parseInt(this.$stateParams.id || 0);
      };

      Admin_ChannelSms_Ctrl_Edit.prototype.initialLoad = function() {
        var list_promise;
        list_promise = this.SmsAccountsData.loadList().then((function(_this) {
          return function(recs) {
            _this.accounts = recs.values();
            if (_this.$state.current.name === 'tickets.channel_sms') {
              if (_this.accounts[0]) {
                _this.$state.go('tickets.channel_sms.edit', {
                  id: _this.accounts[0].id
                });
              } else {
                _this.$state.go('tickets.channel_sms.create');
              }
            }
            return _this.addManagedListener(_this.SmsAccountsData.recs, 'changed', function() {
              _this.accounts = _this.SmsAccountsData.recs.values();
              console.log(_this.accounts);
              return _this.ngApply();
            });
          };
        })(this));
        return this.$q.all([list_promise]);
      };

      return Admin_ChannelSms_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_ChannelSms_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
