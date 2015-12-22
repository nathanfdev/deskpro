define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout']

    init: ->
      @open_panels = []
      @values = {}

    save: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/edit-theme-set/variable-values',
        data: @values
      })
      request.then(
        () -> console.log('Saved'),
        () -> console.log('Error')
      )

    commit: () ->
      @$http.get('/portal/api/style/edit-theme-set/commit').then(
        () -> console.log('Committed'),
        () -> console.log('Error')
      );

    discard: () ->
      @$http.get('/portal/api/style/edit-theme-set/discard').then(
        () -> console.log('Committed'),
        () -> console.log('Error')
      );

    initialLoad: ->
      @$http.get('/portal/api/style/variable-groups').success((data) => @groups = data)
      @$http.get('/portal/api/style/variable-values').success(
        (values) =>
          angular.extend(@values, values)
      )

    togglePanel: (name) ->
      if name in @open_panels
        @open_panels = @open_panels.filter (e) -> e != name
      else
        @open_panels.push name

    isOpen: (name) ->
      name in @open_panels

    label: (sys_name) ->
      sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()