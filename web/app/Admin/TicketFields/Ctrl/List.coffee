define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketFields_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketFields_Ctrl_List'
    @CTRL_AS = 'TicketFieldsList'
    @DEPS    = []

    init: ->
      @ticket_fields = @DataService.get('TicketFields')
      @custom_fields = []
      @field_enabled = {}
      return

    initialLoad: ->
      promise = @ticket_fields.loadList()
      promise.then( (list) =>
        @custom_fields = list
        @field_enabled = @ticket_fields.field_enabled
      )
      return promise

    setFieldEnabled: (id, is_enabled) ->
      @field_enabled[id] = is_enabled

    saveLayoutData: (id, user_layouts, agent_layouts) ->
      postData = {
        enable_user_layouts: [],
        enable_agent_layouts: []
      }

      for own k,l of user_layouts
        if l.enabled
          postData.enable_user_layouts.push(if l.department then l.department.id else 0)
      for own k,l of agent_layouts
        if l.enabled
          postData.enable_agent_layouts.push(if l.department then l.department.id else 0)

      return @Api.sendPostJson('/ticket_layouts/fields/' + id, postData)

  Admin_TicketFields_Ctrl_List.EXPORT_CTRL()