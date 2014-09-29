(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketMacros_Ctrl_Edit;
    Admin_TicketMacros_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketMacros_Ctrl_Edit, _super);

      function Admin_TicketMacros_Ctrl_Edit() {
        return Admin_TicketMacros_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketMacros_Ctrl_Edit.CTRL_ID = 'Admin_TicketMacros_Ctrl_Edit';

      Admin_TicketMacros_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketMacros_Ctrl_Edit.DEPS = ['dpObTypesDefTicketActions', '$stateParams'];

      Admin_TicketMacros_Ctrl_Edit.prototype.init = function() {
        this.macroData = this.DataService.get('TicketMacros');
        this.$scope.me_id = window.DP_PERSON_ID;
        this.macroId = parseInt(this.$stateParams.id);
        this.macro = null;
        this.agents = null;
        this.form = null;
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.actionOptionTypes = this.actionsTypeDef.getOptionsForTypes();
        return this.$scope.$watch('EditCtrl.form.person_id', (function(_this) {
          return function(id) {
            var a, name;
            id = parseInt(id);
            name = 'unknown agent';
            if (id && !isNaN(id)) {
              a = _this.agents.filter(function(a) {
                return a.id === id;
              });
              if (a[0]) {
                name = a[0].display_name;
              }
            }
            return _this.$scope.for_agent_name = name;
          };
        })(this));
      };

      Admin_TicketMacros_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.macroData.loadEditMacroData(this.macroId || null).then((function(_this) {
          return function(data) {
            _this.macro = data.macro;
            _this.agents = data.agents;
            _this.form = data.form;
            return console.log(_this.form);
          };
        })(this));
        return promise;
      };

      Admin_TicketMacros_Ctrl_Edit.prototype.saveForm = function() {
        var promise;
        this.form.agents = this.agents;
        promise = this.macroData.saveFormModel(this.macro, this.form);
        this.startSpinner('saving');
        return promise.then((function(_this) {
          return function() {
            _this.stopSpinner('saving');
            _this.skipDirtyState();
            if (!_this.macroId) {
              return _this.$state.go('tickets.macros.gocreate');
            }
          };
        })(this));
      };

      return Admin_TicketMacros_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketMacros_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
