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
      @escData = @DataService.get('TicketEscalations')


    initialLoad: ->
      promise = @escData.loadList()
      promise.then( (list) =>
        return if !list

        list.map (item) =>
          if 'satisfaction' == item.sys_name
            @list_satisfaction.push item
          else
            @list.push item

        if @$state.current.name == 'tickets.ticket_escalations'
          if @list[0]
            @$state.go('tickets.ticket_escalations.edit', { id: @list[0].id })
          else
            @$state.go('tickets.ticket_escalations.create')
      )

      return promise


    ###
    # Show the delete dlg
    ###
    startDelete: (esc_id) ->

      esc = null
      for v in @list
        if v.id == esc
          esc = v
          break

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
        @escData.deleteEscalationById(esc_id).then(=>
          if @$state.current.name == 'tickets.ticket_escalations.edit' and parseInt(@$state.params.id) == esc_id
            @$state.go('tickets.ticket_escalations')
        )
      )

    ###
    # Update the enabled state of a esc
    ###
    updateEscEnabledState: (esc) ->
      return @escData.saveEnabledStateById(esc.id, esc.is_enabled)

  Admin_TicketEscalations_Ctrl_List.EXPORT_CTRL()