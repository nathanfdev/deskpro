(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_TicketFilters_Ctrl_Edit, _ref;
    Admin_TicketFilters_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketFilters_Ctrl_Edit, _super);

      function Admin_TicketFilters_Ctrl_Edit() {
        _ref = Admin_TicketFilters_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_Ctrl_Edit.CTRL_ID = 'Admin_TicketFilters_Ctrl_Edit';

      Admin_TicketFilters_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketFilters_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketFilters_Ctrl_Edit.DEPS = ['dpObTypesDefTicketFilter', '$stateParams'];

      Admin_TicketFilters_Ctrl_Edit.prototype.init = function() {
        this.filterData = this.DataService.get('TicketFilters');
        this.filter = null;
        this.filter_criteria = {};
        this.criteraTypeDef = this.dpObTypesDefTicketFilter;
        return this.criteriaOptionTypes = this.criteraTypeDef.getOptionsForTypes();
      };

      Admin_TicketFilters_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.filterData.loadEditFilterData(this.$stateParams.id).then(function(data) {
            _this.filter = data.filter;
            return _this.form = _this.getFormFromModel(_this.filter);
          });
          return promise;
        } else {
          this.filter = {};
          this.form = this.getFormFromModel(this.filter);
          return null;
        }
      };

      Admin_TicketFilters_Ctrl_Edit.prototype.getFormFromModel = function(filterModel) {
        var form;
        form = {};
        form.title = filterModel.title || '';
        return form;
      };

      return Admin_TicketFilters_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/