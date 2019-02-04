define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/Usersources/Helper/UsersourceTypeDecider', 'moment']
, (Admin_Ctrl_Base, Util, Admin_Usersources_Helper_UsersourceTypeDecider, moment) => {
  class Admin_Usersources_Ctrl_SyncInformation extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Usersources_Ctrl_SyncInformation';
      this.CTRL_AS = 'Ctrl';
      this.DEPS = ['$http', 'dpTemplateManager', '$interval'];
    }


    init() {
      this.instanceId = this.$stateParams.id;
      this.permission_groups = [];
      this.$scope.getController = () => this;
      this.$scope.setPresaveCallback = callback => this.presaveCallback = callback;
      this.$scope.enableCustomFooter = false;
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.presaveCallback = null;
      this.app = null;
      return this.$scope.$on('$destroy', () => this.interval && this.$interval.cancel(this.interval));
    }


    initialLoad() {
      const promise = this.refresh();

      this.interval = this.$interval(() => this.refresh()
      , 10000);

      return promise;
    }

    refresh() {
      const d = this.$q.defer();
      const d2 = this.$q.defer();

      this.Api.sendDataGet({
        app: `/apps/instances/${this.instanceId}`
      }).then((result) => {
        this.app = result.data.app != null ? result.data.app.app : undefined;

        this.$scope.app = this.app;
        this.$scope.appId = this.app != null ? this.app.id : undefined;

        return this.Api.sendDataGet({
          extra_info: `/usersources/${this.usersourceType}/app-${this.instanceId}/extra-details`,
          sync_info:  `/usersources/sync/info/${this.instanceId}`,
          pack:       `/apps/packages/${this.app.package_name}`
        }).then((result) => {
          this.pack = result.data.pack.package;
          this.$scope.usersource_details = result.data.extra_info != null ? result.data.extra_info.usersource_details : undefined;
          this.packageName = this.pack.name;
          this.$scope.pack = this.pack;
          this.$scope.setting_values = this.app.settings;
          if (!this.$scope.setting_values || Util.isArray(this.$scope.setting_values)) {
            this.$scope.setting_values = {};
          }
          this.$scope.setting_values.dp_app = { title: this.app.title };

          const { sync_log } = result.data.sync_info;

          if (sync_log) {
            if (!sync_log.phase_1_running) {
              sync_log.phase_1_time_readable = moment(sync_log.date_start).from(sync_log.date_end, true);
            } else {
              sync_log.phase_1_time_readable = '-';
            }

            if (sync_log.phase_2_show) {
              if (!sync_log.phase_2_running) {
                sync_log.phase_2_time_readable = moment(sync_log.date_phase_2_start).from(sync_log.date_phase_2_end, true);
              } else {
                sync_log.phase_2_time_readable = '-';
              }
            }
          }

          this.$scope.sync_log = sync_log;
          this.$scope.ListCtrl = this.listCtrl();

          return d.resolve();
        });
      });
      return d.promise;
    }

    listCtrl() {
      return (this.$scope.$parent != null ? this.$scope.$parent.ListCtrl : undefined) || { running_now: false, refresh: () => {} };
    }
  }
  Admin_Usersources_Ctrl_SyncInformation.initClass();

  return Admin_Usersources_Ctrl_SyncInformation.EXPORT_CTRL();
});
