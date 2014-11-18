define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_TicketDeps_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_TicketDeps_Ctrl_List'
    @CTRL_AS = 'ListCtrl'

    init: ->
      @depData = @DataService.get('TicketDeps')

      @sortedListOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          $list = data.item.closest('ul')

          order = []
          $list.find('li').each(->
            order.push(parseInt($(this).data('id')))
          )

          @depData.saveDisplayOrders(order)
          @pingElement('display_orders')
      }

    ###
    # Loads the dep list
    ###
    initialLoad: ->
      promise = @depData.loadList().then( (list) =>
        @depList = list
        @deps = @depData.listModels
      )

      return promise

    ###
    # Get the move dep list for use in the delete/move dlg
      # @return {Array}
    ###
    getMoveDepList: (for_dep) ->
      dep_move_list = []
      for dep in @departments
        if for_dep.id != dep.id
          if not dep._child_ids
            dep_move_list.push(dep)

      return dep_move_list

    ###*
    # Show the delete dlg
    ###

    startDelete: (for_dep_id) ->

      dep = @depData.findListModelById(for_dep_id)

      if dep.children?.length
        @showAlert("You cannot delete a department with sub-departments. Move or delete the sub-departments first.")
        return

      move_deps_list = @depData.getLeafOptionsArray(dep.id)

      if not move_deps_list.length
        @showAlert('@no_delete_last');
        return

      inst = @$modal.open({
        templateUrl: @getTemplatePath('TicketDeps/delete-modal.html'),
        controller: ['$scope', '$modalInstance', 'move_deps_list', ($scope, $modalInstance, move_deps_list) ->
          $scope.move_deps_list = move_deps_list
          $scope.selected = {
            move_to_id: move_deps_list[0].id
          }

          $scope.confirm = ->
            $modalInstance.close($scope.selected.move_to_id);

          $scope.dismiss = ->
            $modalInstance.dismiss();
        ],
        resolve: {
          move_deps_list: =>
            return move_deps_list
        }
      });

      inst.result.then( (move_to) =>
        @deleteDepartment(dep, move_to)
      )

    ###
    # Actually do the delete
    ###

    deleteDepartment: (for_dep, move_to) ->

      @depData.deleteDepartmentById(for_dep.id, move_to).success( =>

        if @$state.current.name == 'tickets.ticket_deps.edit' and parseInt(@$state.params.id) == for_dep.id
          @skipDirtyState()
          @$state.go('tickets.ticket_deps')

      ).error( (info, code) =>

        @applyErrorResponseToView(info)
      )

  Admin_TicketDeps_Ctrl_List.EXPORT_CTRL()