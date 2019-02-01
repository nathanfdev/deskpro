define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Strings'
], (
  Admin_Ctrl_Base,
  Strings
) => {
  class Admin_Cloud_Main_Ctrl_Home extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        const thisFn = (() => this).toString();
        const thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.pollFeatures = this.pollFeatures.bind(this);
      this.actualPoll = this.actualPoll.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Cloud_Main_Ctrl_Home';
      this.CTRL_AS   = 'Home';
      this.DEPS      = ['$http'];
    }

    init() {
      this.online_agents = [];
      this.offline_agents = [];
      this.$scope.new_agent = {};
      this.features = {};
      this.$scope.keys = Object.keys;
    }

    initialLoad() {
      const promise = this.Api.sendDataGet({
        agents:      '/agents',
        lastLogin:   '/me/last-login',
        quickStats:  '/tickets/quick-stats',
        lic_info:    '/dp_license',
        errorStatus: '/server/error-status',
      }).then((result) => {
        const { data } = result;
        this.online_agents  = [];
        this.offline_agents = [];
        this.quick_stats    = result.data.quickStats;
        this.last_login     = result.data.lastLogin.last_login;
        this.error_status   = result.data.errorStatus;

        if (this.last_login) {
          this.last_login.date_created_d = new Date(this.last_login.date_created_ts * 1000);
        }

        this.license = result.data.lic_info.license;

        for (const agent of Array.from(data.agents.agents)) {
          if (agent.is_online_now || (agent.id === DP_PERSON_ID)) {
            this.online_agents.push(agent);
          } else {
            this.offline_agents.push(agent);
          }
        }

        const problem_triggers = [
          (this.error_status != null ? this.error_status.gateway_error_count : undefined) > 0,
          (this.error_status != null ? this.error_status.sendmail_error_count : undefined) > 0,
        ];

        return this.is_server_problem = problem_triggers.filter(x => !!x).length > 0;
      });

      this.Api2.sendGet('features').then(res => (res.data.data != null ? res.data.data.forEach((feature) => {
        if (feature.processing) {
          this.pollFeatures();
        }
        return this.features[feature.id] = feature;
      }) : undefined));

      return promise;
    }

    pollFeatures() {
      if (!this.pollTimer) {
        return this.pollTimer = setTimeout(this.actualPoll, 60000);
      }
    }

    actualPoll() {
      return this.Api2.sendGet('features').then((res) => {
        this.pollTimer = null;
        return res.data.data.forEach((feature) => {
          if ((this.features[feature.id].processing === true) && (feature.processing === false)) {
            const enDisStr = feature.enabled ? 'enabled' : 'disabled';
            this.Growl.success(`Feature ${feature.title} successfully ${enDisStr}!`);
          }
          if (feature.processing) { this.pollFeatures(); }
          return this.features[feature.id] = feature;
        });
      });
    }

    /*
     * Saves new agent form
     */
    addNewAgent() {
      this.$scope.created_agent = null;

      const postData = {
        agent: {
          name:   Strings.trim(this.$scope.new_agent.name || ''),
          emails: [Strings.trim(this.$scope.new_agent.email || '')]
        }
      };

      this.$scope.new_agent.errors = {
        name:  !postData.agent.name,
        email: postData.agent.emails[0].indexOf('@') === -1
      };

      if (this.$scope.new_agent.errors.name || this.$scope.new_agent.errors.email) {
        return;
      }

      this.startSpinner('saving_new_agent');
      return this.Api.sendPutJson('/agents', postData).then(() => this.stopSpinner('saving_new_agent').then(() => {
        this.$scope.created_agent = this.$scope.new_agent;
        return this.$scope.new_agent = {};
      }));
    }

    /*
     * Sends support request
     */
    sendSupportRequest() {
      const { submit_ticket } = this.$scope;

      const contact = {
        subject: Strings.trim(submit_ticket.subject || ''),
        message: Strings.trim(submit_ticket.message || ''),
        email:   Strings.trim(submit_ticket.email   || '')
      };

      if (!contact.message) {
        this.$scope.submit_ticket_message_error = true;
        return;
      }

      if (this.$scope.submit_ticket_defaultemail || (contact.email.indexOf('@') === -1)) {
        delete contact.email;
        this.$scope.submit_ticket_defaultemail = true;
      }

      this.startSpinner('sending_support_request');
      return this.Api.sendPostJson('/dp_license/support-request', { contact }).then(() => this.stopSpinner('sending_support_request').then(() => this.$scope.support_sent = true));
    }
  }
  Admin_Cloud_Main_Ctrl_Home.initClass();

  return Admin_Cloud_Main_Ctrl_Home.EXPORT_CTRL();
});
