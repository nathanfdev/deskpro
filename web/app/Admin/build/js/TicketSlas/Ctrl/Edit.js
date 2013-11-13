(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketSlas_Ctrl_Edit, _ref;
    Admin_TicketSlas_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketSlas_Ctrl_Edit, _super);

      function Admin_TicketSlas_Ctrl_Edit() {
        _ref = Admin_TicketSlas_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketSlas_Ctrl_Edit.CTRL_ID = 'Admin_TicketSlas_Ctrl_Edit';

      Admin_TicketSlas_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketSlas_Ctrl_Edit.prototype.init = function() {
        this.slaData = this.DataService.get('TicketSlas');
        this.sla = null;
        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];
        return this.updateCriteriaOptionTypes();
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, setCritOptions, types, _i, _j, _len, _len1, _results;
        types = [];
        setCritOptions = this.criteraTypeDef.getOptionsForTypes(types);
        this.$scope.criteriaOptionTypes.length = 0;
        for (_i = 0, _len = setCritOptions.length; _i < _len; _i++) {
          opt = setCritOptions[_i];
          this.$scope.criteriaOptionTypes.push(opt);
        }
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types);
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_j = 0, _len1 = setActionOptions.length; _j < _len1; _j++) {
          opt = setActionOptions[_j];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.slaData.loadEditSlaData(this.$stateParams.id).then(function(data) {
            _this.sla = data.sla;
            return _this.form = _this.getFormFromModel(_this.sla);
          });
          return promise;
        } else {
          this.macro = {};
          this.form = this.getFormFromModel(this.sla);
          return null;
        }
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.getFormFromModel = function(slaModel) {
        var form;
        form = {};
        form.title = slaModel.title || '';
        return form;
      };

      return Admin_TicketSlas_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/