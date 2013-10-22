(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['angular', 'Admin/Main/Ctrl/Base'], function(angular, Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_EmailTemplateEditor, _ref;
    Admin_Templates_Ctrl_EmailTemplateEditor = (function(_super) {
      __extends(Admin_Templates_Ctrl_EmailTemplateEditor, _super);

      function Admin_Templates_Ctrl_EmailTemplateEditor() {
        _ref = Admin_Templates_Ctrl_EmailTemplateEditor.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Templates_Ctrl_EmailTemplateEditor.CTRL_ID = 'Admin_Templates_Ctrl_EmailTemplateEditor';

      Admin_Templates_Ctrl_EmailTemplateEditor.CTRL_TYPE = 'modal';

      Admin_Templates_Ctrl_EmailTemplateEditor.CTRL_AS = 'EmailTemplateEditor';

      Admin_Templates_Ctrl_EmailTemplateEditor.DEPS = ['$modalInstance', 'templateName', 'variantOf'];

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.init = function() {
        var _this = this;
        this.$scope.dismiss = function() {
          return _this.$modalInstance.dismiss('cancel');
        };
        this.$scope.save = function() {
          return _this.$modalInstance.close();
        };
        return this.$scope.aceLoaded = function(editor) {
          var maxH, updateH;
          maxH = $(editor.container).data('max-height') || 500;
          updateH = function() {
            var newHeight;
            newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth();
            if (newHeight > maxH) {
              newHeight = maxH;
            }
            if (newHeight < 10) {
              newHeight = 10;
            }
            $(editor.container).height(newHeight);
            return editor.resize();
          };
          updateH();
          editor.getSession().on('change', updateH);
          return editor.setShowPrintMargin(false);
        };
      };

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.initialLoad = function() {
        var p,
          _this = this;
        if (this.templateName) {
          p = this.Api.sendGet("/templates/" + this.templateName).success(function(data) {
            return _this.initTemplateData(data);
          });
        } else {
          p = this.Api.sendPost("/templates/" + this.variantOf + "/create-random-variant").success(function(data) {
            return _this.initTemplateData(data);
          });
        }
        return p;
      };

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.initTemplateData = function(info) {
        this.templateName = info.name;
        this.email = info;
        return this.$scope.email = this.email;
      };

      return Admin_Templates_Ctrl_EmailTemplateEditor;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_EmailTemplateEditor.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=EmailTemplateEditor.js.map
*/