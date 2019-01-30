/*
 * decaffeinate suggestions:
 * DS001: Remove Babel/TypeScript constructor workaround
 * DS101: Remove unnecessary use of Array.from
 * DS102: Remove unnecessary code created because of implicit returns
 * DS203: Remove `|| {}` from converted for-own loops
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'Admin/Main/Ctrl/Base',
  'DeskPRO/Util/Strings'
], function(
  Admin_Ctrl_Base,
  Strings
) {
  class Admin_Main_Ctrl_Home extends Admin_Ctrl_Base {
    constructor(...args) {
      {
        // Hack: trick Babel/TypeScript into allowing this before super.
        if (false) { super(); }
        let thisFn = (() => { return this; }).toString();
        let thisName = thisFn.slice(thisFn.indexOf('return') + 6 + 1, thisFn.indexOf(';')).trim();
        eval(`${thisName} = this;`);
      }
      this.deleteLogFile = this.deleteLogFile.bind(this);
      this.pollFeatures = this.pollFeatures.bind(this);
      this.actualPoll = this.actualPoll.bind(this);
      super(...args);
    }

    static initClass() {
      this.CTRL_ID   = 'Admin_Main_Ctrl_Home';
      this.CTRL_AS   = 'Home';
      this.DEPS      = ['$http', 'DpLicense', 'Growl'];
    }

    init() {
      this.online_agents = [];
      this.offline_agents = [];
      this.$scope.new_agent = {};
      this.$scope.hide_admin_upgrade_notice = window.hide_admin_upgrade_notice || false;
      this.$scope.readable_config = false;
      this.$scope.missconfigure_web_root = false;
      this.$scope.missed_fk_found = false;
      this.$scope.valid_url = true;

      this.online_agents  = [];
      this.offline_agents = [];
      this.unactive_agents = [];
      this.agentsMap = {};
      this.features = {};
      this.service =
        {agents: this.DataService.get('Agents')};
      this.$scope.keys = Object.keys;

      this.loadConfigPhpTest();
      this.loadMethodTests();
    }

    initialLoad() {
      let defaultPort;
      let w = window;
      if (w.parent !== window) {
        w = w.parent;
      }
      const l = w.location;
      if (l.protocol.replace(':', '') === 'http') {
        defaultPort = 80;
      } else {
        defaultPort = 443;
      }
      this.Api.sendGet('check_url', {scheme: l.protocol.replace(':', ''), host: encodeURIComponent(l.hostname), port: l.port || defaultPort}).then(res => {
        return this.$scope.valid_url = res.data.valid;
      });

      this.refreshAgents();
      const promise = this.Api.sendDataGet({
        lastLogin:   '/me/last-login',
        versionInfo: '/dp_license/version-info'
      }).then( result => {
        this.version_info   = result.data.versionInfo;
        this.last_login     = result.data.lastLogin.last_login;

        if (this.last_login) {
          return this.last_login.date_created_d = new Date(this.last_login.date_created_ts * 1000);
        }
      });

      this.Api.sendDataGet({
        cronStatus:   '/server/cron-status',
        errorStatus:  '/server/error-status',
        apcStatus:    '/server/apc-status',
        quickStats:   '/tickets/quick-stats',
      }).then( result => {
        this.cron_status    = result.data.cronStatus;
        this.error_status   = result.data.errorStatus;
        this.apc_status     = result.data.apcStatus;
        this.quick_stats    = result.data.quickStats;

        const problem_triggers = [
          (this.cron_status != null ? this.cron_status.is_problem : undefined),
          (this.error_status != null ? this.error_status.error_count : undefined) > 0,
          (this.error_status != null ? this.error_status.error_log_size : undefined),
          (this.error_status != null ? this.error_status.gateway_error_count : undefined) > 0,
          (this.error_status != null ? this.error_status.sendmail_error_count : undefined) > 0,
          (this.apc_status != null ? this.apc_status.is_problem : undefined)
        ];
        this.is_server_problem = problem_triggers.filter(x => !!x).length > 0;
        return this.$scope.missed_fk_found = this.error_status.missed_fk_found;
      });

      // Get news and version info in parallel
      this.Api.sendDataGet({
        latestVersion: '/dp_license/latest-version-info',
        news: '/dp_license/news'
      }).then( result => {
        if (((result.data.latestVersion != null ? result.data.latestVersion.version_info : undefined) == null)) {
          this.latest_version_status = "error";
        } else {
          this.latest_version_status = "okay";
          this.latest_version = result.data.latestVersion.version_info;
          this.latest_version.count_behind = parseInt(result.data.latestVersion.version_info.count_behind) || 0;

          if (this.latest_version.build_name.indexOf(this.version_info.build_name) === -1) {
            this.latest_version.build_name = this.latest_version.build_name.split(/\.(?=[^\.]+$)/)[0];
          }
        }

        if (((result.data.news != null ? result.data.news.news : undefined) == null)) {
          return this.news_status = "error";
        } else {
          this.news_status = "okay";
          return this.news = result.data.news.news;
        }
      });

      this.Api2.sendGet('features').then( res => {
        return (res.data.data != null ? res.data.data.forEach( feature => {
          if (feature.processing) {
            this.pollFeatures();
          }
          return this.features[feature.id] = feature;
        }) : undefined);
      });

      return promise;
    }

    deleteLogFile(e) {
      return this.Api.sendDelete('/server_error_logs').then( () => {
        this.error_status.error_log_size = false;

        const problem_triggers = [
          (this.cron_status != null ? this.cron_status.is_problem : undefined),
          (this.error_status != null ? this.error_status.error_count : undefined) > 0,
          (this.error_status != null ? this.error_status.gateway_error_count : undefined) > 0,
          (this.error_status != null ? this.error_status.sendmail_error_count : undefined) > 0,
          (this.apc_status != null ? this.apc_status.is_problem : undefined)
        ];
        return this.is_server_problem = problem_triggers.filter(x => !!x).length > 0;
      });
    }


    pollFeatures() {
      if (!this.pollTimer) {
        return this.pollTimer = setTimeout(this.actualPoll, 60000);
      }
    }

    actualPoll() {
      return this.Api2.sendGet('features').then( res => {
        this.pollTimer = null;
        return res.data.data.forEach(feature => {
          if ((this.features[feature.id].processing === true) && (feature.processing === false)) {
            const enDisStr = feature.enabled ? 'enabled' : 'disabled';
            this.Growl.success(`Feature ${feature.title} successfully ${enDisStr}!`);
          }
          if (feature.processing) { this.pollFeatures(); }
          return this.features[feature.id] = feature;
        });
      });
    }

    // todo just move agent object from one array to another when BaseListEdit will be able to handle model objects updates after reload
    refreshAgents() {
      return this.service.agents.all(true).then(agents => {
        for (let agent of Array.from(agents)) {
          // if we see this agent for the first time
          if ((this.agentsMap[agent.id] == null)) {
            if (agent.is_online_now || (agent.id === DP_PERSON_ID)) {
              this.online_agents.push(agent);
              this.agentsMap[agent.id] = 'online_agents';
            } else if ((agent.date_last_login == null)) {
              this.unactive_agents.push(agent);
              this.agentsMap[agent.id] = 'unactive_agents';
            } else {
              this.offline_agents.push(agent);
              this.agentsMap[agent.id] = 'offline_agents';
            }

          // or we already stored this agent in @agentsMap
          } else {
            // state - is the new state of agent
            let state = 'offline_agents';
            if (agent.is_online_now || (agent.id === DP_PERSON_ID)) {
              state = 'online_agents';
            } else if ((agent.date_last_login == null)) {
              state = 'unactive_agents';
            }

            // if agent state changed
            if (this.agentsMap[agent.id] !== state) {
              const list = this[this.agentsMap[agent.id]];
              let index = -1;

              // then find agent index in old state array
              for (let i = 0; i < list.length; i++) {
                const _agent = list[i];
                if (_agent.id === agent.id) {
                  index = i;
                  break;
                }
              }

              // then remove it if found
              if (-1 !== index) {
                list.splice(index, 1);
              }

              // and push to new state array
              this[state].push(agent);
              this.agentsMap[agent.id] = state;
            }
          }
        }


        return this.$timeout((() => this.refreshAgents()), 60 * 1000);
      });
    }

    loadConfigPhpTest() {
      let portStr;
      const checkUrl = DP_BASE_URL + 'app/run/test_ping.html';

      if (location.port) {
        portStr = ':';
        portStr += location.port;
      } else {
        portStr = '';
      }
      this.$scope.config_php_url = location.protocol+'//'+location.hostname+portStr+checkUrl;

      return this.$http({
        method: 'GET',
        url: checkUrl + '?x=' + ((new Date()).getTime()),
        responseType: "text",
        cache: false
      }).success(res => {
        if (!res || res.success) { return; }
        if ((typeof res === 'string') && (res.indexOf('DESKPRO_PONG') !== -1) && (res.indexOf('<!--') !== -1)) {
          this.$scope.readable_config = true;
        }
        if ((typeof res === 'string') && (res.indexOf('OK') !== 0)) {
          return this.$scope.missconfigured_web_root = true;
        }
      }).error( () => {
        return this.$scope.missconfigured_web_root = true;
      });
    }

    loadMethodTests() {
      const promises = [];
      const http_method = {};
      const { $http } = this;

      const checkUrl = DP_BASE_URL + '__serverinfo/check_http_methods?x=' + ((new Date()).getTime());

      const makeCheck = function(type) {
        const typeU = type.toUpperCase();
        const p = $http({
          method: typeU,
          url: checkUrl,
          responseType: "text",
          cache: false
        });

        p.success( function(res) {
          if (!res) {
            res = '';
          }
          let method = "HTTP_METHOD_";
          method += typeU;
          if (res.indexOf(method) !== -1) {
            return http_method[type] = true;
          } else {
            return http_method[type] = false;
          }
        });
        p.error(() => http_method[type] = false);

        return p;
      };

      promises.push(makeCheck('get'));
      promises.push(makeCheck('post'));
      promises.push(makeCheck('put'));
      promises.push(makeCheck('delete'));

      const masterP = this.$q.all(promises);
      const checkRes = () => {
        this.$scope.http_method_checks = http_method;

        let any = false;
        for (let k of Object.keys(http_method || {})) {
          const v = http_method[k];
          if (!v) {
            any = true;
            break;
          }
        }

        return this.$scope.http_method_errors = any;
      };

      return masterP.then(checkRes, checkRes);
    }

    /*
     * Saves new agent form
     */
    addNewAgent() {
      this.$scope.created_agent = null;

      const postData = {
        quick_add: true,
        agent: {
          name: Strings.trim(this.$scope.new_agent.name || ''),
          emails: [Strings.trim(this.$scope.new_agent.email || '')]
        }
      };

      this.$scope.new_agent.errors = {
        name: !postData.agent.name,
        email: postData.agent.emails[0].indexOf('@') === -1
      };

      if (this.$scope.new_agent.errors.name || this.$scope.new_agent.errors.email) {
        return;
      }

      this.startSpinner('saving_new_agent');
      return this.Api.sendPutJson('/agents', postData).then( res => {
        this.unactive_agents.push(res.data);
        return this.stopSpinner('saving_new_agent').then(() => {
          this.$scope.created_agent = this.$scope.new_agent;
          return this.$scope.new_agent = {};
        });
      }
      , res => {
        this.stopSpinner('saving_new_agent', true);
        if (res.data.error_code && (res.data.error_code === 'license_exceeded')) {
          return this.DpLicense.openUpgradeLicense('upgrade_plan').then(() => {
            return this.addNewAgent();
          });
        }
      });
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
      return this.Api.sendPostJson('/dp_license/support-request', { contact}).then( () => {
        return this.stopSpinner('sending_support_request').then(() => {
          return this.$scope.support_sent = true;
        });
      });
    }

    /*
     * Dismiss ugrade notice
     */
    dismissUpgradeNotice(value) {
      $('#admin_upgrade_notice').slideUp();
      window.hide_admin_upgrade_notice = true;
      return this.Api.sendPost('/settings/values/core.admin_upgrade_notice', {
        value
      });
    }
  }
  Admin_Main_Ctrl_Home.initClass();


  return Admin_Main_Ctrl_Home.EXPORT_CTRL();
});
