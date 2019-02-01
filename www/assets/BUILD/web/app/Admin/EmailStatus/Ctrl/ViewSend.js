define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
  class Admin_EmailStatus_Ctrl_ViewSend extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSend';
      this.CTRL_AS = 'ViewSource';
      this.DEPS    = ['$state', '$modal', 'DpDateService', '$sce'];
    }

    init() {
      this.sendmailId = parseInt(this.$stateParams.id);
      this.$scope.ds = this.DpDateService;
      this.$scope.render_type = 'raw';
      this.rendered = {
        summary_loaded: false,
        rendered_loaded: false
      };

      this.$scope.$watch('render_type', () => this.updateRenderType());

    }

    initialLoad() {
      return this.Api.sendGet(`/email_status/sendmail/${this.sendmailId}?with_raw=1`).then( res => {
        this.sendmail     = res.data.sendmail;
        this.sendmail_raw = res.data.sendmail_raw;
        this.statuses     = res.data.statuses;
        this.log          = res.data.sendmail_log;
        this.sendmail.date_created = this.DpDateService.local(this.sendmail.date_created);
        if (this.sendmail.date_sent) {
          this.sendmail.date_sent = this.DpDateService.local(this.sendmail.date_sent);
        }
        if (this.sendmail.date_next_attempt) {
          return this.sendmail.date_next_attempt = this.DpDateService.local(this.sendmail.date_next_attempt);
        }
      });
    }

    updateRenderType() {
      const type = this.$scope.render_type;
      this.$scope.loading_render_type = false;

      switch (type) {
        case 'raw': return;
        case 'summary':
          if (this.rendered.summary_loaded) { return; }
          this.$scope.loading_render_type = true;
          return this.Api.sendGet(`/email_status/sendmail/${this.sendmailId}/summary`).success( data => {
            this.$scope.loading_render_type = false;
            this.rendered.summary_loaded = true;
            return this.rendered.summary = data.summary;
          });
        case 'rendered':
          if (this.rendered.rendered_loaded) { return; }
          this.$scope.loading_render_type = true;
          return this.Api.sendGet(`/email_status/sendmail/${this.sendmailId}/rendered`).success( data => {
            this.$scope.loading_render_type  = false;
            this.rendered.rendered_loaded    = true;
            this.rendered.text               = data.text || null;
            return this.rendered.html               = data.html ? this.$sce.trustAsHtml(data.html) : null;
          });
      }
    }

    delete() {
      return this.Api.sendDelete(`/email_status/sendmail/${this.sendmailId}`);
    }

    resend() {
      return this.Api.sendPost(`/email_status/sendmail/${this.sendmailId}/resend`);
    }

    startDelete() {
      const doDelete = () => {
        return this.delete().then( () => {
          return this.$state.go('emails.ticket_accounts.sendmailqueue');
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('EmailStatus/sendmail-delete-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doDelete = function() {
            $scope.is_loading = true;
            return doDelete().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }

    startResend() {
      const doResend = () => {
        return this.resend().then( () => {
          return this.$state.go('emails.ticket_accounts.gosendmailview', {id: this.sendmailId});
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('EmailStatus/sendmail-resend-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doResend = function() {
            $scope.is_loading = true;
            return doResend().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }
  }
  Admin_EmailStatus_Ctrl_ViewSend.initClass();

  return Admin_EmailStatus_Ctrl_ViewSend.EXPORT_CTRL();
});