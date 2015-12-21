define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_PortalEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_PortalEditor'
    @CTRL_AS = 'Portal'
    @DEPS    = ['$http', '$scope', '$timeout']

    init: ->
      @open_panels = []
      @values = {}
      @$scope.$watch((() => @values), @saveValuesDelayed, true);


    saveValuesDelayed: (newValues, oldValues) =>
      if not angular.equals(newValues, oldValues)
        @$timeout.cancel(@saveValuesTimeout)
        @saveValuesTimeout = @$timeout(@saveValues, 1500)

    saveValues: () =>
      request = @$http({
        method: 'PUT',
        url: '/portal/api/style/variables',
        data: @values
      })
      request.then(
        () -> console.log('Saved'),
        () -> console.log('Error')
      )

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
      sys_name.replace(/[\-_]/g, ' ').replace(/^(.)|\s(.)/g, (v) -> v.toUpperCase())

  Admin_Portal_Ctrl_PortalEditor.EXPORT_CTRL()