define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout', '$upload']

    init: ->
      @open_panels = []
      @values = {}
      @recompiling = false
      @advanced = {header: '', footer: '', scss: '', javascript: ''}
      @advanced_tab = 'header'
      @is_advanced_expanded = false
      @asset_files = []
      @custom_logo = null
      @uploading_files_count = 0
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
      @$http.get('/portal/api/style/variable-groups').success((data) => @groups = data)
      @loadValues()
      @loadAdvancedEdits()
      @loadAssetFiles()
      @loadLogo()

    togglePanel: (name) ->
      if name in @open_panels
        @open_panels = @open_panels.filter (e) -> e != name
      else
        @open_panels.push name

    isOpen: (name) ->
      name in @open_panels

    label: (sys_name) ->
      sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

    refreshPreviewUrl: ->
      @preview_url = '/admin-preview?anti-cache=' + (new Date()).getTime()

    loadValues: (success) ->
      @$http.get('/portal/api/style/edit-theme-set/variable-values').success(
        (values) =>
          angular.extend(@values, values)
          if success
            success()
      )

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

    loadLogo: () ->
      @$http.get('/portal/api/style/edit-theme-set/logo').success((response) => @custom_logo = response.data.url)

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
      window.prompt('File URL:', file.url)

    delete: (file) =>
      if window.confirm('Are you sure you want to remove ' + file.name + '?')
        @$http.delete('/portal/api/style/edit-theme-set/assets/' + file.id).success(
          () => @asset_files = @asset_files.filter (f) -> f isnt file
        )

    deleteLogo: () =>
      @$http.delete('/portal/api/style/edit-theme-set/logo').success((response) => @custom_logo = null)

    openAdvancedTab: (tab) => @advanced_tab = tab
    isAdvancedTab: (tab) => @advanced_tab == tab

    isAdvancedExpanded: () => @is_advanced_expanded
    collapseAdvanced: () => @is_advanced_expanded = false
    expandAdvanced: () => @is_advanced_expanded = true

    error: (message) -> window.alert(message)
    success: (message) -> window.alert(message)
    serverError: -> @error('Server error occurred. Unable to save data.')

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()