(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Templates_Ctrl_TemplateEditor;
    Admin_Templates_Ctrl_TemplateEditor = (function(_super) {
      __extends(Admin_Templates_Ctrl_TemplateEditor, _super);

      function Admin_Templates_Ctrl_TemplateEditor() {
        return Admin_Templates_Ctrl_TemplateEditor.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_TemplateEditor.CTRL_ID = 'Admin_Templates_Ctrl_TemplateEditor';

      Admin_Templates_Ctrl_TemplateEditor.CTRL_AS = 'Ctrl';

      Admin_Templates_Ctrl_TemplateEditor.DEPS = ['$modalInstance', 'templateName'];

      Admin_Templates_Ctrl_TemplateEditor.prototype.init = function() {
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss('cancel');
          };
        })(this);
        this.$scope.save = (function(_this) {
          return function() {
            var postData;
            _this.$scope.saving_template = true;
            postData = {
              template: {
                code: _this.editor.getValue()
              }
            };
            return _this.Api.sendPostJson("/templates/" + _this.templateName, postData).then(function() {
              _this.$scope.saving_template = false;
              return _this.$modalInstance.close({
                templateName: _this.templateName,
                mode: 'custom'
              });
            }, function(result) {
              _this.$scope.saving_template = false;
              _this.$scope.is_error = true;
              _this.$scope.syntax_error = result.data.error_syntax || false;
              _this.$scope.syntax_line = result.data.error_line || 0;
              return _this.$scope.error_message = result.data.error_message || 'Unknown';
            });
          };
        })(this);
        this.$scope.revert = (function(_this) {
          return function() {
            return _this.showConfirm('Are you sure you want to revert this template? Your changes will be completely lost and the template will be returned to the default.').result.then(function() {
              _this.$scope.saving_template = true;
              return _this.Api.sendDelete("/templates/" + _this.templateName).then(function() {
                _this.$scope.saving_template = false;
                return _this.$modalInstance.close({
                  templateName: _this.templateName,
                  mode: 'revert'
                });
              });
            });
          };
        })(this);
        return this.$scope.aceLoaded = (function(_this) {
          return function(editor) {
            var maxH, updateH;
            _this.editor = editor;
            maxH = $(editor.container).data('max-height') || 500;
            updateH = function() {
              var newHeight;
              newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth();
              if (newHeight > maxH) {
                newHeight = maxH;
              }
              if (newHeight < 85) {
                newHeight = 85;
              }
              $(editor.container).height(newHeight);
              return editor.resize();
            };
            updateH();
            editor.getSession().on('change', updateH);
            return editor.setShowPrintMargin(false);
          };
        })(this);
      };

      Admin_Templates_Ctrl_TemplateEditor.prototype.initialLoad = function() {
        var promise;
        promise = this.Api.sendGet("/templates/" + this.templateName).success((function(_this) {
          return function(data) {
            _this.tpl = data;
            _this.$scope.tpl = _this.tpl;
            return _this.$scope.template_code = data.template_code.code;
          };
        })(this));
        return promise;
      };

      return Admin_Templates_Ctrl_TemplateEditor;

    })(Admin_Ctrl_Base);
    return Admin_Templates_Ctrl_TemplateEditor.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=TemplateEditor.js.map
