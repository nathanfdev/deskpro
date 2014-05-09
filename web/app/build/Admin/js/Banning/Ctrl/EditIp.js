(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Banning_Ctrl_EditIp;
    Admin_Banning_Ctrl_EditIp = (function(_super) {
      __extends(Admin_Banning_Ctrl_EditIp, _super);

      function Admin_Banning_Ctrl_EditIp() {
        return Admin_Banning_Ctrl_EditIp.__super__.constructor.apply(this, arguments);
      }

      Admin_Banning_Ctrl_EditIp.CTRL_ID = 'Admin_Banning_Ctrl_EditIp';

      Admin_Banning_Ctrl_EditIp.CTRL_AS = 'EditCtrl';

      Admin_Banning_Ctrl_EditIp.DEPS = ['$stateParams'];

      Admin_Banning_Ctrl_EditIp.prototype.init = function() {
        this.banData = this.DataService.get('Bans');
        this.banData.setType('ip');
        return this.ip_ban = null;
      };


      /*
       	 *
       */

      Admin_Banning_Ctrl_EditIp.prototype.initialLoad = function() {
        var promise;
        promise = this.banData.loadEditBanData(this.$stateParams.ban || null).then((function(_this) {
          return function(data) {
            _this.ip_ban = data.ip_ban;
            return _this.form = data.form;
          };
        })(this));
        return promise;
      };


      /*
      		 *
       */

      Admin_Banning_Ctrl_EditIp.prototype.saveForm = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.$stateParams.ban;
        promise = this.banData.saveFormModel(this.ip_ban, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('crm.banning.gocreate_ip');
            } else {
              return _this.$state.go('crm.banning.edit_ip', {
                ban: _this.ip_ban.banned_ip
              });
            }
          };
        })(this));
      };

      return Admin_Banning_Ctrl_EditIp;

    })(Admin_Ctrl_Base);
    return Admin_Banning_Ctrl_EditIp.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditIp.js.map
