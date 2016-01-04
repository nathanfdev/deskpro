define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout']

    init: ->
      @open_panels = []
      @values = {}
      @recompiling = false
      @advanced = {header: '', footer: '', scss: '', javascript: ''}
      @advanced_tab = 'header'
      @is_advanced_expanded = false
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
          () => @loadAdvancedEdits(() => @loadValues(() => @success('Changes were discarded'); @recompiling = false)),
          () => @serverError(); @recompiling = false
        );

    initialLoad: ->
      @$http.get('/portal/api/style/variable-groups').success((data) => @groups = data)
      @loadValues()
      @loadAdvancedEdits()

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

    openAdvancedTab: (tab) => @advanced_tab = tab
    isAdvancedTab: (tab) => @advanced_tab == tab

    isAdvancedExpanded: () => @is_advanced_expanded
    collapseAdvanced: () => @is_advanced_expanded = false
    expandAdvanced: () => @is_advanced_expanded = true

    error: (message) -> window.alert(message)
    success: (message) -> window.alert(message)
    serverError: -> @error('Server error occurred. Unable to save data.')

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()