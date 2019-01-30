// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS202: Simplify dynamic range loops
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
  class Admin_License_Ctrl_UpgradeLicenseModal extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_License_Ctrl_UpgradeLicenseModal';
      this.CTRL_AS   = 'Ctrl';
      this.DEPS      = ['$modalInstance', 'upgradeType', 'upgradeOptions', 'Api', 'DpLicense'];
    }

    init() {
      let i;
      let asc, end, start;
      this.$scope.upgradeType     = this.upgradeType;
      this.$scope.upgradeOptions  = this.upgradeOptions;
      this.$scope.initial_loading = true;
      this.$scope.phase           = 1;
      this.$scope.paymentForm = { new_card: {}, address: {} };

      this.$scope.dismiss      = () => this.$modalInstance.dismiss();
      this.$scope.closeSuccess = () => this.$modalInstance.close();

      if (window.DP_USES_CUSTOM_BILLING) {
        this.$scope.initial_loading = false;
        this.$scope.uses_custom_billing = true;
        return;
      }

      // DEBUG
      this.$scope.paymentForm.mode = 'new';
      this.$scope.paymentForm.new_card = {
        number: '',
        cv2: '',
        name: '',
        expire_yy: '',
        expire_mm: '',
        type: ''
      };
      this.$scope.paymentForm.address = {
        country: '',
        city: '',
        state: '',
        post_code: '',
        address: ''
      };

      this.$scope.month_opts = [];
      for (i = 1; i <= 12; i++) {
        this.$scope.month_opts.push(i < 10 ? `0${i}` : i);
      }

      this.$scope.year_opts = [];
      for (start = new Date().getFullYear(), i = start, end = (new Date().getFullYear())+10, asc = start <= end; asc ? i <= end : i >= end; asc ? i++ : i--) {
        this.$scope.year_opts.push((i+"").substr(2));
      }

      this.$scope.$watch('paymentForm.new_card', () => {
        if (this.$scope.formErrors && this.$scope.formErrors.length) {
          return this.validate();
        }
      }
      , true);
      this.$scope.$watch('paymentForm.mode', () => {
        if (this.$scope.formErrors && this.$scope.formErrors.length) {
          return this.$scope.formErrors = [];
        }
      });

      if (this.upgradeType === 'extend') {
        this.$scope.toPlan = 1;
      } else {
        this.$scope.toPlan = 100;
      }

      this.currentPlan = null;
      this.$scope.planChanged = newPlan => {
        if (!this.currentPlan || (this.currentPlan === parseInt(newPlan))) { return; }
        return this.refreshForm(newPlan);
      };

      return this.refreshForm();
    }

    refreshForm(plan) {
      if (this.upgradeType === 'extend') {
        return this.refreshRenewForm(plan);
      } else {
        return this.refreshPlanForm(plan);
      }
    }

    refreshPlanForm(plan) {
      this.$scope.initial_loading = true;
      this.planInfo = null;
      this.currentPlan = null;

      return this.DpLicense.getPlanUpgradeInfo(plan || 0).then(info => {
        let name;
        if (info.error_code) {
          console.error(`License server error code: ${info.error_code}`);
          this.$scope.not_online = true;
          return;
        }

        this.planInfo = info;
        this.$scope.planInfo = info;
        this.$scope.initial_loading = false;
        this.$scope.paymentForm.exist_card = info.card_details || null;
        this.$scope.paymentForm.invoice = info.invoice || null;
        this.$scope.availablePlans = info.available_plans.map( x => ({ num: x+"", title: x }));
        this.$scope.toPlan = info.next_plan.agents+"";
        this.currentPlan = info.next_plan.agents;

        if (info.currency_pref === 'usd') {
          name = 'upgrade_cost_total_display';
        } else {
          name = `upgrade_cost_total_${info.currency_pref}_display`;
        }

        this.$scope.paymentSummary = {
          line_title:         `Upgrade license to ${info.next_plan.agents} agents`,
          cost:                   info.next_plan.upgrade_cost,
          cost_display:           info.next_plan.upgrade_cost_display,
          cost_vat:               info.next_plan.upgrade_cost_vat,
          cost_vat_display:       info.next_plan.upgrade_cost_vat_display,
          cost_total:             info.next_plan.upgrade_cost_total,
          cost_total_display:     info.next_plan.upgrade_cost_total_display,
          currency_total_display: info.next_plan[name],
          currency:               info.currency_pref,
          currency_display:       info.currency_pref.toUpperCase(),
          vat_rate:               info.vat_rate,
          has_vat:                info.vat_rate > 0.0,
          invoice_link:           info.invoice ? info.invoice.pdf_link : null,
          invoice_web_link:       info.invoice ? info.invoice.link : null
        };

        if (this.$scope.paymentForm.exist_card) {
          this.$scope.paymentForm.mode = 'exist';
        } else {
          this.$scope.paymentForm.mode = 'new';
        }

        if (!info.allow_inline_form) {
          return this.$scope.not_online = true;
        }
      }
      , () => {
        this.$scope.initial_loading = false;
        return this.$scope.not_online = true;
      });
    }

    refreshRenewForm(plan) {
      this.$scope.initial_loading = true;
      this.planInfo = null;
      this.currentPlan = null;

      return this.DpLicense.getRenewInfo(plan || 0).then(info => {
        let name;
        if (info.error_code) {
          console.error(`License server error code: ${info.error_code}`);
          this.$scope.not_online = true;
          return;
        }

        this.planInfo = info;
        this.$scope.planInfo = info;
        this.$scope.initial_loading = false;
        this.$scope.paymentForm.exist_card = info.card_details || null;
        this.$scope.paymentForm.address = info.address_info || {};
        this.$scope.paymentForm.invoice = info.invoice || null;
        this.$scope.availablePlans = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10].map( x => ({ num: x+"", title: x }));
        this.$scope.toPlan = info.next_plan.years+"";
        this.currentPlan = info.next_plan.years;

        if (info.currency_pref === 'usd') {
          name = 'upgrade_cost_total_display';
        } else {
          name = `upgrade_cost_total_${info.currency_pref}_display`;
        }

        this.$scope.paymentSummary = {
          line_title:             `Renew license for ${info.next_plan.years} years`,
          cost_per_year:          info.current_plan.per_year_cost_display,
          num_agents:             info.current_plan.agents,
          cost:                   info.next_plan.upgrade_cost,
          cost_display:           info.next_plan.upgrade_cost_display,
          cost_vat:               info.next_plan.upgrade_cost_vat,
          cost_vat_display:       info.next_plan.upgrade_cost_vat_display,
          cost_total:             info.next_plan.upgrade_cost_total,
          cost_total_display:     info.next_plan.upgrade_cost_total_display,
          currency_total_display: info.next_plan[name],
          currency:               info.currency_pref,
          currency_display:       info.currency_pref.toUpperCase(),
          vat_rate:               info.vat_rate,
          has_vat:                info.vat_rate > 0.0,
          invoice_link:           info.invoice ? info.invoice.pdf_link : null,
          invoice_web_link:       info.invoice ? info.invoice.link : null
        };

        if (this.$scope.paymentForm.exist_card) {
          this.$scope.paymentForm.mode = 'exist';
        } else {
          this.$scope.paymentForm.mode = 'new';
        }

        if (!info.allow_inline_form) {
          return this.$scope.not_online = true;
        }
      }
      , () => {
        this.$scope.initial_loading = false;
        return this.$scope.not_online = true;
      });
    }

    validate() {
      const errors = [];
      if (this.$scope.paymentForm.mode === 'new') {
        const card = this.$scope.paymentForm.new_card;
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

        const addy = this.$scope.paymentForm.address;

        if (!addy.country) {
          errors.push('country');
        }
        if (!addy.address || !addy.address.length) {
          errors.push('address');
        }
        if (!addy.city || !addy.city.length) {
          errors.push('city');
        }
        if ((!addy.state || !addy.state.length) && (addy.country === 'US')) {
          errors.push('state');
        }
        if (!addy.post_code || !addy.post_code.length) {
          errors.push('post_code');
        }
      }

      this.$scope.formErrors = errors;

      return errors.length === 0;
    }

    sendPayment() {
      if (!this.validate()) { return; }
      this.$scope.loading = true;
      this.$scope.phase = 2;
      this.$scope.stepId = 0;
      this.$scope.error_code = null;

      return this.DpLicense.sendPayInvoiceRequest(this.$scope.paymentForm.mode, this.$scope.paymentForm.new_card, this.$scope.paymentForm.address, this.planInfo.invoice.id, this.planInfo.invoice.auth).then( data => {

        if (!data.success) {
          this.$scope.phase = 1;
          this.$scope.loading = false;
          if (data.error_message) {
            this.$scope.formErrors = [data.error_message];
          } else {
            this.$scope.formErrors = ["There was a problem processing your payment. Please try agian."];
          }
          return;
        }

        this.$scope.stepId = 1;

        return this.DpLicense.getNewLicenseKey().then( res => {
          this.$scope.stepId = 2;

          return this.DpLicense.setNewLicenseCode(res.license_code).then(() => {
            this.$scope.loading = false;
            this.$scope.phase = 3;
            return this.$scope.show_done = true;
          }
          , function() {
            this.$scope.loading = false;
            return this.$scope.error_code = 'failed_get_lic_key';
          });
        }
        , function() {
          this.$scope.loading = false;
          return this.$scope.error_code = 'failed_get_lic_key';
        });
      }

      , data => {
        this.$scope.loading = false;
        this.$scope.phase = 1;
        return this.$scope.formErrors = ["There was a problem processing your payment."];
      });
    }
  }
  Admin_License_Ctrl_UpgradeLicenseModal.initClass();

  return Admin_License_Ctrl_UpgradeLicenseModal.EXPORT_CTRL();
});