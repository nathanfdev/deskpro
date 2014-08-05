(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_Cloud_Main_Ctrl_Home;
    Admin_Cloud_Main_Ctrl_Home = (function(_super) {
      __extends(Admin_Cloud_Main_Ctrl_Home, _super);

      function Admin_Cloud_Main_Ctrl_Home() {
        return Admin_Cloud_Main_Ctrl_Home.__super__.constructor.apply(this, arguments);
      }

      Admin_Cloud_Main_Ctrl_Home.CTRL_ID = 'Admin_Cloud_Main_Ctrl_Home';

      Admin_Cloud_Main_Ctrl_Home.CTRL_AS = 'Home';

      Admin_Cloud_Main_Ctrl_Home.DEPS = ['$http'];

      Admin_Cloud_Main_Ctrl_Home.prototype.init = function() {
        this.online_agents = [];
        this.offline_agents = [];
        this.$scope.new_agent = {};
      };

      Admin_Cloud_Main_Ctrl_Home.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          agents: '/agents',
          lastLogin: '/me/last-login',
          quickStats: '/tickets/quick-stats',
          lic_info: '/dp_license'
        }).then((function(_this) {
          return function(result) {
            var agent, data, _i, _len, _ref, _results;
            data = result.data;
            _this.online_agents = [];
            _this.offline_agents = [];
            _this.quick_stats = result.data.quickStats;
            _this.last_login = result.data.lastLogin.last_login;
            if (_this.last_login) {
              _this.last_login.date_created_d = new Date(_this.last_login.date_created_ts * 1000);
            }
            _this.license = result.data.lic_info.license;
            _ref = data.agents.agents;
            _results = [];
            for (_i = 0, _len = _ref.length; _i < _len; _i++) {
              agent = _ref[_i];
              if (agent.is_online_now || agent.id === DP_PERSON_ID) {
                _results.push(_this.online_agents.push(agent));
              } else {
                _results.push(_this.offline_agents.push(agent));
              }
            }
            return _results;
          };
        })(this));
        return promise;
      };


      /*
      		 * Saves new agent form
       */

      Admin_Cloud_Main_Ctrl_Home.prototype.addNewAgent = function() {
        var postData;
        this.$scope.created_agent = null;
        postData = {
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
          return function() {
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

      Admin_Cloud_Main_Ctrl_Home.prototype.sendSupportRequest = function() {
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

      return Admin_Cloud_Main_Ctrl_Home;

    })(Admin_Ctrl_Base);
    return Admin_Cloud_Main_Ctrl_Home.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Home.js.map
