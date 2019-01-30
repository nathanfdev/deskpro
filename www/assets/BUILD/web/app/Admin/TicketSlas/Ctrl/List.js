define [
  'Admin/Main/Ctrl/Base'
], (
  Admin_Ctrl_Base
) ->
  class Admin_TicketSlas_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketSlas_Ctrl_List'
    @CTRL_AS = 'ListCtrl'
    @DEPS = ['$state', '$stateParams', 'DataService']

    init: ->
      @list = []
      @slaData = @DataService.get('TicketSlas')

    initialLoad: ->
      promise = @slaData.loadList()
      promise.then( (list) =>
        @list = list
      )

      return promise

    ###
    # Show the delete dlg
    ###
    startDelete: (sla_id) ->

      sla = null
      for v in @list
        if v.id == sla_id
          sla = v
          break

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketSlas/delete-modal.html'),
        controller: ['$scope', '$modalInstance', ($scope, $modalInstance) ->
          $scope.confirm = ->
            $modalInstance.close();

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ]
      });

      inst.result.then( =>
        @slaData.deleteSlaById(sla.id).then(=>
          if @$state.current.name == 'tickets.slas.edit' and parseInt(@$state.params.id) == sla.id
            @$state.go('tickets.slas')
        )
      )

  Admin_TicketSlas_Ctrl_List.EXPORT_CTRL()