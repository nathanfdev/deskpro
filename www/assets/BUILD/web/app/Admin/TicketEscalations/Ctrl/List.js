define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketEscalations_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketEscalations_Ctrl_List'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state', '$stateParams', 'DataService']

    init: ->
      @list = []
      @list_satisfaction = []
      @list_statuses = []
      @escData = @DataService.get('TicketEscalations')



    initialLoad: ->
      @loadList().then((list) =>
        if @$state.current.name == 'tickets.ticket_escalations'
          if @list[0]
            @$state.go('tickets.ticket_escalations.edit', {id: @list[0].id})
          else
            @$state.go('tickets.ticket_escalations.create')
      )



    loadList:    ->
      d = @$q.defer()
      @escData.loadList().then (list) =>
        @list.length = 0
        @list_satisfaction.length = 0
        @list_statuses.length = 0
        return d.resolve([]) if !list

        list.map (item) =>
          if 'satisfaction' == item.sys_type
            @list_satisfaction.push item
          else if 'statuses' == item.sys_type
            @list_statuses.push item
          else
            @list.push item
        d.resolve list
      d.promise



    ###
    # Show the delete dlg
    ###
    startDelete: (esc) ->
      return if esc?.sys_name?

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketEscalations/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @escData.deleteEscalationById(esc.id).then(=>
          @list = @list.filter (item) -> esc.id != item.id
          @list_satisfaction = @list_satisfaction.filter (item) -> esc.id != item.id
          @list_statuses = @list_statuses.filter (item) -> esc.id != item.id
          if @$state.current.name == 'tickets.ticket_escalations.edit' and parseInt(@$state.params.id) == esc.id
            @$state.go('tickets.ticket_escalations')
        )
      )

    ###
    # Update the enabled state of a esc
    ###
    updateEscEnabledState: (esc) ->
      return @escData.saveEnabledStateById(esc.id, esc.is_enabled)

  Admin_TicketEscalations_Ctrl_List.EXPORT_CTRL()