define([
  'Admin/Main/Ctrl/Base',
  'Admin/Usersources/Helper/UsersourceTypeDecider',
  'moment'
], function(
  Admin_Ctrl_Base,
  Admin_Usersources_Helper_UsersourceTypeDecider,
  moment
) {
  class Admin_Usersources_Ctrl_UsersourcesList extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID = 'Admin_Usersources_Ctrl_UsersourcesList';
      this.CTRL_AS = 'ListCtrl';
      this.DEPS = ['$state', 'Growl', '$q', '$interval'];
    }

    init() {
      this.usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(this.$state);
      this.usersourcesDataService = this.DataService.get('Usersources');
      this.show_sync_section = false;
      this.show_url = this.usersourceType === 'user' ? 'crm.usersources.id' : 'agents.usersources.id';
      this.sync_url = this.usersourceType === 'user' ? 'crm.usersources.sync' : 'agents.usersources.sync';
      this.new_url = this.usersourceType === 'user' ? 'crm.usersources.new' : 'agents.usersources.new';
      this.sync_status = null;
      this.sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) => {
          const $list = data.item.closest('ul');
          const displayOrders = [];
          $list.find('li').each(function() {
            return displayOrders.push(parseInt($(this).data('id')));
          });
          this.usersourcesDataService.saveDisplayOrder(displayOrders);
          return this.pingElement('display_orders');
        }
      };
      return this.$scope.$on('$destroy', () => this.interval && this.$interval.cancel(this.interval));
    }

    initialLoad() {
      const promise = this.refresh();

      this.interval = this.$interval(() => {
        return this.refresh();
      }
      , 10000);

      return promise;
    }

    updateAppTitle(id, title) {
      this.usersources.filter(x => (x.app != null ? x.app.id : undefined) === id).map(x => x.usersource.title = title);
      return this.refresh();
    }

    refresh() {
      const d = this.$q.defer();

      this.Api.sendDataGet({
        us: `/usersources/${this.usersourceType}`,
        sync_status: '/usersources/sync/status'
      }).then(result => {
        this.sync_status = result.data.sync_status;
        if (this.sync_status.next_sync) {
          this.sync_next_text = moment(this.sync_status.next_sync).format('MMM D, YYYY @ HH:mm');
        } else {
          this.sync_next_text = 'scheduling';
        }
        this.usersources = result.data.us.usersources;
        this.show_sync_section = true;
        let count_syncing = 0;
        for (let us of Array.from(this.usersources)) {
          if (us.usersource.sync_enabled) {
            count_syncing++;
          }
        }
        if (count_syncing) {
          this.show_sync_section = true;
        } else {
          this.show_sync_section = false;
        }

        return d.resolve();
      });

      return d.promise;
    }

    startSync() {
      return this.Api.sendPost('/usersources/sync/start').then(result => {
        if (result.data.success) {
          this.refresh();
          return this.Growl.success(this.getRegisteredMessage('usersource_sync_starting') || 'Starting sync job. It will begin shortly.');
        }
      });
    }

    stopSync() {
      return this.Api.sendPost('/usersources/sync/stop').then(result => {
        if (result.data.success) {
          this.refresh();
          return this.Growl.success(this.getRegisteredMessage('usersource_sync_stopping') || 'Aborted sync jobs.');
        }
      });
    }
  }
  Admin_Usersources_Ctrl_UsersourcesList.initClass();

  return Admin_Usersources_Ctrl_UsersourcesList.EXPORT_CTRL();
});