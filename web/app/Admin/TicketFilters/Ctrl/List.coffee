define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketFilters_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketFilters_Ctrl_List'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state', '$stateParams', 'DataService']

    init: ->
      @list = []
      @filterData = @DataService.get('TicketFilters')
      @$scope.display_filter = {
        type:  "all",
        agent: "0",
        team:  "0"
      }

      @$scope.$watch('display_filter', =>
        @updateFilterList()
      , true)

      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          orders = []
          $list.find('li').each(->
            id = parseInt($(this).data('id'))
            console.log(id)

            if id
              orders.push(id)
          )

          @filterData.saveDisplayOrder(orders).then( =>
            @pingElement('display_orders')
          )
      }

    initialLoad: ->
      promise = @filterData.loadList()
      promise.then( (list) =>

        @list = list

        if @$state.current.name == 'tickets.ticket_filters'
          if @list[0]
            @$state.go('tickets.ticket_filters.edit', { id: @list[0].id })
          else
            @$state.go('tickets.ticket_filters.create')
      )

      data_promise = @Api.sendDataGet({
        agents: '/agents',
        teams: '/agent_teams'
      }).then( (res) =>
        @agents = res.data.agents.agents
        @teams = res.data.teams.agent_teams

        if not @teams[0]
          @teams = null
      )

      bothPromise = @$q.all([promise, data_promise])
      bothPromise.then(=>
        @updateFilterList()
      )

      return bothPromise;

    updateFilterList: ->
      filterList = []
      display_filter = @$scope.display_filter

      if display_filter.type == 'all'
        filterList = @list
      else
        if display_filter.type == 'global'
          filterList = @list.filter((x) -> x.is_global)
        else if display_filter.type == 'agent'
          agentId = parseInt(display_filter.agent)
          if agentId
            filterList = @list.filter((x) -> !x.is_global && x.person && x.person.id == agentId)
          else
            filterList = @list.filter((x) -> !x.is_global && x.person)
        else if display_filter.type == 'team'
          teamId = parseInt(display_filter.team)
          if teamId
            filterList = @list.filter((x) -> !x.is_global && x.agent_team && x.agent_team.id == teamId)
          else
            filterList = @list.filter((x) -> !x.is_global && x.agent_team)

      @$scope.filterList = filterList

    ###
    # Show the delete dlg
    ###
    startDelete: (filter_id) ->

      filter = null
      for v in @list
        if v.id == filter_id
          filter = v
          break

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketFilters/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @filterData.deleteFilterId(filter.id).then(=>
          if @$state.current.name == 'tickets.ticket_filters.edit' and parseInt(@$state.params.id) == filter.id
            @$state.go('tickets.ticket_filters')
        )
      )

  Admin_TicketFilters_Ctrl_List.EXPORT_CTRL()