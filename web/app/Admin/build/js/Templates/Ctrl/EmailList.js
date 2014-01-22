(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_EmailList, _ref;
    Admin_Templates_Ctrl_EmailList = (function(_super) {
      __extends(Admin_Templates_Ctrl_EmailList, _super);

      function Admin_Templates_Ctrl_EmailList() {
        _ref = Admin_Templates_Ctrl_EmailList.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Templates_Ctrl_EmailList.CTRL_ID = 'Admin_Templates_Ctrl_EmailList';

      Admin_Templates_Ctrl_EmailList.CTRL_AS = 'ListCtrl';

      Admin_Templates_Ctrl_EmailList.prototype.init = function() {
        var parts;
        parts = this.$stateParams.groupName.split(':');
        this.typeId = parts.shift();
        return this.groupId = parts.shift();
      };

      Admin_Templates_Ctrl_EmailList.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendDataGet({
          info: '/email-templates-info'
        }).then(function(res) {
          return _this.templates = res.data.info.list[_this.typeId].groups[_this.groupId].templates;
        });
        return promise;
      };

      /*
      		# Open an editor
      */


      Admin_Templates_Ctrl_EmailList.prototype.openEditor = function(tpl) {
        var modalInstance,
          _this = this;
        modalInstance = this.$modal.open({
          templateUrl: 'Templates/modal-email-editor.html',
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve: {
            templateName: function() {
              return tpl.name;
            }
          }
        }).result.then(function(info) {
          if (info.mode === 'custom') {
            return tpl.is_custom = true;
          } else if (info.mode === 'revert') {
            return tpl.is_custom = false;
          }
        });
        return modalInstance;
      };

      return Admin_Templates_Ctrl_EmailList;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_EmailList.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EmailList.js.map
*/