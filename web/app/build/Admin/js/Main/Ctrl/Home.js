(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_Main_Ctrl_Home;
    Admin_Main_Ctrl_Home = (function(_super) {
      __extends(Admin_Main_Ctrl_Home, _super);

      function Admin_Main_Ctrl_Home() {
        return Admin_Main_Ctrl_Home.__super__.constructor.apply(this, arguments);
      }

      Admin_Main_Ctrl_Home.CTRL_ID = 'Admin_Main_Ctrl_Home';

      Admin_Main_Ctrl_Home.CTRL_AS = 'Home';

      Admin_Main_Ctrl_Home.DEPS = ['$http'];

      Admin_Main_Ctrl_Home.prototype.init = function() {
        this.online_agents = [];
        this.offline_agents = [];
        this.$scope.new_agent = {};
        this.$scope.hide_admin_upgrade_notice = window.hide_admin_upgrade_notice || false;
        this.online_agents = [];
        this.offline_agents = [];
        this.unactive_agents = [];
        this.agentsMap = {};
        this.service = {
          agents: this.DataService.get('Agents')
        };
        this.loadMethodTests();
      };

      Admin_Main_Ctrl_Home.prototype.initialLoad = function() {
        var promise;
        this.refreshAgents();
        promise = this.Api.sendDataGet({
          lastLogin: '/me/last-login',
          versionInfo: '/dp_license/version-info'
        }).then((function(_this) {
          return function(result) {
            _this.version_info = result.data.versionInfo;
            _this.last_login = result.data.lastLogin.last_login;
            if (_this.last_login) {
              return _this.last_login.date_created_d = new Date(_this.last_login.date_created_ts * 1000);
            }
          };
        })(this));
        this.Api.sendDataGet({
          cronStatus: '/server/cron-status',
          errorStatus: '/server/error-status',
          apcStatus: '/server/apc-status',
          quickStats: '/tickets/quick-stats'
        }).then((function(_this) {
          return function(result) {
            var problem_triggers;
            _this.cron_status = result.data.cronStatus;
            _this.error_status = result.data.errorStatus;
            _this.apc_status = result.data.apcStatus;
            _this.quick_stats = result.data.quickStats;
            problem_triggers = [_this.cron_status.is_problem, _this.error_status.error_count > 0, _this.error_status.gateway_error_count > 0, _this.error_status.sendmail_error_count > 0, _this.apc_status.is_problem];
            return _this.is_server_problem = problem_triggers.filter(function(x) {
              return !!x;
            }).length > 0;
          };
        })(this));
        this.Api.sendDataGet({
          latestVersion: '/dp_license/latest-version-info',
          news: '/dp_license/news'
        }).then((function(_this) {
          return function(result) {
            var _ref, _ref1;
            if (((_ref = result.data.latestVersion) != null ? _ref.version_info : void 0) == null) {
              _this.latest_version_status = "error";
            } else {
              _this.latest_version_status = "okay";
              _this.latest_version = result.data.latestVersion.version_info;
              _this.latest_version.count_behind = parseInt(result.data.latestVersion.version_info.count_behind) || 0;
            }
            if (((_ref1 = result.data.news) != null ? _ref1.news : void 0) == null) {
              return _this.news_status = "error";
            } else {
              _this.news_status = "okay";
              return _this.news = result.data.news.news;
            }
          };
        })(this));
        return promise;
      };

      Admin_Main_Ctrl_Home.prototype.refreshAgents = function() {
        return this.service.agents.all(true).then((function(_this) {
          return function(agents) {
            var agent, i, index, list, state, _agent, _i, _j, _len, _len1;
            for (_i = 0, _len = agents.length; _i < _len; _i++) {
              agent = agents[_i];
              if (_this.agentsMap[agent.id] == null) {
                if (agent.is_online_now || agent.id === DP_PERSON_ID) {
                  _this.online_agents.push(agent);
                  _this.agentsMap[agent.id] = 'online_agents';
                } else if (agent.date_last_login == null) {
                  _this.unactive_agents.push(agent);
                  _this.agentsMap[agent.id] = 'unactive_agents';
                } else {
                  _this.offline_agents.push(agent);
                  _this.agentsMap[agent.id] = 'offline_agents';
                }
              } else {
                state = 'offline_agents';
                if (agent.is_online_now || agent.id === DP_PERSON_ID) {
                  state = 'online_agents';
                } else if (agent.date_last_login == null) {
                  state = 'unactive_agents';
                }
                if (_this.agentsMap[agent.id] !== state) {
                  list = _this[_this.agentsMap[agent.id]];
                  index = -1;
                  for (i = _j = 0, _len1 = list.length; _j < _len1; i = ++_j) {
                    _agent = list[i];
                    if (_agent.id === agent.id) {
                      index = i;
                      break;
                    }
                  }
                  if (-1 !== index) {
                    list.splice(index, 1);
                  }
                  _this[state].push(agent);
                  _this.agentsMap[agent.id] = state;
                }
              }
            }
            return _this.$timeout((function() {
              return _this.refreshAgents();
            }), 60 * 1000);
          };
        })(this));
      };

      Admin_Main_Ctrl_Home.prototype.loadMethodTests = function() {
        var $http, checkRes, checkUrl, http_method, makeCheck, masterP, promises;
        promises = [];
        http_method = {};
        $http = this.$http;
        checkUrl = DP_BASE_URL + 'index.php?_sys=check_http_method&x=' + ((new Date()).getTime());
        makeCheck = function(type) {
          var p, typeU;
          typeU = type.toUpperCase();
          p = $http({
            method: typeU,
            url: checkUrl,
            responseType: "text",
            cache: false
          });
          p.success(function(res) {
            if (!res) {
              res = '';
            }
            if (res.indexOf("HTTP_METHOD_" + typeU) !== -1) {
              return http_method[type] = true;
            } else {
              return http_method[type] = false;
            }
          });
          p.error(function() {
            return http_method[type] = false;
          });
          return p;
        };
        promises.push(makeCheck('get'));
        promises.push(makeCheck('post'));
        promises.push(makeCheck('put'));
        promises.push(makeCheck('delete'));
        masterP = this.$q.all(promises);
        checkRes = (function(_this) {
          return function() {
            var any, k, v;
            _this.$scope.http_method_checks = http_method;
            any = false;
            for (k in http_method) {
              if (!__hasProp.call(http_method, k)) continue;
              v = http_method[k];
              if (!v) {
                any = true;
                break;
              }
            }
            return _this.$scope.http_method_errors = any;
          };
        })(this);
        return masterP.then(checkRes, checkRes);
      };


      /*
      		 * Saves new agent form
       */

      Admin_Main_Ctrl_Home.prototype.addNewAgent = function() {
        var postData;
        this.$scope.created_agent = null;
        postData = {
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
        return this.Api.sendPutJson('/agents', postData).then((function(_this) {
          return function(data) {
            _this.unactive_agents.push(data.data);
            return _this.stopSpinner('saving_new_agent').then(function() {
              _this.$scope.created_agent = _this.$scope.new_agent;
              return _this.$scope.new_agent = {};
            });
          };
        })(this));
      };


      /*
      		 * Sends support request
       */

      Admin_Main_Ctrl_Home.prototype.sendSupportRequest = function() {
        var contact, submit_ticket;
        submit_ticket = this.$scope.submit_ticket;
        contact = {
          subject: Strings.trim(submit_ticket.subject || ''),
          message: Strings.trim(submit_ticket.message || ''),
          email: Strings.trim(submit_ticket.email || '')
        };
        if (!contact.message) {
          this.$scope.submit_ticket_message_error = true;
          return;
        }
        if (this.$scope.submit_ticket_defaultemail || contact.email.indexOf('@') === -1) {
          delete contact.email;
          this.$scope.submit_ticket_defaultemail = true;
        }
        this.startSpinner('sending_support_request');
        return this.Api.sendPostJson('/dp_license/support-request', {
          contact: contact
        }).then((function(_this) {
          return function() {
            return _this.stopSpinner('sending_support_request').then(function() {
              return _this.$scope.support_sent = true;
            });
          };
        })(this));
      };


      /*
      		 * Dismiss ugrade notice
       */

      Admin_Main_Ctrl_Home.prototype.dismissUpgradeNotice = function(value) {
        $('#admin_upgrade_notice').slideUp();
        window.hide_admin_upgrade_notice = true;
        return this.Api.sendPost('/settings/values/core.admin_upgrade_notice', {
          value: value
        });
      };

      return Admin_Main_Ctrl_Home;

    })(Admin_Ctrl_Base);
    return Admin_Main_Ctrl_Home.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Home.js.map
