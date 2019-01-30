define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerIncidents_Ctrl_View extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerIncidents_Ctrl_View'
    @CTRL_AS   = 'View'
    @DEPS      = ['Api2', '$sce']

    init: ->
      @incident = {}
      @instructions_html = ''

    initialLoad: ->
      @Api2.sendGet('/system/incidents/' + @$stateParams.id).then(
        ({data}) => @incident = data.data.incident; @instructions_html = @$sce.trustAsHtml(data.data.instructions_html)
      )

  Admin_ServerIncidents_Ctrl_View.EXPORT_CTRL()
