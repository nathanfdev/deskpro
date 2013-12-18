(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['Admin/Main/Ctrl/Base'], function(Admin_Ctrl_Base) {
    var Admin_Portal_Ctrl_PortalEditor, _ref;
    Admin_Portal_Ctrl_PortalEditor = (function(_super) {
      __extends(Admin_Portal_Ctrl_PortalEditor, _super);

      function Admin_Portal_Ctrl_PortalEditor() {
        _ref = Admin_Portal_Ctrl_PortalEditor.__super__.constructor.apply(this, arguments);
        return _ref;
      }

      Admin_Portal_Ctrl_PortalEditor.CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor';

      Admin_Portal_Ctrl_PortalEditor.CTRL_AS = 'Portal';

      Admin_Portal_Ctrl_PortalEditor.prototype.init = function() {
        this.portal_enabled = false;
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.initialLoad = function() {
        var promise,
          _this = this;
        promise = this.Api.sendGet('/portal_settings').then(function(result) {
          var settings;
          settings = result.data.portal_settings;
          _this.portal_enabled = settings.portal_enabled;
          _this.favicon_url = settings.favicon_blob_url || '/favicon.ico';
          return _this.favicon_blob_id = settings.favicon_blob_id || null;
        });
        return promise;
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.startTogglePortal = function() {
        var message,
          _this = this;
        if (this.portal_enabled) {
          message = "Are are sure you want to disable the portal? The front-end portal website for end-users will be completely disabled. Anyone who knows the URL of the portal will see a blank page.";
        } else {
          message = "Are are sure you want to enable the portal?";
        }
        this.showConfirm(message).result.then(function() {
          return _this.togglePortal();
        });
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.togglePortal = function() {
        var newVal,
          _this = this;
        newVal = this.portal_enabled ? '0' : '1';
        return this.Api.sendPost('/settings/values/user.portal_enabled', {
          value: newVal
        }).then(function() {
          return _this.$state.go('portal.portal_editor_go');
        });
      };

      Admin_Portal_Ctrl_PortalEditor.prototype.openFaviconEditor = function() {
        var favi_type, inst, portalCtrl,
          _this = this;
        if (this.favicon_blob_id) {
          favi_type = 'custom';
        } else {
          favi_type = 'default';
        }
        portalCtrl = this;
        return inst = this.$modal.open({
          templateUrl: this.getTemplatePath('PortalEditor/change-favicon-modal.html'),
          controller: [
            '$scope', '$modalInstance', function($scope, $modalInstance) {
              $scope.upload_url = _this.Api.formatUrl('/blobs') + '?API-TOKEN=' + _this.Api.api_token;
              $scope.fileSelected = false;
              $scope.fileTransferStarted = false;
              $scope.favicon_choice = favi_type;
              $scope.favicon_saving = false;
              if (portalCtrl.favicon_blob_id) {
                $scope.custom_favicon_blob_url = portalCtrl.favicon_url;
              }
              $scope.form = {
                favicon_choice: favi_type
              };
              $scope.$on('fileuploaddone', function(e, data) {
                var _ref1;
                if (((_ref1 = data.result.blob) != null ? _ref1.id : void 0) && data.result.blob.is_image) {
                  $scope.new_blob_id = data.result.blob.id;
                  $scope.new_blob_auth = data.result.blob.authcode;
                  return $scope.new_blob_url = data.result.blob.thumbnail_url_16;
                } else {
                  $scope.new_uploaded_failed = true;
                  $scope.new_blob_id = null;
                  return $scope.fileSelected = false;
                }
              });
              $scope.$on('fileuploadfail', function(e, data) {
                $scope.new_uploaded_failed = true;
                $scope.new_blob_id = null;
                return $scope.fileSelected = false;
              });
              $scope.$on('fileuploadchange', function(e, data) {
                $scope.new_uploaded_failed = false;
                $scope.new_blob_id = null;
                $scope.new_blob_url = null;
                return $scope.fileSelected = true;
              });
              $scope.confirm = function() {
                var blob_auth, blob_id,
                  _this = this;
                if ($scope.form.favicon_choice === favi_type || $scope.form.favicon_choice === 'new' && !$scope.new_blob_id) {
                  $modalInstance.close();
                  return;
                }
                blob_id = $scope.form.favicon_choice === 'default' ? 0 : $scope.new_blob_id;
                blob_auth = $scope.form.favicon_choice === 'default' ? '0' : $scope.new_blob_auth;
                $scope.favicon_saving = true;
                return portalCtrl.Api.sendPost("/portal_settings/favicon/" + blob_id + "/" + blob_auth).then(function() {
                  $scope.favicon_saving = false;
                  portalCtrl.favicon_url = blob_id === 0 ? '/favicon.ico' : $scope.new_blob_url;
                  portalCtrl.favicon_blob_id = blob_id || null;
                  return $modalInstance.close();
                });
              };
              return $scope.dismiss = function() {
                return $modalInstance.dismiss();
              };
            }
          ]
        });
      };

      return Admin_Portal_Ctrl_PortalEditor;

    })(Admin_Ctrl_Base);
    return Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL();
  });

}).call(this);

/*
//@ sourceMappingURL=PortalEditor.js.map
*/