define ['Admin/Main/Ctrl/Base', 'moment'], (Admin_Ctrl_Base, moment) ->
  class Admin_ServerAuditLogs_Ctrl_ServerAuditLogs extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_ServerAuditLogs_Ctrl_ServerAuditLogs'
    @CTRL_AS   = 'ServerAuditLogs'
    @DEPS      = ['Api2']

    init: ->
      @filters = {
        performer_id: ''
        performer_name: ''
        date_created_to: null
        date_created_from: null
        object_type: ''
        object_id: ''
        object_name: ''
        action: ''
        api_key: ''
      }
      @logs = []
      @pagination = {
        total: 0
        count: 0
        per_page: 20
        current_page: 1
        total_pages: 1
        page_nums: [1]
      }

    initialLoad: ->
      @makeQuery()

      @$scope.$watch('ServerAuditLogs.pagination.current_page', (newVal, oldVal) => if parseInt(newVal) != parseInt(oldVal) then @updateFilter())

    makeQuery: ->
      @is_loading = true
      params = {}
      for key in Object.keys(@filters)
        if @filters[key] then params[key] = @filters[key]
      params.count = @pagination.per_page
      params.page = @pagination.current_page

      if(params.date_created_from)
        params.date_created_from = moment(params.date_created_from).format("YYYY-MM-DD")

      if(params.date_created_to)
        params.date_created_to = moment(params.date_created_to).format("YYYY-MM-DD")

      @Api2.sendGet('/audit_logs', params).then(
        (response) =>
          @logs = response.data.data
          @pagination = response.data.meta.pagination
          page_nums = []
          for i in [0...@pagination.total_pages]
            page_nums.push(i + 1)
          @pagination.page_nums = page_nums
          @is_loading = false
      )

    goPrevPage: ->
      @pagination.current_page = parseInt(@pagination.current_page) - 1
      if (@pagination.current_page < 0)
        @pagination.current_page = 0

    goNextPage: ->
      @pagination.current_page = parseInt(@pagination.current_page) + 1
      if (@pagination.current_page > @pagination.total_pages)
        @pagination.current_page = @pagination.total_pages

    updateFilter: ->
      @makeQuery()

    clearFilter: ->
      @filters = {
        performer_id: ''
        performer_name: ''
        date_created_to: null
        date_created_from: null
        object_type: ''
        object_id: ''
        object_name: ''
        action: ''
        api_key: ''
      }
      @pagination.current_page = 1
      @makeQuery()

  Admin_ServerAuditLogs_Ctrl_ServerAuditLogs.EXPORT_CTRL()
