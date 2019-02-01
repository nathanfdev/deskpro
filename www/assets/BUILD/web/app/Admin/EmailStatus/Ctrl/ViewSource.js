define(['Admin/Main/Ctrl/Base', 'moment'], function(Admin_Ctrl_Base, moment) {
  class Admin_EmailStatus_Ctrl_ViewSource extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_EmailStatus_Ctrl_ViewSource';
      this.CTRL_AS = 'ViewSource';
      this.DEPS    = ['$state', '$modal', 'DpDateService', '$sce'];
    }

    init() {
      this.sourceId = parseInt(this.$stateParams.id);
      this.$scope.ds = this.DpDateService;
      this.$scope.render_type = 'raw';
      this.rendered = {
        summary_loaded: false,
        rendered_loaded: false
      };

      this.$scope.$watch('render_type', () => this.updateRenderType());

      this.$scope.showStatusHelp = () => {
        return this.$modal.open({
          templateUrl: this.getTemplatePath('EmailStatus/emailsource-status-code-modal.html'),
          controller: ['$scope', '$modalInstance', ($scope, $modalInstance) =>
            $scope.dismiss = () => $modalInstance.dismiss()
          
          ]
        });
      };

    }

    initialLoad() {
      return this.Api.sendGet(`/email_status/sources/${this.sourceId}?with_raw=1`).then( res => {
        this.source         = res.data.source;
        this.source_raw     = res.data.source_raw;
        this.source_log     = res.data.source_log;
        this.account_log    = res.data.account_log;
        this.source_info    = res.data.source_info;
        this.ticket         = res.data.ticket;
        return this.ticket_message = res.data.ticket_message;
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
          return this.Api.sendGet(`/email_status/sources/${this.sourceId}/summary`).success( data => {
            this.$scope.loading_render_type = false;
            this.rendered.summary_loaded = true;
            return this.rendered.summary = data.summary;
          });
        case 'rendered':
          if (this.rendered.rendered_loaded) { return; }
          this.$scope.loading_render_type = true;
          return this.Api.sendGet(`/email_status/sources/${this.sourceId}/rendered`).success( data => {
            this.$scope.loading_render_type  = false;
            this.rendered.rendered_loaded    = true;
            this.rendered.text               = data.text || null;
            return this.rendered.html               = data.html ? this.$sce.trustAsHtml(data.html) : null;
          });
      }
    }

    delete() {
      return this.Api.sendDelete(`/email_status/sources/${this.sourceId}`);
    }

    reprocess() {
      return this.Api.sendPost(`/email_status/sources/${this.sourceId}/reprocess`);
    }

    startDelete() {
      const doDelete = () => {
        return this.delete().then( () => {
          return this.$state.go('emails.ticket_accounts.emailsources');
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('EmailStatus/emailsource-delete-modal.html'),
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

    startReprocess() {
      const doReprocess = () => {
        return this.reprocess().then( () => {
          return this.$state.go('emails.ticket_accounts.goemailsourcesview', {id: this.sourceId});
        });
      };

      return this.$modal.open({
        templateUrl: this.getTemplatePath('EmailStatus/emailsource-reprocess-modal.html'),
        controller: ['$scope', '$modalInstance', function($scope, $modalInstance) {
          $scope.dismiss = () => $modalInstance.dismiss();

          return $scope.doReprocess = function() {
            $scope.is_loading = true;
            return doReprocess().then(() => $modalInstance.dismiss());
          };
        }
        ]
      });
    }
  }
  Admin_EmailStatus_Ctrl_ViewSource.initClass();

  return Admin_EmailStatus_Ctrl_ViewSource.EXPORT_CTRL();
});
