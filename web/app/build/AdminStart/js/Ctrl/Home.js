(function() {
  var __hasProp = {}.hasOwnProperty,
    __extends = function(child, parent) { for (var key in parent) { if (__hasProp.call(parent, key)) child[key] = parent[key]; } function ctor() { this.constructor = child; } ctor.prototype = parent.prototype; child.prototype = new ctor(); child.__super__ = parent.prototype; return child; };

  define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
    var AdminStart_Ctrl_Home;
    AdminStart_Ctrl_Home = (function(_super) {
      __extends(AdminStart_Ctrl_Home, _super);

      function AdminStart_Ctrl_Home() {
        return AdminStart_Ctrl_Home.__super__.constructor.apply(this, arguments);
      }

      AdminStart_Ctrl_Home.CTRL_ID = 'AdminStart_Ctrl_Home';

      AdminStart_Ctrl_Home.DEPS = ['$modal', '$location'];

      AdminStart_Ctrl_Home.prototype.init = function() {
        var tz;
        this.$scope.opt = {
          deskpro_url: '',
          deskpro_name: 'Helpdesk',
          timezone: 'UTC',
          has_lic: 'has_lic'
        };
        this.$scope.opt.deskpro_url = (window.location.href + "").replace(/\/admin\/start(.*?)$/, '') + "/";
        this.$scope.opt.deskpro_url = this.$scope.opt.deskpro_url.replace(/\/index\.php\/?(.*?)$/, '');
        this.$scope.opt.deskpro_url = this.$scope.opt.deskpro_url.replace(/\/$/, '') + "/";
        tz = jstz.determine();
        if (tz && tz.name()) {
          console.log("Detected timezone: %s", tz.name());
          this.$scope.opt.timezone = tz.name();
        } else {
          console.log("Could not detect timezone");
        }
      };

      AdminStart_Ctrl_Home.prototype.saveAndContinue = function(isValid) {
        this.$scope.has_submit = true;
        this.$scope.lic_error = false;
        if (!isValid) {
          return;
        }
        this.$scope.is_loading = true;
        return this.Api.sendPostJson('/start-settings', {
          deskpro_url: this.$scope.opt.deskpro_url,
          deskpro_name: this.$scope.opt.deskpro_name,
          timezone: this.$scope.opt.timezone,
          license_code: this.$scope.opt.license
        }).success((function(_this) {
          return function(data) {
            return _this.$location.path('/cron');
          };
        })(this)).error((function(_this) {
          return function(data) {
            _this.$scope.is_loading = false;
            if (data && data.error_code) {
              return _this.$scope.lic_error = data.error_code;
            } else {
              return _this.$scope.lic_error = 'generic';
            }
          };
        })(this));
      };

      AdminStart_Ctrl_Home.prototype.showRequestDemo = function() {
        var url;
        url = this.$scope.opt.deskpro_url;
        return this.$modal.open({
          templateUrl: 'AdminInterface/Start/get-demo-modal.html',
          controller: [
            '$scope', '$modalInstance', '$http', 'Api', (function(_this) {
              return function($scope, $modalInstance, $http, Api) {
                $scope.mode = 'auto';
                $scope.form_vals = {
                  phone_country: '1',
                  org_url: url
                };
                $scope.setInitialVals = function(vals) {
                  $scope.form_vals.user_name = vals.user_name;
                  $scope.form_vals.email_address = vals.email_address;
                  $scope.form_vals.org_name = vals.org_name;
                  return $scope.vals = vals;
                };
                $scope.dismiss = function() {
                  return $modalInstance.dismiss();
                };
                $scope.tryAgain = function() {
                  $scope.errors = {};
                  $scope.is_done = false;
                  $scope.is_error = false;
                  $scope.is_success = false;
                  return $scope.is_loading = false;
                };
                $scope.showManual = function() {
                  $scope.errors = {};
                  $scope.is_done = false;
                  $scope.is_error = false;
                  $scope.is_success = false;
                  $scope.is_loading = false;
                  return $scope.mode = 'manual';
                };
                $scope.showAuto = function() {
                  $scope.errors = {};
                  $scope.is_done = false;
                  $scope.is_error = false;
                  $scope.is_success = false;
                  $scope.is_loading = false;
                  return $scope.mode = 'auto';
                };
                $scope.keyfile = function() {
                  url = Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN;
                  if (!window.open(url)) {
                    return window.location = url;
                  }
                };
                return $scope.doGetLicense = function(isValid) {
                  var postData;
                  $scope.errors = {};
                  $scope.is_done = false;
                  $scope.is_error = false;
                  $scope.is_success = false;
                  $scope.has_submit = true;
                  if (!isValid) {
                    return;
                  }
                  $scope.is_loading = true;
                  postData = {
                    build: $scope.vals.build,
                    install_key: $scope.vals.install_key || "",
                    install_token: $scope.vals.install_token || "",
                    email_address: $scope.form_vals.email_address || "",
                    user_name: $scope.form_vals.user_name || "",
                    website_name: $scope.form_vals.org_name || "",
                    website_url: $scope.form_vals.org_url || "",
                    phone_country: $scope.form_vals.phone_country || "",
                    phone: $scope.form_vals.phone || "",
                    phone_ext: $scope.form_vals.phone_ext || "",
                    callback: "JSON_CALLBACK"
                  };
                  return $http.jsonp($scope.vals.ma_server, {
                    params: postData
                  }).success(function(data) {
                    $scope.is_loading = false;
                    $scope.is_done = true;
                    if (data.success) {
                      return $scope.is_success = true;
                    } else {
                      $scope.is_error = true;
                      return $scope.errors = {
                        unknown_request_error: true
                      };
                    }
                  }).error(function(data) {
                    $scope.is_loading = false;
                    $scope.is_done = true;
                    $scope.is_error = true;
                    return $scope.errors = {
                      unknown_request_error: true
                    };
                  });
                };
              };
            })(this)
          ]
        });
      };

      return AdminStart_Ctrl_Home;

    })(StartBase);
    return AdminStart_Ctrl_Home.EXPORT_CTRL();
  });

}).call(this);

//# sourceMappingURL=Home.js.map
