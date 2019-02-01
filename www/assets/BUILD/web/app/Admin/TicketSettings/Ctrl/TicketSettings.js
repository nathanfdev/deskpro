define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'angular'], (Admin_Ctrl_Base, Util, angular) => {
  class Admin_TicketSettings_Ctrl_TicketSettings extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_TicketSettings_Ctrl_TicketSettings';
      this.CTRL_AS   = 'TicketSettings';
      this.DEPS      = ['$modal'];
    }

    init() {
      this.settings = null;
      this.$scope.escalation_days = 3;

      this.$scope.editSatisfactionTemplate = () => {
        console.log;
        if (window.DP_HAS_NEW_EMAILS) {
          return this.$modal.open({
            templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-new-email-editor.html`,
            size:        'lg',
            controller:  'Admin_Templates_Ctrl_NewEmailTemplateEditor',
            resolve:     {
              templateName() { return 'SendmailBundle:emails_user:ticket_rate.html.twig'; }
            }
          });
        }
        return this.$modal.open({
          templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Templates/modal-email-editor.html`,
          controller:  'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve:     {
            templateName() { return 'DeskPRO:emails_user:ticket-rate.html.twig'; }
          }
        });
      };

      this.$scope.$watch(
        () => (this.$scope.settings != null ? this.$scope.settings.timelog_autostart : undefined)
        , (newVal, oldVal) => {
          if (newVal === false) { return this.$scope.settings.billing_on_reply = false; }
        });

      this.$scope.digits = [];
      return [0, 1, 2, 3, 4, 5, 6, 7, 8].map(i =>
        (i !== 1) ?
          this.$scope.digits.push({ id: i, label: `${i} digits` })
        :
          this.$scope.digits.push({ id: i, label: `${i} digit` }));
    }


    initialLoad() {
      const data_promise = this.Api.sendDataGet({
        settings: '/ticket_settings'
      }).then((res) => {
        const settings = res.data.settings.ticket_settings;
        for (const k of Object.keys(settings.agent_defaults || {})) {
          const v = settings.agent_defaults[k];
          if (!v) { settings.agent_defaults[k] = '0'; }
        }

        const days = [null, false, false, false, false, false, false, false];
        for (const day of Array.from(settings.working_hours.work_days)) {
          days[day] = true;
        }

        settings.working_hours.work_days = days;

        this.$scope.settings = settings;
        return this.settings = angular.copy(this.$scope.settings);
      });

      this.headerSortList = {
        axis:   'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');

          const newOrder = [];
          $list.find('li').each(function () {
            return newOrder.push($(this).data('value'));
          });

          return this.$scope.settings.from_email_headers = newOrder;
        }
      };

      return this.$q.all([data_promise]);
    }

    isDirtyState() {
      return false;
      if (!this.settings) { return false; }
      if (!angular.equals(this.settings, this.$scope.settings)) {
        return true;
      }
      return false;
    }

    save() {
      const postData = {
        ticket_settings: Util.clone(this.$scope.settings, true)
      };

      const work_days = [];
      for (let day = 0; day < this.$scope.settings.working_hours.work_days.length; day++) {
        const enabled = this.$scope.settings.working_hours.work_days[day];
        if (enabled) {
          work_days.push(day);
        }
      }

      postData.ticket_settings.working_hours.work_days = work_days;

      this.startSpinner('saving');
      const promise = this.Api.sendPostJson('/ticket_settings', postData).success(() => {
        this.settings = angular.copy(this.$scope.settings);

        return this.stopSpinner('saving').then(() => this.Growl.success(this.getRegisteredMessage('saved_settings')));
      }).error((info, code) => {
        this.stopSpinner('saving', true);
        return this.applyErrorResponseToView(info);
      });

      return this.$scope.$broadcast('trigger.save');
    }
  }
  Admin_TicketSettings_Ctrl_TicketSettings.initClass();

  return Admin_TicketSettings_Ctrl_TicketSettings.EXPORT_CTRL();
});
