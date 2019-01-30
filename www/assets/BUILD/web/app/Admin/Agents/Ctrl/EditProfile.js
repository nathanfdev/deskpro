/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * DS206: Consider reworking classes to avoid initClass
 * DS207: Consider shorter variations of null checks
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
define([
  'DeskPRO/Util/Strings',
  'Admin/Main/Ctrl/Base'
], function(
  Strings,
  Admin_Ctrl_Base
) {
  class Admin_Agents_Ctrl_EditProfile extends Admin_Ctrl_Base {
    static initClass() {
      this.CTRL_ID   = 'Admin_Agents_Ctrl_EditProfile';
      this.CTRL_AS   = 'Edit';
      this.DEPS      = ['agent', 'saveMethod', '$modalInstance'];
    }

    init() {
      this.form = {};
      this.$scope.dismiss = () => {
        return this.$modalInstance.dismiss();
      };

      this.form.timezone = this.agent.timezone || 'UTC';
      this.form.signature_html = 'Loading...';

      this.Api.sendGet(`/agents/${this.agent.id}?extended=1`).success(data => {
        return this.form.signature_html = data.signature_html;
      });

      if (this.agent.picture_blob) {
        this.form.picture_set = 'current';
      } else {
        this.form.picture_set = 'default';
      }

      const me = this;
      this.uploadPictureCtrl = ['$scope', '$upload', '$http', function($iscope, $upload, $http) {
        $iscope.$watch('new_image', new_image => me.new_image = new_image);
        return $iscope.onFileSelect = function(files) {
          $iscope.is_loading_img = true;
          $iscope.error = false;
          $iscope.error_message = false;
          const file = files[0];
          return $upload.upload({
            url: $http.formatApiUrl('/misc/upload'),
            data: { is_image: true },
            file
          }).success( function(data) {
            $iscope.is_loading_img = false;
            return $iscope.new_image = data.blob;
          }).error( function(data) {
            $iscope.is_loading_img = false;
            $iscope.error = true;
            return $iscope.error_message = (data != null ? data.error_message : undefined) || null;
          });
        };
      }
      ];

      return this.$scope.doSave = () => {
        const postForm = {
          timezone:       this.form.timezone || 'UTC',
          signature_html: this.form.signature_html || ''
        };

        if ((this.form.picture_set === 'default') && this.agent.picture_blob) {
          postForm.unset_picture = true;
        } else if ((this.form.picture_set === 'upload') && this.new_image) {
          postForm.set_picture_blob = this.new_image.authcode;
        }

        this.$scope.is_loading = true;
        const p = this.saveMethod(postForm, this);

        if (p === true) {
          return window.setTimeout(() => {
            this.$scope.is_loading = false;
            return this.$modalInstance.dismiss();
          }
          , 500);
        } else {
          return p.then(() => {
            this.$scope.is_loading = false;
            return this.$modalInstance.dismiss();
          }
          , () => {
            return this.$scope.is_loading = false;
          });
        }
      };
    }
  }
  Admin_Agents_Ctrl_EditProfile.initClass();

  return Admin_Agents_Ctrl_EditProfile.EXPORT_CTRL();
});