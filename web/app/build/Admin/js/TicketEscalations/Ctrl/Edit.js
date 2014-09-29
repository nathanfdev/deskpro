(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketEscalations_Ctrl_Edit;
    Admin_TicketEscalations_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketEscalations_Ctrl_Edit, _super);

      function Admin_TicketEscalations_Ctrl_Edit() {
        return Admin_TicketEscalations_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketEscalations_Ctrl_Edit.CTRL_ID = 'Admin_TicketEscalations_Ctrl_Edit';

      Admin_TicketEscalations_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketEscalations_Ctrl_Edit.DEPS = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams'];

      Admin_TicketEscalations_Ctrl_Edit.prototype.init = function() {
        this.escData = this.DataService.get('TicketEscalations');
        this.esc = null;
        this.criteriaTypeDef = this.dpObTypesDefTicketFilter;
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.criteriaOptionTypes = [];
        this.actionOptionTypes = [];
        this.criteriaTypeDef.setVar('object_type', 'escalation');
        this.actionsTypeDef.setVar('object_type', 'escalation');
        this.criteriaTypeDef.setVar('object_type', 'escalation');
        return this.actionsTypeDef.setVar('object_type', 'escalation');
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.initialLoad = function() {
        this.criteriaTypeDef.loadDataOptions().then((function(_this) {
          return function() {
            return _this.criteriaOptionTypes = _this.criteriaTypeDef.getOptionsForTypes();
          };
        })(this));
        this.actionsTypeDef.loadDataOptions().then((function(_this) {
          return function() {
            return _this.actionOptionTypes = _this.actionsTypeDef.getOptionsForTypes();
          };
        })(this));
        return this.escData.loadEditEscalationData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            _this.esc = data.escalation;
            return _this.form = data.form;
          };
        })(this));
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, promise;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        is_new = !this.esc.id;
        promise = this.escData.saveFormModel(this.esc, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('tickets.ticket_escalations.gocreate');
            }
          };
        })(this));
      };

      return Admin_TicketEscalations_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
