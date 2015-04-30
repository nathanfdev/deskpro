define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Server_Ctrl_ImportersView extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Server_Ctrl_ImportersView'
    @CTRL_AS = 'ImportersViewCtrl'
    @DEPS = []

    init:        ->


    initialLoad: ->
      @Api.sendGet('/server/importers/' + @$stateParams.id).then (res) =>
        @$scope.importer = res.data


  Admin_Server_Ctrl_ImportersView.EXPORT_CTRL()