define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Apps_Ctrl_ImportersList extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Apps_Ctrl_ImportersList'
    @CTRL_AS = 'ImportersListCtrl'
    @DEPS = []

    init:        ->


    initialLoad: ->
      @Api.sendGet('/server/importers').then (res) =>
        @$scope.list = res.data


  Admin_Apps_Ctrl_ImportersList.EXPORT_CTRL()