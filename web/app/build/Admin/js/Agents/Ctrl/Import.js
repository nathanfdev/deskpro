(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base', 'angular'], function(Admin_Ctrl_Base, angular) {
    var Admin_Agents_Ctrl_Import;
    Admin_Agents_Ctrl_Import = (function(_super) {
      __extends(Admin_Agents_Ctrl_Import, _super);

      function Admin_Agents_Ctrl_Import() {
        return Admin_Agents_Ctrl_Import.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_Import.CTRL_ID = 'Admin_Agents_Ctrl_Import';

      Admin_Agents_Ctrl_Import.CTRL_AS = 'Ctrl';

      Admin_Agents_Ctrl_Import.DEPS = ['$http', '$upload', 'DpLicense'];

      Admin_Agents_Ctrl_Import.prototype.init = function() {
        this.busy = false;
        this.restart();
        return this.$scope.fileUploadOptions = {
          url: this.$http.formatApiUrl('/import_csv_upload')
        };
      };

      Admin_Agents_Ctrl_Import.prototype.uploadFiles = function(files) {
        var file, _i, _len, _results;
        this.busy = true;
        this.page = 1;
        _results = [];
        for (_i = 0, _len = files.length; _i < _len; _i++) {
          file = files[_i];
          _results.push(this.$upload.upload({
            url: this.$scope.fileUploadOptions.url,
            file: file
          }).then((function(_this) {
            return function(data) {
              return _this.sendEmails(data.data.filename);
            };
          })(this), (function(_this) {
            return function() {
              _this.busy = false;
              return console.log('error');
            };
          })(this)));
        }
        return _results;
      };

      Admin_Agents_Ctrl_Import.prototype.restart = function() {
        this.page = 0;
        this.emails = [];
        this.results = [];
        return this.invited = 0;
      };

      Admin_Agents_Ctrl_Import.prototype.sendEmails = function(filename) {
        var agents;
        this.busy = true;
        agents = {};
        this.emails.map((function(_this) {
          return function(email) {
            return agents[email] = {
              email: email
            };
          };
        })(this));
        return this.Api.sendPostJson('/agents_bulk/check', {
          agents: agents,
          filename: filename
        }).then((function(_this) {
          return function(res) {
            _this.busy = false;
            if (res.data.need_plan) {
              return _this.DpLicense.openUpgradeLicense('upgrade_plan').then(function() {
                return _this.doSendEmails(filename);
              });
            } else {
              return _this.doSendEmails(filename);
            }
          };
        })(this), (function(_this) {
          return function() {
            return _this.busy = false;
          };
        })(this));
      };

      Admin_Agents_Ctrl_Import.prototype.doSendEmails = function(filename) {
        var agents;
        this.busy = true;
        this.page = 1;
        agents = {};
        this.emails.map((function(_this) {
          return function(email) {
            return agents[email] = {
              email: email
            };
          };
        })(this));
        return this.Api.sendPostJson('/agents_bulk', {
          agents: agents,
          filename: filename
        }).then((function(_this) {
          return function(data) {
            var email, entry, message, _ref, _ref1, _results;
            _this.busy = false;
            if (data.data.length != null) {
              return;
            }
            _ref = data.data;
            _results = [];
            for (email in _ref) {
              entry = _ref[email];
              if (entry.person_id != null) {
                _this.invited++;
              }
              if ('validation_error' === entry.error_code) {
                message = entry.error_message;
                if (((_ref1 = entry.errors) != null ? _ref1.errors : void 0) != null) {
                  message = '';
                  entry.errors.errors.map(function(error) {
                    return message += error.message + ' ';
                  });
                }
                entry = {
                  error: message
                };
              }
              entry._email = email;
              _results.push(_this.results.push(entry));
            }
            return _results;
          };
        })(this), (function(_this) {
          return function() {
            return _this.busy = false;
          };
        })(this));
      };

      Admin_Agents_Ctrl_Import.prototype.submitEmails = function() {
        if (!this.$scope.Form.$valid) {
          return false;
        }
        return this.sendEmails();
      };

      return Admin_Agents_Ctrl_Import;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_Import.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Import.js.map
