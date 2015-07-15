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
      @viewList = []
      @filterSetData = @DataService.get('TicketFilterSets')
      @filterViewData = @DataService.get('TicketFilterViews')
      @$scope.display_filter = {
        type:  "all",
        agent: "0",
        team:  "0"
      }
      @$scope.foobar = {
        addingNewFilterSet: false,
        new_filterset_name: ""
      }
      @$scope.addFilterSet = @addFilterSet

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

            if id
              orders.push(id)
          )

          @filterSetData.saveDisplayOrder(orders).then( =>
            @pingElement('display_orders')
          )
      }

    initialLoad: ->
      # Promises, promises...
      @filterSetData.loadList().then( (list) =>
        @list = list
        @updateFilterList()
      )
      @filterViewData.loadList().then( (list) =>
        @viewList = list
        @updateFilterViewList()
      )
      
      ###
      promises.push(@Api.sendDataGet({
        agents: '/agents',
        teams: '/agent_teams'
      }).then( (res) =>
        @agents = res.data.agents.agents
        @teams = res.data.teams.agent_teams

        if not @teams[0]
          @teams = null
      ))
      ###
      
    updateFilterList: ->
      ###
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
      ###

      @$scope.filterList = @list
    
    updateFilterViewList: =>
      @$scope.filterViewList = @viewList

    ###
    # Add a brand new filter set.
    ###
    addFilterSet: =>
      filterSet = @filterSetData.blank()
      filterSet.title = @$scope.foobar.new_filterset_name
      @filterSetData.saveTicketFilterSet(filterSet).then(
        (data) =>
          @$scope.filterList.push(data.data)
          @$scope.foobar.new_filterset_name = ""
          @$scope.foobar.addingNewFilterSet = false
          @$state.go('tickets.ticket_filters.edit', { id: data.data.id })
      ,
        (data) =>
          console.log "failure"
      )
        
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
        templateUrl: @getTemplatePath('TicketFilterSets/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @filterSetData.deleteFilterId(filter.id).then(=>
          if @$state.current.name == 'tickets.ticket_filter_sets.edit' and parseInt(@$state.params.id) == filter.id
            @$state.go('tickets.ticket_filter_sets')
        )
      )

  Admin_TicketFilters_Ctrl_List.EXPORT_CTRL()
