(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_TemplateList;
    Admin_Templates_Ctrl_TemplateList = (function(_super) {
      __extends(Admin_Templates_Ctrl_TemplateList, _super);

      function Admin_Templates_Ctrl_TemplateList() {
        return Admin_Templates_Ctrl_TemplateList.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_TemplateList.CTRL_ID = 'Admin_Templates_Ctrl_TemplateList';

      Admin_Templates_Ctrl_TemplateList.CTRL_AS = 'ListCtrl';

      Admin_Templates_Ctrl_TemplateList.DEPS = [];

      Admin_Templates_Ctrl_TemplateList.prototype.init = function() {
        var parts;
        parts = this.$stateParams.groupName.split(':');
        this.bundle = parts.shift();
        return this.tplDir = parts.shift();
      };

      Admin_Templates_Ctrl_TemplateList.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          info: '/templates-info'
        }).then((function(_this) {
          return function(res) {
            return _this.templates = res.data.info.list[_this.bundle][_this.tplDir].templates;
          };
        })(this));
        return promise;
      };


      /*
      		 * Open an editor
       */

      Admin_Templates_Ctrl_TemplateList.prototype.openEditor = function(tpl) {
        var modalInstance;
        modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('Templates/modal-template-editor.html'),
          controller: 'Admin_Templates_Ctrl_TemplateEditor',
          resolve: {
            templateName: function() {
              return tpl.name;
            }
          }
        }).result.then((function(_this) {
          return function(info) {
            if (info.mode === 'custom') {
              return tpl.is_custom = true;
            } else if (info.mode === 'revert') {
              return tpl.is_custom = false;
            }
          };
        })(this));
        return modalInstance;
      };

      return Admin_Templates_Ctrl_TemplateList;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_TemplateList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TemplateList.js.map
