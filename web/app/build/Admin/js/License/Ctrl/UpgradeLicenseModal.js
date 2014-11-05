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
        var i, _i, _j, _ref, _ref1;
        this.$scope.upgradeType = this.upgradeType;
        this.$scope.upgradeOptions = this.upgradeOptions;
        this.$scope.initial_loading = true;
        this.$scope.phase = 1;
        this.$scope.paymentForm = {
          new_card: {},
          address: {}
        };
        this.$scope.paymentForm.mode = 'new';
        this.$scope.paymentForm.new_card = {
          number: '4929000000006',
          cv2: '123',
          name: 'CN',
          expire_yy: '18',
          expire_mm: '01',
          type: 'visa'
        };
        this.$scope.paymentForm.address = {
          country: 'UK',
          city: 'London',
          state: '',
          post_code: 'W14 0QA',
          address: 'Flat D, 27 Aynhoe Road'
        };
        this.$scope.month_opts = [];
        for (i = _i = 1; _i <= 12; i = ++_i) {
          this.$scope.month_opts.push(i < 10 ? "0" + i : i);
        }
        this.$scope.year_opts = [];
        for (i = _j = _ref = new Date().getFullYear(), _ref1 = (new Date().getFullYear()) + 10; _ref <= _ref1 ? _j <= _ref1 : _j >= _ref1; i = _ref <= _ref1 ? ++_j : --_j) {
          this.$scope.year_opts.push((i + "").substr(2));
        }
        this.$scope.$watch('paymentForm.new_card', (function(_this) {
          return function() {
            if (_this.$scope.formErrors && _this.$scope.formErrors.length) {
              return _this.validate();
            }
          };
        })(this), true);
        this.$scope.$watch('paymentForm.mode', (function(_this) {
          return function() {
            if (_this.$scope.formErrors && _this.$scope.formErrors.length) {
              return _this.$scope.formErrors = [];
            }
          };
        })(this));
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss();
          };
        })(this);
        this.$scope.closeSuccess = (function(_this) {
          return function() {
            return _this.$modalInstance.close();
          };
        })(this);
        if (this.upgradeType === 'extend') {
          this.$scope.toPlan = 1;
        } else {
          this.$scope.toPlan = 100;
        }
        this.currentPlan = null;
        this.$scope.planChanged = (function(_this) {
          return function(newPlan) {
            if (!_this.currentPlan || _this.currentPlan === parseInt(newPlan)) {
              return;
            }
            return _this.refreshForm(newPlan);
          };
        })(this);
        return this.refreshForm();
      };

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.refreshForm = function(plan) {
        if (this.upgradeType === 'extend') {
          return this.refreshRenewForm(plan);
        } else {
          return this.refreshPlanForm(plan);
        }
      };

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.refreshPlanForm = function(plan) {
        this.$scope.initial_loading = true;
        this.planInfo = null;
        this.currentPlan = null;
        return this.DpLicense.getPlanUpgradeInfo(plan || 0).then((function(_this) {
          return function(info) {
            var name;
            if (info.error_code) {
              console.error("License server error code: " + info.error_code);
              _this.$scope.not_online = true;
              return;
            }
            _this.planInfo = info;
            _this.$scope.planInfo = info;
            _this.$scope.initial_loading = false;
            _this.$scope.paymentForm.exist_card = info.card_details || null;
            _this.$scope.paymentForm.invoice = info.invoice || null;
            _this.$scope.availablePlans = info.available_plans.map(function(x) {
              return {
                num: x + "",
                title: x === 100 ? 'Unlimited' : x
              };
            });
            _this.$scope.toPlan = info.next_plan.agents + "";
            _this.currentPlan = info.next_plan.agents;
            if (info.currency_pref === 'usd') {
              name = 'upgrade_cost_total_display';
            } else {
              name = 'upgrade_cost_total_' + info.currency_pref + '_display';
            }
            _this.$scope.paymentSummary = {
              line_title: "Upgrade license to " + info.next_plan.agents + " agents",
              cost: info.next_plan.upgrade_cost,
              cost_display: info.next_plan.upgrade_cost_display,
              cost_vat: info.next_plan.upgrade_cost_vat,
              cost_vat_display: info.next_plan.upgrade_cost_vat_display,
              cost_total: info.next_plan.upgrade_cost_total,
              cost_total_display: info.next_plan.upgrade_cost_total_display,
              currency_total_display: info.next_plan[name],
              currency: info.currency_pref,
              currency_display: info.currency_pref.toUpperCase(),
              vat_rate: info.vat_rate,
              has_vat: info.vat_rate > 0.0,
              invoice_link: info.invoice ? info.invoice.pdf_link : null,
              invoice_web_link: info.invoice ? info.invoice.link : null
            };
            if (_this.$scope.paymentForm.exist_card) {
              _this.$scope.paymentForm.mode = 'exist';
            } else {
              _this.$scope.paymentForm.mode = 'new';
            }
            if (!info.allow_inline_form) {
              return _this.$scope.not_online = true;
            }
          };
        })(this), (function(_this) {
          return function() {
            _this.$scope.initial_loading = false;
            return _this.$scope.not_online = true;
          };
        })(this));
      };

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.refreshRenewForm = function(plan) {
        this.$scope.initial_loading = true;
        this.planInfo = null;
        this.currentPlan = null;
        return this.DpLicense.getRenewInfo(plan || 0).then((function(_this) {
          return function(info) {
            var name;
            if (info.error_code) {
              console.error("License server error code: " + info.error_code);
              _this.$scope.not_online = true;
              return;
            }
            _this.planInfo = info;
            _this.$scope.planInfo = info;
            _this.$scope.initial_loading = false;
            _this.$scope.paymentForm.exist_card = info.card_details || null;
            _this.$scope.paymentForm.address = info.address_info || {};
            _this.$scope.paymentForm.invoice = info.invoice || null;
            _this.$scope.availablePlans = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map(function(x) {
              return {
                num: x + "",
                title: x
              };
            });
            _this.$scope.toPlan = info.next_plan.years + "";
            _this.currentPlan = info.next_plan.years;
            if (info.currency_pref === 'usd') {
              name = 'upgrade_cost_total_display';
            } else {
              name = 'upgrade_cost_total_' + info.currency_pref + '_display';
            }
            _this.$scope.paymentSummary = {
              line_title: "Renew license for " + info.next_plan.years + " years",
              cost_per_year: info.current_plan.per_year_cost_display,
              num_agents: info.current_plan.agents,
              cost: info.next_plan.upgrade_cost,
              cost_display: info.next_plan.upgrade_cost_display,
              cost_vat: info.next_plan.upgrade_cost_vat,
              cost_vat_display: info.next_plan.upgrade_cost_vat_display,
              cost_total: info.next_plan.upgrade_cost_total,
              cost_total_display: info.next_plan.upgrade_cost_total_display,
              currency_total_display: info.next_plan[name],
              currency: info.currency_pref,
              currency_display: info.currency_pref.toUpperCase(),
              vat_rate: info.vat_rate,
              has_vat: info.vat_rate > 0.0,
              invoice_link: info.invoice ? info.invoice.pdf_link : null,
              invoice_web_link: info.invoice ? info.invoice.link : null
            };
            if (_this.$scope.paymentForm.exist_card) {
              _this.$scope.paymentForm.mode = 'exist';
            } else {
              _this.$scope.paymentForm.mode = 'new';
            }
            if (!info.allow_inline_form) {
              return _this.$scope.not_online = true;
            }
          };
        })(this), (function(_this) {
          return function() {
            _this.$scope.initial_loading = false;
            return _this.$scope.not_online = true;
          };
        })(this));
      };

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.validate = function() {
        var addy, card, errors;
        errors = [];
        if (this.$scope.paymentForm.mode === 'new') {
          card = this.$scope.paymentForm.new_card;
          if (!card.number || !card.number.length) {
            errors.push('number');
          }
          if (!card.cv2 || !card.cv2.length) {
            errors.push('cv2');
          }
          if (!card.name || !card.name.length) {
            errors.push('name');
          }
          if (!parseInt(card.expire_mm) || !parseInt(card.expire_yy)) {
            errors.push('expire');
          }
          addy = this.$scope.paymentForm.address;
          if (!addy.country) {
            errors.push('country');
          }
          if (!addy.address || !addy.address.length) {
            errors.push('address');
          }
          if (!addy.city || !addy.city.length) {
            errors.push('city');
          }
          if ((!addy.state || !addy.state.length) && addy.country === 'US') {
            errors.push('state');
          }
          if (!addy.post_code || !addy.post_code.length) {
            errors.push('post_code');
          }
        }
        this.$scope.formErrors = errors;
        return errors.length === 0;
      };

      Admin_License_Ctrl_UpgradeLicenseModal.prototype.sendPayment = function() {
        if (!this.validate()) {
          return;
        }
        this.$scope.loading = true;
        this.$scope.phase = 2;
        this.$scope.stepId = 0;
        this.$scope.error_code = null;
        return this.DpLicense.sendPayInvoiceRequest(this.$scope.paymentForm.mode, this.$scope.paymentForm.new_card, this.$scope.paymentForm.address, this.planInfo.invoice.id, this.planInfo.invoice.auth).then((function(_this) {
          return function(data) {
            if (!data.success) {
              _this.$scope.phase = 1;
              _this.$scope.loading = false;
              if (data.error_message) {
                _this.$scope.formErrors = [data.error_message];
              } else {
                _this.$scope.formErrors = ["There was a problem processing your payment. Please try agian."];
              }
              return;
            }
            _this.$scope.stepId = 1;
            return _this.DpLicense.getNewLicenseKey().then(function(res) {
              _this.$scope.stepId = 2;
              return _this.DpLicense.setNewLicenseCode(res.license_code).then(function() {
                _this.$scope.loading = false;
                _this.$scope.phase = 3;
                return _this.$scope.show_done = true;
              }, function() {
                this.$scope.loading = false;
                return this.$scope.error_code = 'failed_get_lic_key';
              });
            }, function() {
              this.$scope.loading = false;
              return this.$scope.error_code = 'failed_get_lic_key';
            });
          };
        })(this), (function(_this) {
          return function(data) {
            _this.$scope.loading = false;
            _this.$scope.phase = 1;
            return _this.$scope.formErrors = ["There was a problem processing your payment."];
          };
        })(this));
      };

      return Admin_License_Ctrl_UpgradeLicenseModal;

    })(Admin_Ctrl_Base);
    return Admin_License_Ctrl_UpgradeLicenseModal.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=UpgradeLicenseModal.js.map
