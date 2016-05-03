define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout', '$upload', '$modal', 'Growl']

    init: ->
      @open_panels = []
      @values = {}
      @recompiling = false
      @advanced = {header: '', footer: '', scss: '', javascript: ''}
      @available_themes = [
        {id: "standard", title: "Standard"},
        {id: "sidebar", title: "Sidebar"}
      ]
      @welcome_box = {
        title: '',
        message: ''
      }
      @advanced_tab = 'header'
      @is_advanced_expanded = false
      @asset_files = []
      @custom_logo = null
      @uploading_files_count = 0
      @template_options = []
      @selected_template = null
      @selected_template_code = ''
      @selected_template_code_loaded = false
      @preview_as_expanded = false
      @preview_as = 'myself'
      @preview_as_email = null
      @selected_theme = null
      @theme_set = null;
      @refreshPreviewUrl()

    save: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/advanced-edits',
        data: @advanced
      })
      @recompiling = true
      request.then(
        @saveValues,
        () => @serverError(); @recompiling = false
      )

    editTheme: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/info',
        data: {
          theme_id: @selected_theme
        }
      })
      @recompiling = true
      request.then(
        () => @refreshPreviewUrl(); @recompiling = false,
        () => @serverError(); @recompiling = false
      )

     editWelcomeBox: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/welcome-message',
        data: @welcome_box
      })
      @recompiling = true
      request.then(
        () => @refreshPreviewUrl(); @recompiling = false,
        () => @serverError(); @recompiling = false
      )

    saveValues: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/variable-values',
        data: @values
      })
      @recompiling = true
      request.then(
        () => @refreshPreviewUrl(); @recompiling = false,
        () => @serverError(); @recompiling = false
      )

    commit: () ->
      if window.confirm('Are you sure you want to apply this changes to the portal?')
        @$http.get('/portal/api/style/edit-theme-set/commit').then(
          () => @success('Changes were applied to the portal'),
          () => @serverError(); @recompiling = false
        );

    discard: () ->
      if window.confirm('Are you sure you want to discard all changes you\'ve made?')
        @recompiling = true
        @$http.get('/portal/api/style/edit-theme-set/discard').then(
          () => @loadAdvancedEdits(
            () =>
              @loadLogo()
              @loadValues(() => @success('Changes were discarded'); @recompiling = false)),
          () => @serverError(); @recompiling = false
        );

    initialLoad: ->
      @$q.all([
        @$http.get('/portal/api/style/variable-groups').success((data) => @groups = data),
        @loadValues(),
        @loadAdvancedEdits(),
        @loadAssetFiles(),
        @loadLogo(),
        @loadTemplateOptions(),
        @loadThemeSet()
        @loadWelcomeBox()
      ])

    togglePanel: (name) ->
      if name in @open_panels
        @open_panels = @open_panels.filter (e) -> e != name
      else
        @open_panels.push name

    isOpen: (name) ->
      name in @open_panels

    label: (sys_name) ->
      sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

    refreshPreviewUrl: =>
      preview_url = '/admin-preview?anti-cache=' + (new Date()).getTime()
      if @preview_as is 'user' or @preview_as is 'agent' then preview_url += '&_preview_as=' + @preview_as_email
      if @preview_as is 'myself' then preview_url += '&_preview_as=_exit'
      if @preview_as is 'guest' then preview_url += '&_preview_as=_anon'
      @preview_url = preview_url

    loadValues: (success) ->
      @$http.get('/portal/api/style/edit-theme-set/variable-values').success(
        (values) =>
          angular.extend(@values, values)
          if success
            success()
      )

    loadTemplateOptions: () ->
      @$http.get('/portal/api/style/edit-theme-set/templates').success(
        (templates) =>
          for template in templates
            @template_options.push({
              value: template,
              name: @templateName(template),
              group: @templateGroup(template)
            })
      )

    templateName: (template) -> template.split(':')[2].replace(/\.twig/, '')
    templateGroup: (template) ->
      parts = template.split(':')
      if parts[1] then parts[1] else parts[0]

    editTemplate: () =>
      @$http.get('/portal/api/style/edit-theme-set/template-sources?template=' + @selected_template).success(
        (code) => @selected_template_code = angular.fromJson(code); @selected_template_code_loaded = true
      )

    closeTemplateEditor: () =>
      @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/template-sources?template=' + @selected_template,
        data: angular.toJson({code: @selected_template_code})
      })
      .error(@serverError)

      @selected_template = null
      @selected_template_code = null
      @selected_template_code_loaded = false

    loadAdvancedEdits: (success) ->
      @$http.get('/portal/api/style/edit-theme-set/advanced-edits').success(
        (advanced) =>
          angular.extend(@advanced, advanced)
          if success
            success()
      )

    loadAssetFiles: () ->
      @$http.get('/portal/api/style/edit-theme-set/assets').success(
        (response) => angular.extend(@asset_files, response.data)
      )

    loadThemeSet: () ->
      @$http.get('/portal/api/style/edit-theme-set/info').success((data) =>
        @theme_set = data
        @selected_theme = @theme_set.theme_id
      )

    loadWelcomeBox: () ->
      @$http.get('/portal/api/style/edit-theme-set/welcome-message').success((data) =>
        @welcome_box = data
      )

    loadLogo: () ->
      @$http.get('/portal/api/style/edit-theme-set/logo').success((response) => @custom_logo = response.data?.url)

    upload: (files) =>
      for file in files
        @uploading_files_count++;
        @$upload.upload({
          url: '/portal/api/style/edit-theme-set/assets',
          file: file
        }).then(
          (response) =>
            @uploading_files_count--
            @asset_files.unshift(response.data.data)
          ,
          () => @error('Server error occurred. Unable to upload files.')
        );

    uploadLogo: (files) =>
      @$upload
        .upload({url: '/portal/api/style/edit-theme-set/logo', file: files[0]})
        .then(
          (response) => @custom_logo = response.data.data.url,
          () => @error('Server error occurred. Unable to upload files.')
        );

    copyUrl: (file) ->
      window.prompt('Copy this:', file.url)
      return

    notifyUrlCopied: () ->
      @Growl.success('File URL was copied to your clipboard');
      return

    delete: (file) =>
      if window.confirm('Are you sure you want to remove ' + file.name + '?')
        @$http.delete('/portal/api/style/edit-theme-set/assets/' + file.id).success(
          () => @asset_files = @asset_files.filter (f) -> f isnt file
        )

    deleteLogo: () =>
      @$http.delete('/portal/api/style/edit-theme-set/logo').success(() => @custom_logo = null)

    openAdvancedTab: (tab) => @advanced_tab = tab
    isAdvancedTab: (tab) => @advanced_tab == tab

    isAdvancedExpanded: () => @is_advanced_expanded
    collapseAdvanced: () => @is_advanced_expanded = false
    expandAdvanced: () => @is_advanced_expanded = true

    canPreview: () =>
      !@recompiling and (@preview_as is 'guest' or @preview_as is 'myself' or @preview_as_email)

    previewAs: (mode) =>
      @preview_as = mode
      if mode is 'user' or mode is 'agent' then @promptEmail()
      @preview_as_expanded = false
      @preview_as_email = null
      @refreshPreviewUrl()

    promptEmail: () =>
      modalInstance = @$modal.open({
        templateUrl: @getTemplatePath('Portal/Editor/email-modal.html'),
        controller: ['$scope', '$modalInstance', '$http', 'preview_as', ($scope, $modalInstance, $http, preview_as) ->
          $scope.email = '';
          $scope.preview_as = preview_as
          $scope.ok = () -> $modalInstance.close(@email)
          $scope.cancel = () -> $modalInstance.dismiss('cancel')
          $scope.loadEmails = (val) ->
            $http.get('/portal/api/emails?term=' + val + '&target=' + preview_as)
                 .then((response) => response.data)
        ],
        resolve: {
          preview_as: () => @preview_as
        }
      });
      modalInstance.result.then((email) => @preview_as_email = email; @refreshPreviewUrl())

    error: (message) -> window.alert(message)
    success: (message) -> window.alert(message)
    serverError: => @error('Server error occurred. Unable to save data.')

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()
