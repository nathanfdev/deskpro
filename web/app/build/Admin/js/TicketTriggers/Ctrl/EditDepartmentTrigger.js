(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Arrays', 'Admin/Main/Ctrl/Base', 'Admin/TicketTriggers/TriggerEditFormMapper', 'Admin/TicketTriggers/Ctrl/EditBase'], function(Arrays, Admin_Ctrl_Base, TriggerEditFormMapper, Admin_TicketTriggers_Ctrl_EditBase) {
    var Admin_TicketTriggers_Ctrl_EditDepartmentTrigger;
    Admin_TicketTriggers_Ctrl_EditDepartmentTrigger = (function(_super) {
      __extends(Admin_TicketTriggers_Ctrl_EditDepartmentTrigger, _super);

      function Admin_TicketTriggers_Ctrl_EditDepartmentTrigger() {
        return Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.CTRL_ID = 'Admin_TicketTriggers_Ctrl_EditDepartmentTrigger';

      Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.CTRL_AS = 'TicketTriggersEdit';

      Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.DEPS = ['dpObTypesDefTicketCriteria', 'dpObTypesDefTicketActions'];

      Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.prototype.customInit = function() {
        this.triggerId = 0;
        if (this.$stateParams.id.indexOf('department-changed-') !== -1) {
          this.eventType = 'update';
          this.depId = this.$stateParams.id.replace(/^department\-changed\-(\d+)$/, '$1');
        } else {
          this.eventType = 'newticket';
          this.depId = this.$stateParams.id.replace(/^department\-(\d+)$/, '$1');
        }
        this.$scope.triggerType = this.$stateParams.type;
        this.$scope.triggerId = 0;
        return this.$scope.depId = this.depId;
      };


      /*
      		 * Load the trigger
       */

      Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.prototype.initialLoad = function() {
        var get, promise, promise2, promise3, promises;
        get = {
          customActions: '/ticket_triggers/get-custom-actions',
          depInfo: "/ticket_deps/" + this.depId
        };
        if (this.eventType === 'newticket') {
          get.trigger = "/ticket_triggers/departments/" + this.depId;
        } else {
          get.trigger = "/ticket_triggers/departments_changed/" + this.depId;
        }
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
            _this.dep = result.data.depInfo.department;
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

      return Admin_TicketTriggers_Ctrl_EditDepartmentTrigger;

    })(Admin_TicketTriggers_Ctrl_EditBase);
    return Admin_TicketTriggers_Ctrl_EditDepartmentTrigger.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditDepartmentTrigger.js.map
