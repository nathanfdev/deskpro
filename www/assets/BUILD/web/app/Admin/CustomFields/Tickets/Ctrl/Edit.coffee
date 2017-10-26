define [
  'Admin/CustomFields/Base/Ctrl/Edit',
], (Admin_CustomFields_Base_Ctrl_Edit) ->
  class Admin_CustomFields_Tickets_Ctrl_Edit extends Admin_CustomFields_Base_Ctrl_Edit
    @CTRL_ID = 'Admin_CustomFields_Tickets_Ctrl_Edit'
    @CTRL_AS = 'EditCtrl'
    @DEPS = []

    init: ->
      super
      @showLayouts = false
      @referencedByApp = { status: false, appName: "", appUrl: "#" }
      return

    initialLoadExtra: ->
      return @Api.sendGet('/ticket_layouts/fields/ticket_field_' + (@field_id || '__undefined__')).success((data) =>
        @user_layouts = data.user_layouts
        @agent_layouts = data.agent_layouts

        if !@field_id
          for own l of @user_layouts
            @user_layouts[l].enabled = true
          for own l of @agent_layouts
            @agent_layouts[l].enabled = true

      )

    postLoad: (fieldData) ->
      referencedByAppFilter = (reference) ->
        reference.entity == 'app'

      referencingApps = if fieldData.referencedBy instanceof Array then fieldData.referencedBy.filter referencedByAppFilter else []

      isReferenced = referencingApps.length != 0
      @showLayouts = !isReferenced
      @showFieldType = !isReferenced
      @showEnabled = !isReferenced
      @showAgentOnly = !isReferenced

      @referencedByApp  = {
        status: isReferenced,
        appName: if isReferenced then referencingApps[0].appName else '',
        appUrl: if isReferenced then  'apps/apps/v2_' + referencingApps[0].appId else '#'
      }

      return

    startDelete: ->
      if (@referencedByApp.status)
        @showAlert('This field can not be deleted until the app has been deleted')
        return
      super

    postSave: ->
      postData = {
        enable_user_layouts:  [],
        enable_agent_layouts: []
      }

      if @form.is_enabled
        if not @form.is_agent_field
          for own k,l of @user_layouts
            if l.enabled
              postData.enable_user_layouts.push(if l.department then l.department.id else 0)
        for own k,l of @agent_layouts
          if l.enabled
            postData.enable_agent_layouts.push(if l.department then l.department.id else 0)

      return @Api.sendPostJson('/ticket_layouts/fields/ticket_field_' + @field_id, postData)

    getDataService: ->
      return @DataService.get('TicketFields')

    getBaseRouteName: ->
      return "tickets.fields"

    type: ->
      'tickets'

  Admin_CustomFields_Tickets_Ctrl_Edit.EXPORT_CTRL()
