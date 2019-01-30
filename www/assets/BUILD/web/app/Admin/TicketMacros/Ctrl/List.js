define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketMacros_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketMacros_Ctrl_List'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state', '$stateParams', 'DataService']

    init: ->
      @list = []
      @macroData = @DataService.get('TicketMacros')
      @$scope.display_filter = {
        type:  "all",
        agent: "0"
      }

      @$scope.$watch('display_filter', =>
        @updateFilterList()
      , true)

    initialLoad: ->
      promise = @macroData.loadList()
      promise.then( (list) =>
        @list = list
      )

      data_promise = @Api.sendDataGet({
        agents: '/agents'
      }).then( (res) =>
        @agents = res.data.agents.agents
      )

      bothPromise = @$q.all([promise, data_promise])
      bothPromise.then(=>
        @updateFilterList()
      )

      return bothPromise

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
        else if display_filter.type == 'department'
          filterList = @list.filter((x) -> !x.is_global && x.department)

      @$scope.filterList = filterList

    ###
    # Show the delete dlg
    ###
    startDelete: (macro) ->
      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketMacros/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @macroData.deleteMacroById(macro.id).then(=>
          if @$state.current.name == 'tickets.ticket_macros.edit' and parseInt(@$state.params.id) == macro.id
            @$state.go('tickets.ticket_macros')
        )
      )

  Admin_TicketMacros_Ctrl_List.EXPORT_CTRL()