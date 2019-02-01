define(['AdminStart/Ctrl/StartBase'], function(StartBase) {
  class AdminStart_Ctrl_Home extends StartBase {
    static initClass() {
      this.CTRL_ID = 'AdminStart_Ctrl_Home';
      this.DEPS    = ['$modal', '$location', 'AppState'];
    }

    init() {
      this.$scope.opt = {
        deskpro_url: '',
        deskpro_name: 'Helpdesk',
        timezone: 'UTC',
        has_lic: 'has_lic'
      };
      this.$scope.opt.deskpro_url = (window.location.href+"").replace(/\/admin\/start(.*?)$/, '')+"/";
      this.$scope.opt.deskpro_url = this.$scope.opt.deskpro_url.replace(/\/index\.php\/?(.*?)$/, '');
      this.$scope.opt.deskpro_url = this.$scope.opt.deskpro_url.replace(/\/$/, '') + "/";

      const tz = jstz.determine();
      if (tz && tz.name()) {
        console.log("Detected timezone: %s", tz.name());
        this.$scope.opt.timezone = tz.name();
      } else {
        console.log("Could not detect timezone");
      }

      $('body').addClass('done-load');
    }

    saveAndContinue(isValid) {
      this.$scope.has_submit = true;
      this.$scope.lic_error = false;
      if (!isValid) { return; }

      this.$scope.is_loading = true;
      return this.Api.sendPostJson('/start-settings', this.$scope.opt).success(data => {
        return this.$location.path('/email');
      }).error( data => {
        this.$scope.is_loading = false;
        if ('form_error' === (data != null ? data.error_code : undefined)) {
          return this.$scope.error_message = data.error_message;
        } else {
          return this.$scope.lic_error = data.error_code || 'generic';
        }

      });
    }

    showRequestDemo() {
      let url   = this.$scope.opt.deskpro_url;
      const name  = this.$scope.opt.first_name ? this.$scope.opt.first_name + ' ' + (this.$scope.opt.last_name+'') : undefined;
      const email = this.$scope.opt.email || '';

      return this.$modal.open({
        templateUrl: 'AdminInterface/Start/get-demo-modal.html',
        controller: ['$scope', '$modalInstance', '$http', 'Api', ($scope, $modalInstance, $http, Api) => {

          $scope.mode = 'auto';
          $scope.form_vals = {
            phone_country: '1',
            org_url: url
          };

          $scope.setInitialVals = function(vals) {
            $scope.form_vals.user_name = name || vals.user_name;
            $scope.form_vals.email_address = email || vals.email_address;
            $scope.form_vals.org_name = vals.org_name !== 'Example' ? vals.org_name : '';
            return $scope.vals = vals;
          };

          $scope.dismiss = () => $modalInstance.dismiss();

          $scope.tryAgain = function() {
            $scope.errors = {};
            $scope.is_done    = false;
            $scope.is_error   = false;
            $scope.is_success = false;
            return $scope.is_loading = false;
          };

          $scope.showManual = function() {
            $scope.errors = {};
            $scope.is_done    = false;
            $scope.is_error   = false;
            $scope.is_success = false;
            $scope.is_loading = false;

            $scope.manual_requested = true;
            if (!$scope.form.email_address.$error.required && !$scope.form.email_address.$error.email) {
              return $scope.mode = 'manual';
            } else {
              return;
            }
          };

          $scope.showAuto = function() {
            $scope.errors = {};
            $scope.is_done    = false;
            $scope.is_error   = false;
            $scope.is_success = false;
            $scope.is_loading = false;
            return $scope.mode = 'auto';
          };

          $scope.keyfile = function() {
            url = Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN + '&email_address=' + $scope.form_vals.email_address;
            if (!window.open(url)) {
              return window.location = url;
            }
          };

          return $scope.doGetLicense = function(isValid) {
            $scope.errors      = {};
            $scope.is_done    = false;
            $scope.is_error   = false;
            $scope.is_success = false;

            $scope.has_submit = true;
            if (!isValid) { return; }

            $scope.is_loading = true;

            const postData = {
              build:         $scope.vals.build,
              install_key:   $scope.vals.install_key || "",
              install_token: $scope.vals.install_token || "",
              email_address: $scope.form_vals.email_address || "",
              user_name:     $scope.form_vals.user_name || "",
              website_name:  $scope.form_vals.org_name || "",
              website_url:   $scope.form_vals.org_url || "",
              phone_country: $scope.form_vals.phone_country || "",
              phone:         $scope.form_vals.phone || "",
              phone_ext:     $scope.form_vals.phone_ext || "",
              callback:      "JSON_CALLBACK"
            };

            return $http.jsonp($scope.vals.ma_server, { params: postData }).success( function(data) {
              $scope.is_loading = false;
              $scope.is_done = true;

              if (data.success) {
                return $scope.is_success = true;
              } else {
                $scope.is_error = true;
                return $scope.errors = { unknown_request_error: true };
              }

            }).error( function(data) {
              $scope.is_loading = false;
              $scope.is_done    = true;
              $scope.is_error   = true;
              return $scope.errors = { unknown_request_error: true };
            });
          };
        }
        ]
      });
    }
  }
  AdminStart_Ctrl_Home.initClass();

  return AdminStart_Ctrl_Home.EXPORT_CTRL();
});
