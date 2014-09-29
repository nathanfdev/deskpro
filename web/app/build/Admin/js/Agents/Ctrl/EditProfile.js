(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], function(Strings, Admin_Ctrl_Base) {
    var Admin_Agents_Ctrl_EditProfile;
    Admin_Agents_Ctrl_EditProfile = (function(_super) {
      __extends(Admin_Agents_Ctrl_EditProfile, _super);

      function Admin_Agents_Ctrl_EditProfile() {
        return Admin_Agents_Ctrl_EditProfile.__super__.constructor.apply(this, arguments);
      }

      Admin_Agents_Ctrl_EditProfile.CTRL_ID = 'Admin_Agents_Ctrl_EditProfile';

      Admin_Agents_Ctrl_EditProfile.CTRL_AS = 'Edit';

      Admin_Agents_Ctrl_EditProfile.DEPS = ['agent', 'saveMethod', '$modalInstance'];

      Admin_Agents_Ctrl_EditProfile.prototype.init = function() {
        var me;
        this.form = {};
        this.$scope.dismiss = (function(_this) {
          return function() {
            return _this.$modalInstance.dismiss();
          };
        })(this);
        this.form.timezone = this.agent.timezone || 'UTC';
        this.form.signature_html = this.agent.signature_html || '';
        if (this.agent.picture_blob) {
          this.form.picture_set = 'current';
        } else {
          this.form.picture_set = 'default';
        }
        me = this;
        this.uploadPictureCtrl = [
          '$scope', '$upload', '$http', function($iscope, $upload, $http) {
            $iscope.$watch('new_image', function(new_image) {
              return me.new_image = new_image;
            });
            return $iscope.onFileSelect = function(files) {
              var file;
              $iscope.is_loading_img = true;
              $iscope.error = false;
              $iscope.error_message = false;
              file = files[0];
              return $upload.upload({
                url: $http.formatApiUrl('/misc/upload'),
                data: {
                  is_image: true
                },
                file: file
              }).success(function(data) {
                $iscope.is_loading_img = false;
                return $iscope.new_image = data.blob;
              }).error(function(data) {
                $iscope.is_loading_img = false;
                $iscope.error = true;
                return $iscope.error_message = (data != null ? data.error_message : void 0) || null;
              });
            };
          }
        ];
        return this.$scope.doSave = (function(_this) {
          return function() {
            var p, postForm;
            postForm = {
              timezone: _this.form.timezone || 'UTC',
              signature_html: _this.form.signature_html || ''
            };
            if (_this.form.picture_set === 'default' && _this.agent.picture_blob) {
              postForm.unset_picture = true;
            } else if (_this.form.picture_set === 'upload' && _this.new_image) {
              postForm.set_picture_blob = _this.new_image.authcode;
            }
            _this.$scope.is_loading = true;
            p = _this.saveMethod(postForm, _this);
            if (p === true) {
              return window.setTimeout(function() {
                _this.$scope.is_loading = false;
                return _this.$modalInstance.dismiss();
              }, 500);
            } else {
              return p.then(function() {
                _this.$scope.is_loading = false;
                return _this.$modalInstance.dismiss();
              }, function() {
                return _this.$scope.is_loading = false;
              });
            }
          };
        })(this);
      };

      return Admin_Agents_Ctrl_EditProfile;

    })(Admin_Ctrl_Base);
    return Admin_Agents_Ctrl_EditProfile.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=EditProfile.js.map
