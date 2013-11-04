(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketMacros_Ctrl_Edit, _ref;
    Admin_TicketMacros_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketMacros_Ctrl_Edit, _super);

      function Admin_TicketMacros_Ctrl_Edit() {
        _ref = Admin_TicketMacros_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketMacros_Ctrl_Edit.CTRL_ID = 'Admin_TicketMacros_Ctrl_Edit';

      Admin_TicketMacros_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketMacros_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketMacros_Ctrl_Edit.DEPS = ['dpObTypesDefTicketActions', '$stateParams'];

      Admin_TicketMacros_Ctrl_Edit.prototype.init = function() {
        this.macroData = this.DataService.get('TicketMacros');
        this.macro = null;
        this.macro_criteria = {};
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        return this.actionOptionTypes = this.actionsTypeDef.getOptionsForTypes();
      };

      Admin_TicketMacros_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.macroData.loadEditMacroData(this.$stateParams.id).then(function(data) {
            _this.macro = data.macro;
            return _this.form = _this.getFormFromModel(_this.macro);
          });
          return promise;
        } else {
          this.macro = {};
          this.form = this.getFormFromModel(this.macro);
          return null;
        }
      };

      Admin_TicketMacros_Ctrl_Edit.prototype.getFormFromModel = function(macroModel) {
        var form;
        form = {};
        form.title = macroModel.title || '';
        return form;
      };

      return Admin_TicketMacros_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/