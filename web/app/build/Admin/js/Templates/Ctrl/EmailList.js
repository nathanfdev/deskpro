(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_EmailList;
    Admin_Templates_Ctrl_EmailList = (function(_super) {
      __extends(Admin_Templates_Ctrl_EmailList, _super);

      function Admin_Templates_Ctrl_EmailList() {
        return Admin_Templates_Ctrl_EmailList.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_EmailList.CTRL_ID = 'Admin_Templates_Ctrl_EmailList';

      Admin_Templates_Ctrl_EmailList.CTRL_AS = 'ListCtrl';

      Admin_Templates_Ctrl_EmailList.prototype.init = function() {
        var parts;
        parts = this.$stateParams.groupName.split(':');
        this.typeId = parts.shift();
        return this.groupId = parts.shift() || this.typeId;
      };

      Admin_Templates_Ctrl_EmailList.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendDataGet({
          info: '/email-templates-info'
        }).then((function(_this) {
          return function(res) {
            return _this.templates = res.data.info.list[_this.typeId].groups[_this.groupId].templates;
          };
        })(this));
        return promise;
      };


      /*
      		 * Open an editor
       */

      Admin_Templates_Ctrl_EmailList.prototype.openEditor = function(tpl) {
        var modalInstance;
        modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('Templates/modal-email-editor.html'),
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
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
              tpl.is_custom = false;
              if (_this.typeId === 'custom' && _this.groupId === 'custom') {
                return _this.templates = _this.templates.filter(function(x) {
                  return x !== tpl;
                });
              }
            }
          };
        })(this));
        return modalInstance;
      };


      /*
        	 * Opens email editor in 'new' mode
       */

      Admin_Templates_Ctrl_EmailList.prototype.createNewEmailTemplate = function() {
        var modalInstance;
        modalInstance = this.$modal.open({
          templateUrl: this.getTemplatePath('Templates/modal-email-editor.html'),
          controller: 'Admin_Templates_Ctrl_EmailTemplateEditor',
          resolve: {
            templateName: function() {
              return null;
            }
          }
        }).result.then((function(_this) {
          return function(info) {
            var title, tpl;
            title = info.templateName.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html');
            tpl = {
              typeId: _this.typeId,
              groupId: _this.groupId,
              name: info.templateName,
              title: title
            };
            return _this.templates.push(tpl);
          };
        })(this));
        return modalInstance;
      };

      return Admin_Templates_Ctrl_EmailList;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_EmailList.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EmailList.js.map
