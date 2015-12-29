define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout']

    init: ->
      @open_panels = []
      @values = {}
      @recompiling = false
      @refreshPreviewUrl()

    save: () =>
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
          () => @loadValues(() => @success('Changes were discarded'); @recompiling = false),
          () => @serverError(); @recompiling = false
        );

    initialLoad: ->
      @$http.get('/portal/api/style/variable-groups').success((data) => @groups = data)
      @loadValues()

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
      @$http.get('/portal/api/style/variable-values').success(
        (values) =>
          if success
            success()
          angular.extend(@values, values)
      )

    error: (message) -> window.alert(message)
    success: (message) -> window.alert(message)
    serverError: -> @error('Server error occurred. Unable to save data.')

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()