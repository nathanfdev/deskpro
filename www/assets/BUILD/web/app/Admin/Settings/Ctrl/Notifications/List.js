// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
  class Admin_Settings_Ctrl_Notifications extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.save = this.save.bind(this);
      this.testPusher = this.testPusher.bind(this);
      this.testDeskpro = this.testDeskpro.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Settings_Ctrl_Notifications';
      this.CTRL_AS   = 'Notifications';
      this.DEPS      = ['Api2', 'Growl'];
    }

    init() {
      this.$scope.mode = 'db';
      this.$scope.deskpro = {};
      this.$scope.pusher = {
        clusters: [
          { title: 'mt1 (us-west-1)', value: 'mt1' },
          { title: 'eu (eu-west-1)', value: 'eu' },
          { title: 'ap1 (ap-southwest-1)', value: 'ap1' },
          { title: 'ap2 (ap-south-1)', value: 'ap2' }
        ]
      };
    }

    initialLoad() {
      return this.Api2.sendGet('/notify/setup/action-alerts/clients').then( response => {
          const clients = response.data.data;
          this.$scope.mode                  = clients.mode;

          this.$scope.pusher.id             = clients.pusher.id;
          this.$scope.pusher.secret         = clients.pusher.secret;
          this.$scope.pusher.key            = clients.pusher.key;
          this.$scope.pusher.currentCluster = clients.pusher.cluster;

          this.$scope.deskpro.secret         = clients.deskpro.secret;
          this.$scope.deskpro.host           = clients.deskpro.host;
          this.$scope.deskpro.port           = clients.deskpro.port;
          return this.$scope.deskpro.secure         = clients.deskpro.secure;
      });
    }

    getPusherParams() {
      const params = {
        key:     this.$scope.pusher.key,
        id:      this.$scope.pusher.id,
        secret:  this.$scope.pusher.secret,
        cluster: this.$scope.pusher.currentCluster
      };
      return params;
    }

    getDeskproParams() {
      const params = {
        port:   this.$scope.deskpro.port,
        host:   this.$scope.deskpro.host,
        secret: this.$scope.deskpro.secret,
        secure: this.$scope.deskpro.secure
      };
      return params;
    }

    save() {

      let params;
      if(this.$scope.mode === 'deskpro') {
        params = this.getDeskproParams();
      } else if (this.$scope.mode === 'pusher') {
        params = this.getPusherParams();
      } else {
        params = {};
      }

      params.mode = this.$scope.mode;

      return this.Api2.sendPutJson('/notify/setup/action-alerts/clients', params).success(
        () => this.Growl.success('Your request is successful')
      ).error(
        () => {
          return this.Growl.error('Something went wrong. Please contact your administrator');
      });
    }

    testPusher() {
      this.$scope.pusherTestResult = "Testing settings ...";

      const params = this.getPusherParams();
      return this.Api2.sendPostJson('/notify/setup/action-alerts/pusher/test', params).then( res => {
        if (res.data.success) {
          return this.$scope.pusherTestResult = `Success. Settings are OK.\n\n----- Log -----\n\n${res.data.message}`;
        } else {
          return this.$scope.pusherTestResult = `FAILED. Settings are INVALID..\n\n----- Log -----\n\n${res.data.message}`;
        }
      }
      , () => {
        return this.$scope.pusherTestResult = "FAILED :: The test did not complete successfully";
      });
    }

    testDeskpro() {
      this.$scope.deskproTestResult = "Testing settings ...";

      const params = this.getDeskproParams();
      return this.Api2.sendPostJson('/notify/setup/action-alerts/deskpro/test', params).then( res => {
        if (res.data.success) {
          return this.$scope.deskproTestResult = `Success. Settings are OK.\n\n----- Log -----\n\n${res.data.message}`;
        } else {
          return this.$scope.deskproTestResult = `FAILED. Settings are INVALID..\n\n----- Log -----\n\n${res.data.message}`;
        }
      }
      , () => {
        return this.$scope.deskproTestResult = "FAILED :: The test did not complete successfully";
      });
    }
  }
  Admin_Settings_Ctrl_Notifications.initClass();

  return Admin_Settings_Ctrl_Notifications.EXPORT_CTRL();
});