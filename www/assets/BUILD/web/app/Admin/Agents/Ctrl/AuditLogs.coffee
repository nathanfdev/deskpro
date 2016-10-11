define ['Admin/Main/Ctrl/Base', '../../../../bower_components/moment/moment'], (Admin_Ctrl_Base, moment) ->
  class Admin_AgentAuditLogs_Ctrl_AuditLogs extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_AgentAuditLogs_Ctrl_AuditLogs'
    @CTRL_AS   = 'AuditLogs'
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
      @purge = 'day';
      @pagination = {
        total: 0
        count: 0
        per_page: 50
        current_page: 1
        virtual_current_page: 1
        total_pages: 1
        page_nums: [1]
        plain: false
      }

    initialLoad: ->
      @updateFilter()

    newSearch: ->
      @pagination.current_page = @pagination.virtual_current_page = 1;
      @updateFilter()

    updateFilter: ->
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
          @pagination.virtual_current_page = @pagination.current_page
          page_nums = []
          if(@pagination.total_pages > 250)
            @pagination.plain = true
            @is_loading = false
            return

          for i in [0...@pagination.total_pages]
            page_nums.push(i + 1)
          @pagination.page_nums = page_nums
          @is_loading = false
      )

    purgeLogs: ->
      @is_loading = true
      inst = @$modal.open({
        templateUrl: @getTemplatePath('Agents/audit-logs-delete-modal.html'),
        controller: ['$scope', '$modalInstance',  ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close()

          $scope.dismiss = ->
            $modalInstance.dismiss()
        ]
      });

      inst.result.then( () =>
        @Api2.sendPostJson('/audit_logs/purge', {period: @purge}).then(
          () =>
            @clearFilter()
            @is_loading = false
        )
      ).catch ( () => @is_loading = false );


    goPrevPage: ->
      @pagination.current_page = @pagination.virtual_current_page = parseInt(@pagination.current_page) - 1
      if (@pagination.current_page < 0)
        @pagination.current_page = @pagination.virtual_current_page = 0
      @updateFilter()

    goNextPage: ->
      @pagination.current_page = @pagination.virtual_current_page = parseInt(@pagination.current_page) + 1
      if (@pagination.current_page > @pagination.total_pages)
        @pagination.current_page = @pagination.virtual_current_page = @pagination.total_pages
      @updateFilter()

    goCurrentPage: () ->
      @pagination.current_page = parseInt(@pagination.virtual_current_page)
      if (@pagination.current_page > @pagination.total_pages)
        @pagination.current_page = @pagination.virtual_current_page = @pagination.total_pages
      else if(@pagination.current_page < 0)
        @pagination.current_page = @pagination.virtual_current_page = 0
      @updateFilter()
      return false

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
      @pagination.virtual_current_page = 1
      @updateFilter()

  Admin_AgentAuditLogs_Ctrl_AuditLogs.EXPORT_CTRL()
