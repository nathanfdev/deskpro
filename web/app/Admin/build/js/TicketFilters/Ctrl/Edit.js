(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
    var Admin_TicketFilters_Ctrl_Edit, _ref;
    Admin_TicketFilters_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketFilters_Ctrl_Edit, _super);

      function Admin_TicketFilters_Ctrl_Edit() {
        _ref = Admin_TicketFilters_Ctrl_Edit.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_TicketFilters_Ctrl_Edit.CTRL_ID = 'Admin_TicketFilters_Ctrl_Edit';

      Admin_TicketFilters_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketFilters_Ctrl_Edit.DEPS = ['dpObTypesDefTicketFilter', '$stateParams'];

      Admin_TicketFilters_Ctrl_Edit.prototype.init = function() {
        this.filterId = parseInt(this.$stateParams.id || 0);
        this.filterData = this.DataService.get('TicketFilters');
        this.filter = null;
        this.filter_criteria = {};
        this.criteriaTypeDef = this.dpObTypesDefTicketFilter;
        return this.criteriaOptionTypes = this.criteriaTypeDef.getOptionsForTypes();
      };

      Admin_TicketFilters_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.filterData.loadEditFilterData(this.$stateParams.id).then(function(data) {
            var rowId, term, _i, _len, _ref1, _results;
            _this.filter = data.filter;
            _this.form = _this.getFormFromModel(_this.filter);
            _ref1 = _this.filter.terms.terms;
            _results = [];
            for (_i = 0, _len = _ref1.length; _i < _len; _i++) {
              term = _ref1[_i];
              rowId = Util.uid('term');
              _results.push(_this.filter_criteria[rowId] = term);
            }
            return _results;
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

      Admin_TicketFilters_Ctrl_Edit.prototype.saveForm = function() {
        var method, postData, url,
          _this = this;
        if (!this.$scope.form_props.$valid) {
          return;
        }
        if (this.filterId) {
          method = 'POST';
          url = "/ticket_filters/" + this.filterId;
        } else {
          method = 'PUT';
          url = "/ticket_filters";
        }
        postData = {
          filter: this.form
        };
        postData.filter.terms = this.filter_criteria;
        return this.sendFormSaveApiCall(method, url, postData).then(function(res) {
          _this.Growl.success(_this.getRegisteredMessage('saved_filter'));
          _this.filter.title = _this.form.title;
          if (res.data.filter_id) {
            _this.filter.id = res.data.filter_id;
          }
          _this.filterData.mergeDataModel(_this.filter);
          if (!_this.filterId) {
            return _this.$state.go('tickets.ticket_filters.gocreate');
          }
        });
      };

      return Admin_TicketFilters_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=Edit.js.map
*/