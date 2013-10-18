(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'Admin/Main/Model/DepAgentPermMatrix'], function(Admin_Ctrl_Base, Admin_Main_Model_DepAgentPermMatrix) {
    var Admin_TicketTriggers_Ctrl_Edit, _ref;
    Admin_TicketTriggers_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_Edit, _super);

      function Admin_TicketTriggers_Ctrl_Edit() {
        _ref = Admin_TicketTriggers_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketTriggers_Ctrl_Edit.CTRL_ID = 'Admin_TicketTriggers_Ctrl_Edit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketTriggers_Ctrl_Edit.DEPS = ['em', '$stateParams', 'dpObTypesDefTicketCriteria'];

      Admin_TicketTriggers_Ctrl_Edit.prototype.init = function() {
        this.triggerType = this.$stateParams.type;
        this.trigger = null;
        this.triggerId = this.$stateParams.id;
        this.options = {};
        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId = this.$stateParams.id;
        this.$scope.form = {
          by_user: true,
          by_agent: false,
          by_agent_opt: {
            web: true,
            email: true,
            api: true
          },
          by_user_opt: {
            web_portal: true,
            web_widget: true,
            web_form: true,
            email: true,
            api: true
          }
        };
        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
      };

      /*
      		# Load the trigger
      */


      Admin_TicketTriggers_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.triggerId) {
          promise = this.Api.sendGet("/ticket_triggers/" + this.triggerId).success(function(data) {
            _this.trigger = data.trigger;
            return _this.form = {
              title: _this.trigger.title
            };
          });
          return promise;
        }
        this.trigger = {};
        return null;
      };

      return Admin_TicketTriggers_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketTriggers_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/