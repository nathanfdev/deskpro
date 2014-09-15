(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Usersources_Ctrl_Edit;
    Admin_Usersources_Ctrl_Edit = (function(_super) {
      __extends(Admin_Usersources_Ctrl_Edit, _super);

      function Admin_Usersources_Ctrl_Edit() {
        return Admin_Usersources_Ctrl_Edit.__super__.constructor.apply(this, arguments);
      }

      Admin_Usersources_Ctrl_Edit.CTRL_ID = 'Admin_Usersources_Ctrl_Edit';

      Admin_Usersources_Ctrl_Edit.CTRL_AS = 'EditCtrl';

      Admin_Usersources_Ctrl_Edit.DEPS = ['$stateParams'];

      Admin_Usersources_Ctrl_Edit.prototype.init = function() {
        this.usersourcesDataService = this.DataService.get('Usersources');
        return this.usersourceId = this.$stateParams.id;
      };

      Admin_Usersources_Ctrl_Edit.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet('/usersources/user/' + this.usersourceId).then((function(_this) {
          return function(result) {
            _this.usersource = result.data.usersource;
            _this.app = result.data.app;
            console.log(_this.app);
            return console.log(_this.usersource);
          };
        })(this));
        return promise;
      };

      return Admin_Usersources_Ctrl_Edit;

    })(Admin_Ctrl_Base);
    return Admin_Usersources_Ctrl_Edit.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Edit.js.map
