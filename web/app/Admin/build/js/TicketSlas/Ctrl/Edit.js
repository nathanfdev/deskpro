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

      Admin_TicketSlas_Ctrl_Edit.CTRL_TYPE = 'page';

      Admin_TicketSlas_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_TicketSlas_Ctrl_Edit.prototype.init = function() {
        this.slaData = this.DataService.get('TicketSlas');
        return this.macro = null;
      };

      Admin_TicketSlas_Ctrl_Edit.prototype.initialLoad = function() {
        var promise,
          _this = this;
        if (this.$stateParams.id) {
          promise = this.slaData.loadEditSlaData(this.$stateParams.id).then(function(data) {
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