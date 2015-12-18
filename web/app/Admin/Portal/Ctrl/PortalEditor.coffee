define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope']

    init: ->
      @open_panels = []
      @values = {}
      @$scope.$watch((() => @values), @saveValues, true);

    saveValues: (newValues) ->
      console.log('Saving', newValues);

    commitChanges: () ->
      console.log('Committing', @values)

    initialLoad: ->
      @$http.get('/web/sassdoc/vars.json').success((data) => @groups = data)

    togglePanel: (name) ->
      if name in @open_panels
        @open_panels = @open_panels.filter (e) -> e != name
      else
        @open_panels.push name

    isOpen: (name) ->
      name in @open_panels

    label: (sys_name) ->
      sys_name.replace(/\_/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()