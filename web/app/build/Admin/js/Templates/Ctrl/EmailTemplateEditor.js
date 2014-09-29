(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Strings'], function(Admin_Ctrl_Base, Strings) {
    var Admin_Templates_Ctrl_EmailTemplateEditor;
    Admin_Templates_Ctrl_EmailTemplateEditor = (function(_super) {
      __extends(Admin_Templates_Ctrl_EmailTemplateEditor, _super);

      function Admin_Templates_Ctrl_EmailTemplateEditor() {
        return Admin_Templates_Ctrl_EmailTemplateEditor.__super__.constructor.apply(this, arguments);
      }

      Admin_Templates_Ctrl_EmailTemplateEditor.CTRL_ID = 'Admin_Templates_Ctrl_EmailTemplateEditor';

      Admin_Templates_Ctrl_EmailTemplateEditor.CTRL_AS = 'EmailTemplateEditor';

      Admin_Templates_Ctrl_EmailTemplateEditor.DEPS = ['$modalInstance', 'templateName', 'dpTemplateManager', 'dpObTypesDefTicketActions', 'dpObTypesDefTicketCriteria'];

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.init = function() {
        this.$scope.is_new_email = this.templateName === null;
        if (this.$scope.is_new_email) {
          this.$scope.$watch('email.email_name', (function(_this) {
            return function() {
              _this.$scope.email.email_name = _this.$scope.email.email_name || '';
              _this.$scope.email.email_name = _this.$scope.email.email_name.toLowerCase();
              _this.$scope.email.email_name = _this.$scope.email.email_name.replace(/\s/g, '-');
              _this.$scope.email.email_name = _this.$scope.email.email_name.replace(/[^a-z0-9\-_\.]/g, '');
              return _this.validateName();
            };
          })(this));
        }
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss('cancel');
          };
        })(this);
        this.$scope.save = (function(_this) {
          return function() {
            var postData, url;
            _this.$scope.is_error = false;
            _this.$scope.saving_template = true;
            postData = {
              template: {
                subject: _this.editorSubject.getValue(),
                body: _this.editorMessage.getValue()
              }
            };
            if (!_this.$scope.email.email_name || Strings.trim(_this.$scope.email.email_name) === "") {
              _this.$scope.saving_template = false;
              _this.$scope.is_error = true;
              _this.$scope.syntax_error = false;
              _this.$scope.error_message = 'You must specify a template name';
              return;
            }
            if (_this.$scope.is_new_email) {
              url = "/templates/" + 'DeskPRO:emails_custom:' + _this.$scope.email.email_name + '.html.twig';
              postData.create_new = true;
            } else {
              url = "/templates/" + _this.templateName;
            }
            return _this.Api.sendPostJson(url, postData).then(function(res) {
              var tpl;
              _this.$scope.saving_template = false;
              if (_this.$scope.is_new_email) {
                tpl = {
                  name: res.data.name,
                  title: res.data.name.replace(/^.*?:.*?:(.*?)\.html\.twig$/, '$1.html')
                };
                if (_this.dpObTypesDefTicketActions.options_data) {
                  _this.dpObTypesDefTicketActions.options_data.custom_email_tpls.push(tpl);
                }
                if (_this.dpObTypesDefTicketCriteria.options_data) {
                  _this.dpObTypesDefTicketCriteria.options_data.custom_email_tpls.push(tpl);
                }
              }
              return _this.$modalInstance.close({
                templateName: res.data.name,
                isNewEmail: _this.$scope.is_new_email,
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
        this.$scope.aceLoadedSubject = (function(_this) {
          return function(editor) {
            var maxH, updateH;
            _this.editorSubject = editor;
            maxH = $(editor.container).data('max-height') || 150;
            updateH = function() {
              var newHeight;
              newHeight = editor.getSession().getScreenLength() * editor.renderer.lineHeight + editor.renderer.scrollBar.getWidth();
              if (newHeight > maxH) {
                newHeight = maxH;
              }
              if (newHeight < 18) {
                newHeight = 18;
              }
              $(editor.container).height(newHeight);
              return editor.resize();
            };
            updateH();
            editor.getSession().on('change', updateH);
            return editor.setShowPrintMargin(false);
          };
        })(this);
        return this.$scope.aceLoadedMessage = (function(_this) {
          return function(editor) {
            var maxH, updateH;
            _this.editorMessage = editor;
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

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.validateName = function() {
        this.$scope.email_name_error = null;
        if (!this.customNames) {
          return;
        }
        if (this.customNames.indexOf(this.$scope.email.email_name + '.html') !== -1) {
          return this.$scope.email_name_error = 'exists';
        }
      };

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.initialLoad = function() {
        var p;
        if (!this.$scope.is_new_email) {
          p = this.Api.sendGet("/templates/" + this.templateName).success((function(_this) {
            return function(data) {
              return _this.initTemplateData(data);
            };
          })(this));
          return p;
        } else {
          this.initTemplateData({
            name: null,
            email: {
              email_name: '',
              template_code: {
                subject: '',
                body: ''
              }
            }
          });
          p = this.Api.sendGet('/email-templates-info').success((function(_this) {
            return function(data) {
              return _this.initCustomNames(data.list['custom'].groups['custom'].templates);
            };
          })(this));
          return p;
        }
      };

      Admin_Templates_Ctrl_EmailTemplateEditor.prototype.initCustomNames = function(templates) {
        this.customNames = templates.map(function(x) {
          return x.showName.replace(/^.*?\//, '');
        });
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

//# sourceMappingURL=EmailTemplateEditor.js.map
