(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Util', 'Admin/TicketSlas/SlaFormMapper'], function(Admin_Ctrl_Base, Util, SlaFormMapper) {
    var Admin_TicketSlas_Ctrl_Edit;
    Admin_TicketSlas_Ctrl_Edit = (function(_super) {
      __extends(Admin_TicketSlas_Ctrl_Edit, _super);

      function Admin_TicketSlas_Ctrl_Edit() {
        return Admin_TicketSlas_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_TicketSlas_Ctrl_Edit.CTRL_ID = 'Admin_TicketSlas_Ctrl_Edit';

      Admin_TicketSlas_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_TicketSlas_Ctrl_Edit.DEPS = ['dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria'];

      Admin_TicketSlas_Ctrl_Edit.prototype.init = function() {
        this.formMapper = new SlaFormMapper();
        this.slaData = this.DataService.get('TicketSlas');
        this.sla = null;
        this.form = this.getFormFromModel({});
        this.actionsTypeDef = this.dpObTypesDefTicketActions;
        this.criteraTypeDef = this.dpObTypesDefTicketCriteria;
        this.$scope.criteriaOptionTypes = [];
        this.$scope.actionOptionTypes = [];
        return this.updateCriteriaOptionTypes();
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.updateCriteriaOptionTypes = function() {
        var opt, setActionOptions, setCritOptions, types, _i, _j, _len, _len1, _results;
        types = [];
        setActionOptions = this.actionsTypeDef.getOptionsForTypes(types);
        this.$scope.actionOptionTypes.length = 0;
        for (_i = 0, _len = setActionOptions.length; _i < _len; _i++) {
          opt = setActionOptions[_i];
          this.$scope.actionOptionTypes.push(opt);
        }
        types = ['web', 'web.user', 'email', 'email.user', 'api', 'api.user', 'web.agent', 'email.agent', 'api.agent'];
        setCritOptions = this.criteraTypeDef.getOptionsForTypes(types);
        this.$scope.criteriaOptionTypes.length = 0;
        _results = [];
        for (_j = 0, _len1 = setCritOptions.length; _j < _len1; _j++) {
          opt = setCritOptions[_j];
          _results.push(this.$scope.criteriaOptionTypes.push(opt));
        }
        return _results;
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        if (this.$stateParams.id) {
          promise = this.slaData.loadEditSlaData(this.$stateParams.id).then((function(_this) {
            return function(data) {
              _this.sla = data.sla;
              _this.form = _this.getFormFromModel(_this.sla);
              return _this.origForm = Util.clone(_this.form, true);
            };
          })(this));
          return promise;
        } else {
          this.macro = {};
          this.sla = {};
          this.form = this.getFormFromModel({});
          this.origForm = Util.clone(this.form, true);
          return null;
        }
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.getFormFromModel = function(slaModel) {
        return this.formMapper.getFormFromModel(slaModel);
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.saveForm = function() {
        var is_new, postData, promise;
        postData = this.formMapper.getPostDataFromFormModel(this.form);
        this.startSpinner('saving');
        if (this.sla.id) {
          is_new = false;
          promise = this.Api.sendPostJson("/ticket_slas/" + this.sla.id, postData);
        } else {
          is_new = true;
          promise = this.Api.sendPutJson('/ticket_slas', postData);
        }
        promise.success((function(_this) {
          return function(result) {
            _this.sla.id = result.sla_id;
            if (is_new) {
              _this.sla.is_enabled = true;
            }
            _this.sla.title = postData.title;
            _this.stopSpinner('saving', true).then(function() {
              return _this.Growl.success("Saved");
            });
            _this.slaData.mergeDataModel({
              id: _this.sla.id,
              title: _this.sla.title
            });
            _this.skipDirtyState();
            if (is_new) {
              return _this.$state.go('tickets.slas.gocreate');
            }
          };
        })(this));
        promise.error((function(_this) {
          return function(info, code) {
            _this.stopSpinner('saving', true);
            return _this.applyErrorResponseToView(info);
          };
        })(this));
        return promise;
      };

      return Admin_TicketSlas_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_TicketSlas_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
