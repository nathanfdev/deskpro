define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class AdminPortalCtrlPortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'AdminPortalCtrlPortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout', '$upload', '$modal', 'Growl']

    init: =>
      @open_panels = ['theme', 'colors', 'advanced', 'expert']
      @recompiling = false
      @commiting = false
      @savingMulti = false
      @advanced = {main_scss: '', custom_scss: '', javascript: ''}
      @available_themes = [
        {id: "standard", title: "Standard"},
        {id: "sidebar", title: "Sidebar"}
      ]
      @$scope.brand_id = @$stateParams.brandId

      @$scope.welcome_box = {
        title: '',
        message: ''
      }
      @welcome_box = angular.copy(@$scope.welcome_box)

      @$scope.values = {}
      @$scope.errors = {
        favicon: false
        logo: false
      }

      @values = angular.copy(@$scope.values)

      @advanced_tab = 'header'
      @is_advanced_expanded = false
      @asset_files = []
      @custom_logo = null
      @custom_favicon = null
      @uploading_files_count = 0
      @template_options = []
      @selected_template = null
      @selected_template_info = {}
      @selected_template_info_loaded = false
      @css_template_info = null
      @css_template_selected = null
      @preview_as_expanded = false
      @preview_as = 'myself'
      @preview_as_email = null
      @selected_theme = null
      @theme_set = null

    save: =>
      promises = [@saveValues(), @editWelcomeBox()]
      @savingMulti = true
      all = @$q.all(promises)
      all.then( =>
        @$http({
          method: 'PUT',
          url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/advanced-edits',
          data: @advanced
        }).then( =>
          @savingMulti = false
          @refreshPreviewUrl()
        , =>
          @serverError()
          @savingMulti = false
        )
      , =>
        @serverError()
        @savingMulti = false
      )

    editTheme: =>
      request = @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/info',
        data: {
          theme_id: @selected_theme
        }
      })
      @recompiling = true
      request.then(
        () => @refreshPreviewUrl(); @recompiling = false,
        () => @serverError(); @recompiling = false
      )

    saveWelcomeBox: =>
      @editWelcomeBox().then(
        () => if not @savingMulti then @refreshPreviewUrl()
      )

    clearWelcomeBox: =>
       @welcome_box = {title: '', message: ''}
       @saveWelcomeBox()

    editWelcomeBox: =>
      request = @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/welcome-message',
        data: @$scope.welcome_box
      })
      @recompiling = true
      request.then(
        () => @recompiling = false; @welcome_box = angular.copy(@$scope.welcome_box),
        () => @serverError(); @recompiling = false
      )

    saveValues: =>
      request = @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/variable-values',
        data: @$scope.values
      })
      @recompiling = true
      request.then(
        () => @recompiling = false; @values = angular.copy(@$scope.values),
        (message) => @recompiling = false; @serverError(message)
      )
      return request

    commit: () =>
      @showConfirm('Are you sure you want to apply this changes to the portal?', 'Confirm save').result.then(
        () =>
          @commiting = true
          if @isDirtyState()
            promises = [@saveValues(), @editWelcomeBox()]
          else
            promises = [true]

          all = @$q.all(promises)
          all.then(
            () => @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/commit').then(
              () => @success('Changes were applied to the portal'); @commiting = false,
              () => @serverError(); @commiting = false),
            () => @commiting = false
          )
      )

    discard: () =>
      @showConfirm('Are you sure you want to discard all changes you\'ve made?', 'Confirm discard').result.then(
        () =>
          @recompiling = true
          request = @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/discard')
          promises = [request, @loadAdvancedEdits(), @loadLogo(), @loadFavicon(), @loadValues()]
          all = @$q.all(promises)
          all.then(
            () => new Promise( (resolve) => resolve(@refreshPreviewUrl())).then(() => @success('Changes were discarded'); @recompiling = false),
            () => @serverError(); @recompiling = false
          )
      )

    initialLoad: =>
      d = @$q.defer()
      @Api2.sendGet('brands/'+@$scope.brand_id).then (res) =>
        @$scope.baseUrl  = window.DP_BASE_URL+'b/'+res.data.data.slug
        @refreshPreviewUrl()

        @$q.all([
          @$http.get(@$scope.baseUrl+'/portal/api/style/variable-groups').success((data) => @groups = data),
          @loadValues(),
          @loadAdvancedEdits(),
          @loadAssetFiles(),
          @loadLogo(),
          @loadFavicon(),
          @loadTemplateOptions(),
          @loadThemeSet()
          @loadWelcomeBox()
        ]).then(->
          d.resolve()
        )

      d.promise

    togglePanel: (name) =>
      if name in @open_panels
        @open_panels = @open_panels.filter (e) -> e != name
      else
        @open_panels.push name

    isOpen: (name) =>
      name in @open_panels

    label: (sys_name) =>
      sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

    refreshPreviewUrl: =>
      preview_url = window.DP_BASE_URL+'admin-preview-'+@$scope.brand_id+'?anti-cache=' + (new Date()).getTime()
      if @preview_as is 'user' or @preview_as is 'agent' then preview_url += '&_preview_as=' + @preview_as_email
      if @preview_as is 'myself' then preview_url += '&_preview_as=_exit'
      if @preview_as is 'guest' then preview_url += '&_preview_as=_anon'
      @preview_url = preview_url

    loadValues: (success) =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/variable-values').success(
        (values) =>
          angular.extend(@$scope.values, values)
          @values = angular.copy(@$scope.values)

          if success
            success()
      )

    loadTemplateOptions: () =>
      template_options = []
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/templates').success(
        (templates) =>
          for template in templates
            template_options.push({
              value: template.name,
              custom: template.is_custom,
              name: @templateName(template),
              group: @templateGroup(template)
            })
          @template_options = template_options
      )

    templateName: (template) =>
      name = template.name.split(':')[2].replace(/\.twig/, '')
      if template.is_custom then name = '(*)' + name
      return name
    templateGroup: (template) =>
      parts = template.name.split(':')
      if parts[1] then parts[1] else parts[0]

    editTemplate: =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/template-info?template=' + @selected_template).success((data) =>
        @selected_template_info = {
          code: data.source,
          is_custom: data.is_custom
        }
        @selected_template_info_loaded = true
      )

    openTemplateEditor: (tpl) =>
      @selected_template = tpl
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/template-info?template=' + @selected_template).success((data) =>
        @selected_template_info = {
          code: data.source,
          is_custom: data.is_custom
        }
        @selected_template_info_loaded = true
      )

    saveTemplateEditor: () =>
      @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/template-sources?template=' + @selected_template,
        data: angular.toJson({code: @selected_template_info.code})
      })
      .success(
        () =>
          @selected_template = null
          @selected_template_info_loaded = false
          @loadTemplateOptions()
      )
      .error(@serverError)

    revertTemplateEditor: () =>
      @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/template-sources?template=' + @selected_template,
        data: angular.toJson({revert: true})
      })
      .error(@serverError)

      @selected_template = null
      @selected_template_info_loaded = false

    openCssEditor: (type) =>
      @css_template_selected = true
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/advanced-edits').success((data) =>
        @css_template_info = {
          loaded: true,
          type: type,
          code: data[type],
          is_custom: true
        }
      )

    cancelCssEditor: () =>
      @css_template_selected = false
      @css_template_info = false

    saveCssEditor: () =>
      data = {}
      data[@css_template_info.type] = @css_template_info.code
      @advanced[@css_template_info.type] = @css_template_info.code
      @recompiling = true

      req = @$http({
        method: 'PUT',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/advanced-edits',
        data: angular.toJson(data)
      })
      .success(=> @recompiling = false)
      .error((message) => @recompiling = false; @serverError(message))

      @css_template_selected = null
      @css_template_info = false

      return req

    resetCssEditor: () =>
      data = {}
      data[@css_template_info.type] = true
      req = @$http(
        method: 'DELETE',
        url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/advanced-edits',
        data: angular.toJson(data)
        headers:
          "Content-Type": "application/json"
      )
      .success(=> @recompiling = false)
      .error((message) => @recompiling = false; @serverError(message))

      @css_template_selected = null
      @css_template_info = false
      req

    cancelTemplateEditor: =>
      @selected_template = null
      @selected_template_info_loaded = false

    loadAdvancedEdits: (success) =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/advanced-edits').success(
        (advanced) =>
          angular.extend(@advanced, advanced)
          if success
            success()
      )

    loadAssetFiles: () =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/assets').success(
        (response) => angular.extend(@asset_files, response.data)
      )

    loadThemeSet: () =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/info').success((data) =>
        @theme_set = data
        @selected_theme = @theme_set.theme_id
      )

    loadWelcomeBox: () =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/welcome-message').success((response) =>
        @$scope.welcome_box = response.data
        @welcome_box = angular.copy(@$scope.welcome_box)
      )

    loadLogo: () =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/logo').success((response) => @custom_logo = response.data?.url)

    loadFavicon: () =>
      @$http.get(@$scope.baseUrl+'/portal/api/style/edit-theme-set/favicon').success((response) => @custom_favicon = response.data?.url)

    upload: (files) =>
      for file in files
        @uploading_files_count++
        @$upload.upload({
          url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/assets',
          file: file
        }).then(
          (response) =>
            @uploading_files_count--
            @asset_files.unshift(response.data.data)
          ,
          () => @error('Server error occurred. Unable to upload files.')
        )

    uploadLogo: (files) =>
      @$upload
        .upload({url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/logo', file: files[0]})
        .then(
          (response) =>
            @$scope.errors.logo = false
            @custom_logo = response.data.data.url
          (response) =>
            @$scope.errors.logo = response.data.fields.file.errors[0].message
        )
    uploadFavicon: (files) =>
      @$upload
        .upload({url: @$scope.baseUrl+'/portal/api/style/edit-theme-set/favicon', file: files[0]})
        .then(
          (response) =>
            @$scope.errors.favicon = false
            @custom_favicon = response.data.data.url
          (response) =>
            @$scope.errors.favicon = response.data.fields.file.errors[0].message
        )

    copyUrl: (file) =>
      window.prompt('Copy this:', file.url)
      return

    isDirtyState: =>
      return true if not angular.equals(@welcome_box, @$scope.welcome_box)
      return true if not angular.equals(@values, @$scope.values)

      return false

    notifyUrlCopied: () =>
      @Growl.success('File URL was copied to your clipboard')
      return

    delete: (file) =>
      if window.confirm('Are you sure you want to remove ' + file.name + '?')
        @$http.delete(@$scope.baseUrl+'/portal/api/style/edit-theme-set/assets/' + file.id).success(
          () => @asset_files = @asset_files.filter (f) -> f isnt file
        )

    deleteLogo: () =>
      @$http.delete(@$scope.baseUrl+'/portal/api/style/edit-theme-set/logo').success(() => @custom_logo = null)

    deleteFavicon: () =>
      @$http.delete(@$scope.baseUrl+'/portal/api/style/edit-theme-set/favicon').success(() => @custom_favicon = null)

    openAdvancedTab: (tab) => @advanced_tab = tab
    isAdvancedTab: (tab) => @advanced_tab == tab

    isAdvancedExpanded: () => @is_advanced_expanded
    collapseAdvanced: () => @is_advanced_expanded = false
    expandAdvanced: () => @is_advanced_expanded = true

    canPreview: =>
      !@commiting and !@recompiling and !@savingMulti and (@preview_as is 'guest' or @preview_as is 'myself' or @preview_as_email)

    previewAs: (mode) =>
      @preview_as = mode
      if mode is 'user' or mode is 'agent' then @promptEmail()
      @preview_as_expanded = false
      @preview_as_email = null
      @refreshPreviewUrl()

    promptEmail: =>
      baseUrl = @$scope.baseUrl

      modalInstance = @$modal.open({
        templateUrl: @getTemplatePath('Portal/Editor/email-modal.html'),
        controller: ['$scope', '$modalInstance', '$http', 'preview_as', ($scope, $modalInstance, $http, preview_as) ->
          $scope.email = ''
          $scope.preview_as = preview_as
          $scope.ok = () -> $modalInstance.close(@email)
          $scope.cancel = () -> $modalInstance.dismiss('cancel')
          $scope.loadEmails = (val) ->
            $http.get(baseUrl+'/portal/api/emails?term=' + val + '&target=' + preview_as)
                 .then((response) => response.data)
        ],
        resolve: {
          preview_as: () => @preview_as
        }
      })
      modalInstance.result.then((email) => @preview_as_email = email; @refreshPreviewUrl())

    error: (message) => @showAlert(message, 'Changes were not applied')
    success: (message) => @Growl.success(message)
    serverError: (message) =>
      if message and message.message
        @error('Server error occurred. Unable to save data (' + message.message + ').')
      else
        @error('Server error occurred. Unable to save data.')

  AdminPortalCtrlPortalEditor.EXPORT_CTRL()
