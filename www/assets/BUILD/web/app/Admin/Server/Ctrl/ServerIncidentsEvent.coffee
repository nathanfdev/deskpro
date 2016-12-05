define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerIncidents_Ctrl_Event extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerIncidents_Ctrl_Event'
    @CTRL_AS   = 'View'
    @DEPS      = ['Api2', '$sce']

    init: ->
      @event = {}
      @instructions_html = ''

    initialLoad: ->
      @Api2.sendGet('/system/events/' + @$stateParams.id).then(
        ({data}) => @event = data.data
      )

  Admin_ServerIncidents_Ctrl_Event.EXPORT_CTRL()
