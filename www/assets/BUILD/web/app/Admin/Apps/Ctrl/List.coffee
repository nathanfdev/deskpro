define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Apps_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Apps_Ctrl_List'
    @CTRL_AS   = 'ListCtrl'
    @DEPS      = []


    init: ->
      @apps = [];
      @apps_v2 = [];
      @apps_v2_packages = [];

      @$scope.hide_installed = true;

      ctrl = @$scope.ListCtrl

      @$scope.packagesFilter = (hide_installed) ->
        is_installed = !hide_installed
        return (itm) ->
          return !itm.is_usersource_app && (!itm.is_installed || itm.is_installed == is_installed)

      @$scope.packagesV2Filter = (hide_installed) ->
        return (pkg) ->
          if !hide_installed
            return true

          for instance in ctrl.apps_v2
            if (pkg.id == instance.application_id)
              return false;

          return false
      return

    initialLoad: ->

      appsPromise = @Api.sendDataGet({ apps: '/apps' })
      appsPromise.then( (result) =>
        @packages = result.data.apps.packages

        # Dont list custom apps as "packages"
        @packages = @packages.filter((x) -> !x.is_custom)

        result.data.apps.apps.filter((x) -> !x.package.is_custom).forEach((app) => @apps.push(app))
        @custom_apps = result.data.apps.apps.filter((x) -> x.package.is_custom)
      )

      apps2Promise = @Api2.sendGet('/apps?include=app&inline_sideloads=true')
      apps2Promise.then( (result) =>
        result.data.data.forEach((instance) =>
          instance.app.icon_32 = instance.app.icon_url+ '?s=32';
          @apps_v2.push(instance)
        )
      )

      apps2PackagesPromise = @Api2.sendGet('/apps/packages')
      apps2PackagesPromise.then( (result) =>
        result.data.data.forEach((app) =>
          app.icon_48 = app.icon_url + '?s=48';
          @apps_v2_packages.push(app)
        )
      )

      return @$q.all([appsPromise, apps2Promise, apps2PackagesPromise])

    listInstalledAppsV2: () ->
      if @apps_v2 instanceof Array
        return @apps_v2;
      []

    addAppInstance: (instanceInfo, isNew = false) ->
      if isNew
        instanceInfo.app.icon_32 = instanceInfo.app.icon_url+ '?s=32';
        @apps_v2.push(instanceInfo)
      else
        @apps.push(instanceInfo)

        for p in @packages
          if p.name == instanceInfo.package.name
            if not p.apps then p.apps = []
            p.apps.push(instanceInfo)
            p.is_installed = true
            break

    removeAppInstance: (instanceId, isNew = false) ->
      if isNew
        @apps_v2 = @apps_v2.filter((x) -> return x.id != instanceId)
      else
        app = @apps.find((x) -> return x.id == instanceId)
        @apps = @apps.filter((x) -> return x.id != instanceId)
        @custom_apps = @custom_apps.filter((x) -> return x.id != instanceId)

        # we just removed an app so we might need to switch the
        # is_installed flag on the package so it appears back in the list
        if app
          hasOtherApp = false
          @apps.map((x) -> if x.package.name == app.package.name then hasOtherApp = true)
          if not hasOtherApp
            p = @packages.find((x) -> x.name == app.package.name)
            if p
              p.is_installed = false


    updateAppTitle: (id, title) ->
      @apps.filter((x) -> x.id == id).map((x) -> x.title = title)
      @custom_apps.filter((x) -> x.id == id).map((x) -> x.title = title)

    ensureCustomAppInList: (customApp) ->
      if not @custom_apps then return
      exist = @custom_apps.filter((x) -> x.id == customApp.id)
      if !exist.length
        @custom_apps.push(customApp)

    getInstallerRouteParams: (pkg) ->
      return { appName: encodeURIComponent(pkg.name) }


    showNewApp: ->
      saveNewApp = (options) =>
        postData = {
          options: options
        }
        @Api.sendPutJson('/apps/custom', postData).success( (info) =>
          @$state.go('apps.apps.custom_instance', {custom_id: "custom_" + info.id});
        )

      @$modal.open({
        templateUrl: @getTemplatePath('Apps/new-app-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.doCreate = ->
            $scope.is_loading = true
            saveNewApp($scope.opt).then(->
              $modalInstance.dismiss()
              $scope.is_loading = false
            , ->
              $scope.is_loading = false
            )

          $scope.opt = {
            ticket: {}
          }
        ]
      });

    showUploadApp: ->

      me = @
      @$modal.open({
        templateUrl: @getTemplatePath('Apps/upload-package-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->

          uploadDone = (data) ->
            # TODO: this is a hack to allow installation of packages which might be scoped to an organization, for instance
            # @deskproapps/app-mailchimp
            @normalizedPackageName = data.package_name.replace(/\//, '-').replace('@', '');
            me.initialLoad().then(->
              $modalInstance.dismiss()

              me.$timeout(->
                if (data.version == 2)
                  me.$state.go('apps.apps.install-v2-reload', {appName: encodeURIComponent(data.package_name)})
                else
                  me.$state.go('apps.go_apps_install', {name: 'go-apps-' + @normalizedPackageName})

              , 250)
            )

          uploadError = (data) ->
            $scope.form.error = data?.error_code || 'general'

          $scope.form = {}
          $scope.form.upload_type = 'upload'
          $scope.form.is_active = false

          $scope.dismiss = ->
            $modalInstance.dismiss()

          $scope.fileUploadOptions = {
            singleFileUploads: true,
            limitMultiFileUploads: 1,
            formData: {
              "API-TOKEN": window.DP_API_TOKEN,
              "REQUEST-TOKEN": window.DP_REQUEST_TOKEN,
              "SESSION-ID": window.DP_SESSION_ID
            }
          }

          $scope.$on('fileuploaddone', (e, data) ->
            $scope.form.is_active = false
            uploadDone(data.result)
          )
          $scope.$on('fileuploadfail', (e, data) ->
            $scope.form.is_active = false
            uploadError(data.result || {})
          )

          $scope.startUpload = ->
            $scope.form.error = null
            if $scope.form.upload_type == 'upload'
              $scope.form.is_active = true
              $scope.form.uploadScope.submit()
            else
              $scope.form.is_active = true
              me.Api.sendPost('/apps/upload-package', { file_url: $scope.form.upload_url }).then( (result) ->
                $scope.form.is_active = false
                uploadDone(result.data)
              , (result) ->
                $scope.form.is_active = false
                uploadError(result.data || {})
              )
        ]
      });

  Admin_Apps_Ctrl_List.EXPORT_CTRL()
