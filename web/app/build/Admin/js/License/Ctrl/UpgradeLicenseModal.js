(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_License_Ctrl_UpgradeLicenseModal;
    Admin_License_Ctrl_UpgradeLicenseModal = (function(_super) {
      __extends(Admin_License_Ctrl_UpgradeLicenseModal, _super);

      function Admin_License_Ctrl_UpgradeLicenseModal() {
        return Admin_License_Ctrl_UpgradeLicenseModal.__super__.constructor.apply(this, arguments);
      }

      Admin_License_Ctrl_UpgradeLicenseModal.CTRL_ID = 'Admin_License_Ctrl_UpgradeLicenseModal';

      Admin_License_Ctrl_UpgradeLicenseModal.CTRL_AS = 'Ctrl';

      Admin_License_Ctrl_UpgradeLicenseModal.DEPS = ['$modalInstance', 'upgradeType', 'upgradeOptions', 'Api', 'DpLicense'];

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.init = function() {
        this.$scope.upgradeType = this.upgradeType;
        this.$scope.upgradeOptions = this.upgradeOptions;
        this.$scope.initial_loading = true;
        this.DpLicense.getPlanUpgradeInfo().then((function(_this) {
          return function(info) {
            console.log(info);
            _this.planInfo = info;
            _this.$scope.planInfo = info;
            _this.$scope.initial_loading = false;
            _this.$scope.paymentForm = {};
            _this.$scope.paymentForm.exist_card = info.card_details || null;
            if (_this.$scope.paymentForm.exist_card) {
              return _this.$scope.paymentForm.card_type = 'existing';
            } else {
              return _this.$scope.paymentForm.card_type = 'new';
            }
          };
        })(this));
      };

      return Admin_License_Ctrl_UpgradeLicenseModal;

    })(Admin_Ctrl_Base);
    return Admin_License_Ctrl_UpgradeLicenseModal.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=UpgradeLicenseModal.js.map
