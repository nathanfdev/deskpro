define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
  class AdminStart_Ctrl_Home extends StartBase
    @CTRL_ID = 'AdminStart_Ctrl_Home'
    @DEPS    = ['$modal', '$location', 'AppState']

    init: ->
      @$scope.opt = {
        deskpro_url: '',
        deskpro_name: 'Helpdesk',
        timezone: 'UTC',
        has_lic: 'has_lic'
      }
      @$scope.opt.deskpro_url = (window.location.href+"").replace(/\/admin\/start(.*?)$/, '')+"/"
      @$scope.opt.deskpro_url = @$scope.opt.deskpro_url.replace(/\/index\.php\/?(.*?)$/, '')
      @$scope.opt.deskpro_url = @$scope.opt.deskpro_url.replace(/\/$/, '') + "/"

      tz = jstz.determine()
      if tz and tz.name()
        console.log("Detected timezone: %s", tz.name())
        @$scope.opt.timezone = tz.name()
      else
        console.log("Could not detect timezone")

      $('body').addClass('done-load');
      return

    saveAndContinue: (isValid) ->
      @$scope.has_submit = true
      @$scope.lic_error = false
      return if not isValid

      @$scope.is_loading = true
      @Api.sendPostJson('/start-settings', @$scope.opt).success((data) =>
        @$location.path('/email')
      ).error( (data) =>
        @$scope.is_loading = false
        if 'form_error' == data?.error_code
          @$scope.error_message = data.error_message
        else
          @$scope.lic_error = data.error_code || 'generic'

      )

    showRequestDemo: ->
      url   = @$scope.opt.deskpro_url
      name  = if @$scope.opt.first_name then @$scope.opt.first_name + ' ' + (@$scope.opt.last_name+'')
      email = @$scope.opt.email || ''

      @$modal.open({
        templateUrl: 'AdminInterface/Start/get-demo-modal.html',
        controller: ['$scope', '$modalInstance', '$http', 'Api', ($scope, $modalInstance, $http, Api) =>

          $scope.mode = 'auto'
          $scope.form_vals = {
            phone_country: '1',
            org_url: url
          }

          $scope.setInitialVals = (vals) ->
            $scope.form_vals.user_name = name || vals.user_name
            $scope.form_vals.email_address = email || vals.email_address
            $scope.form_vals.org_name = if vals.org_name != 'Example' then vals.org_name else ''
            $scope.vals = vals

          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.tryAgain = ->
            $scope.errors = {}
            $scope.is_done    = false
            $scope.is_error   = false
            $scope.is_success = false
            $scope.is_loading = false

          $scope.showManual = ->
            $scope.errors = {}
            $scope.is_done    = false
            $scope.is_error   = false
            $scope.is_success = false
            $scope.is_loading = false

            $scope.manual_requested = true
            if (!$scope.form.email_address.$error.required && !$scope.form.email_address.$error.email)
              $scope.mode = 'manual'
            else
              return

          $scope.showAuto = ->
            $scope.errors = {}
            $scope.is_done    = false
            $scope.is_error   = false
            $scope.is_success = false
            $scope.is_loading = false
            $scope.mode = 'auto'

          $scope.keyfile = ->
            url = Api.formatUrl('dp_license/keyfile.txt') + '?API-TOKEN=' + window.DP_API_TOKEN + '&SESSION-ID=' + window.DP_SESSION_ID + '&REQUEST-TOKEN=' + window.DP_REQUEST_TOKEN + '&email_address=' + $scope.form_vals.email_address
            if not window.open(url)
              window.location = url

          $scope.doGetLicense = (isValid) ->
            $scope.errors      = {}
            $scope.is_done    = false
            $scope.is_error   = false
            $scope.is_success = false

            $scope.has_submit = true
            return if not isValid

            $scope.is_loading = true

            postData = {
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
            }

            $http.jsonp($scope.vals.ma_server, { params: postData }).success( (data) ->
              $scope.is_loading = false
              $scope.is_done = true

              if data.success
                $scope.is_success = true
              else
                $scope.is_error = true
                $scope.errors = { unknown_request_error: true }

            ).error( (data) ->
              $scope.is_loading = false
              $scope.is_done    = true
              $scope.is_error   = true
              $scope.errors = { unknown_request_error: true }
            )
        ]
      });

  AdminStart_Ctrl_Home.EXPORT_CTRL()
