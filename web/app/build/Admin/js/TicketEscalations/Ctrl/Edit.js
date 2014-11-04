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
        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];
        this.criteriaTypeDef.setVar('object_type', 'escalation');
        this.actionsTypeDef.setVar('object_type', 'escalation');
        this.criteriaTypeDef.setVar('object_type', 'escalation');
        return this.actionsTypeDef.setVar('object_type', 'escalation');
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, set, _i, _j, _len, _len1, _results;
        set = this.criteriaTypeDef.getOptionsForTypes();
        this.$scope.criteriaOptionTypes.length = 0;
        for (_i = 0, _len = set.length; _i < _len; _i++) {
          opt = set[_i];
          this.$scope.criteriaOptionTypes.push(opt);
        }
        set = this.actionsTypeDef.getOptionsForTypes();
        this.$scope.actionOptionTypes.length = 0;
        _results = [];
        for (_j = 0, _len1 = set.length; _j < _len1; _j++) {
          opt = set[_j];
          _results.push(this.$scope.actionOptionTypes.push(opt));
        }
        return _results;
      };

      Admin_TicketEscalations_Ctrl_Edit.prototype.initialLoad = function() {
        var loadData, promise, promise2, promise3, promises;
        loadData = null;
        promise = this.escData.loadEditEscalationData(this.$stateParams.id || null).then((function(_this) {
          return function(data) {
            return loadData = data;
          };
        })(this));
        promise2 = this.criteriaTypeDef.loadDataOptions();
        promise3 = this.actionsTypeDef.loadDataOptions();
        promises = [promise, promise2, promise3];
        return this.$q.all(promises).then((function(_this) {
          return function() {
            return _this.$timeout(function() {
              _this.updateCriteriaOptionTypes();
              return _this.$timeout(function() {
                _this.esc = loadData.escalation;
                return _this.form = loadData.form;
              });
            });
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
