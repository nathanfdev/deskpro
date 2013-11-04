(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketEscalations_Ctrl_Edit, _ref;
    Admin_TicketEscalations_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketEscalations_Ctrl_Edit, _super);

      function Admin_TicketEscalations_Ctrl_Edit() {
        _ref = Admin_TicketEscalations_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketEscalations_Ctrl_Edit.CTRL_ID = 'Admin_TicketEscalations_Ctrl_Edit';

      Admin_TicketEscalations_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketEscalations_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketEscalations_Ctrl_Edit.DEPS = ['dpObTypesDefTicketFilter', 'dpObTypesDefTicketActions', '$stateParams'];

      Admin_TicketEscalations_Ctrl_Edit.prototype.init = function() {
        this.escData = this.DataService.get('TicketEscalations');
        this.filter = null;
        this.escalation_criteria = {};
        this.escalation_actions = {};
        this.criteraTypeDef = this.dpObTypesDefTicketFilter;
        this.criteriaOptionTypes = this.criteraTypeDef.getOptionsForTypes();
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        return this.actionOptionTypes = this.actionsTypeDef.getOptionsForTypes();
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.escData.loadEditEscalationData(this.$stateParams.id).then(function(data) {
            _this.esc = data.esc;
            return _this.form = _this.getFormFromModel(_this.esc);
          });
          return promise;
        } else {
          this.esc = {};
          this.form = this.getFormFromModel(this.esc);
          return null;
        }
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.getFormFromModel = function(escModel) {
        var form;
        form = {};
        form.title = escModel.title || '';
        return form;
      };

      return Admin_TicketEscalations_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketEscalations_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/