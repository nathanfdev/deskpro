(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Arrays', 'Admin/Main/Ctrl/Base', 'Admin/TicketTriggers/TriggerEditFormMapper', 'Admin/TicketTriggers/Ctrl/EditBase'], function(Arrays, Admin_Ctrl_Base, TriggerEditFormMapper, Admin_TicketTriggers_Ctrl_EditBase) {
    var Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger;
    Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger, _super);

      function Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger() {
        return Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.CTRL_ID = 'Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger';

      Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.DEPS = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];

      Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.prototype.customInit = function() {
        this.triggerId = 0;
        this.accountId = this.$stateParams.id.replace(/^emailaccount\-(\d+)$/, '$1');
        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId = 0;
        return this.$scope.acountId = this.accountId;
      };


      /*
      		 * Load the trigger
       */

      Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.prototype.initialLoad = function() {
        var get, promise, promise2, promise3, promises;
        get = {
          customActions: '/ticket_triggers/get-custom-actions',
          accInfo: "/email_accounts/" + this.accountId,
          trigger: "/ticket_triggers/email_accounts/" + this.accountId
        };
        promise = this.Api.sendDataGet(get).then((function(_this) {
          return function(result) {
            var _ref, _ref1;
            _this.customActions = result.data.customActions.action_defs;
            if (((_ref = result.data) != null ? (_ref1 = _ref.trigger) != null ? _ref1.trigger : void 0 : void 0) != null) {
              _this.trigger = result.data.trigger.trigger;
              _this.triggerId = _this.trigger.id;
            } else {
              _this.trigger = {};
              _this.triggerId = 0;
            }
            _this.account = result.data.accInfo.email_account;
            return _this.$scope.form = _this.editFormMapper.getFormFromModel(_this.trigger);
          };
        })(this));
        promise2 = this.criteraTypeDef.loadDataOptions();
        promise3 = this.actionsTypeDef.loadDataOptions();
        promises = [promise, promise2, promise3];
        return this.$q.all(promises).then((function(_this) {
          return function() {
            _this.updateCriteriaOptionTypes();
            return _this.$scope.$watch('form.typeForm', function() {
              return _this.updateCriteriaOptionTypes();
            }, true);
          };
        })(this));
      };

      return Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger;

    })(Admin_TicketTriggers_Ctrl_EditBase);
    return Admin_TicketTriggers_Ctrl_EditEmailAccountTrigger.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditEmailAccountTrigger.js.map
