(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util'], function(Admin_Ctrl_Base, Util) {
    var Admin_TicketFilters_Ctrl_Edit;
    Admin_TicketFilters_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketFilters_Ctrl_Edit, _super);

      function Admin_TicketFilters_Ctrl_Edit() {
        return Admin_TicketFilters_Ctrl_Edit.__super__.constructor.apply(this, arguments);
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
        return this.filterData.loadEditFilterData(this.filterId).then((function(_this) {
          return function(data) {
            var rowId, term, _i, _len, _ref, _results;
            _this.agents = data.agents;
            _this.teams = data.teams;
            if (!_this.teams[0]) {
              _this.teams = null;
            }
            if (data.filter) {
              _this.filter = data.filter;
              _this.form = _this.getFormFromModel(_this.filter);
              _this.filter_criteria = {};
              _ref = _this.filter.terms.terms;
              _results = [];
              for (_i = 0, _len = _ref.length; _i < _len; _i++) {
                term = _ref[_i];
                rowId = Util.uid('term');
                _results.push(_this.filter_criteria[rowId] = term);
              }
              return _results;
            } else {
              _this.filter = {
                is_global: true
              };
              return _this.form = _this.getFormFromModel(_this.filter);
            }
          };
        })(this));
      };

      Admin_TicketFilters_Ctrl_Edit.prototype.getFormFromModel = function(filterModel) {
        var form;
        form = {};
        form.title = filterModel.title || '';
        if (filterModel.is_gloabl) {
          form.perm_type = 'global';
        } else if (filterModel.agent_team && this.teams[0]) {
          form.perm_type = 'team';
        } else {
          form.perm_type = 'agent';
        }
        if (this.filter.person) {
          form.agent_id = this.filter.person.id + "";
        } else {
          form.agent_id = this.agents[0].id + "";
        }
        form.team_id = null;
        if (this.teams) {
          if (this.filter.agent_team) {
            form.team_id = this.filter.agent_team.id + "";
          } else {
            form.team_id = this.teams[0].id + "";
          }
        }
        return form;
      };

      Admin_TicketFilters_Ctrl_Edit.prototype.saveForm = function() {
        var method, postData, url;
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
          filter: {
            title: this.form.title,
            is_global: this.form.perm_type === 'global',
            person_id: this.form.perm_type === 'agent' ? parseInt(this.form.agent_id) || null : null,
            agent_team_id: this.form.perm_type === 'team' ? parseInt(this.form.team_id) || null : null
          }
        };
        postData.filter.terms = this.filter_criteria;
        return this.sendFormSaveApiCall(method, url, postData).then((function(_this) {
          return function(res) {
            _this.Growl.success(_this.getRegisteredMessage('saved_filter'));
            _this.filter.title = _this.form.title;
            if (res.data.filter_id) {
              _this.filter.id = res.data.filter_id;
            }
            _this.filter.is_global = _this.form.perm_type === 'global';
            _this.filter.person = null;
            _this.filter.agent_team = null;
            if (_this.form.perm_type === 'agent') {
              _this.filter.person = _this.agents.filter(function(x) {
                return x.id === parseInt(_this.form.agent_id);
              })[0];
            }
            if (_this.form.perm_type === 'team') {
              _this.filter.agent_team = _this.teams.filter(function(x) {
                return x.id === parseInt(_this.form.team_id);
              })[0];
            }
            _this.filterData.mergeDataModel(_this.filter);
            if (!_this.filterId) {
              return _this.$state.go('tickets.ticket_filters.gocreate');
            }
          };
        })(this));
      };

      return Admin_TicketFilters_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketFilters_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
