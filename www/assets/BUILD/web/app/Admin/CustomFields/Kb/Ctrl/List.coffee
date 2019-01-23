define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_CustomFields_Kb_Ctrl_List extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_CustomFields_Kb_Ctrl_List'
    @DEPS = []
    @CTRL_AS = 'ListCtrl'

    init: ->
      @$scope.fields = []

    initialLoad: ->
      @DataService.get('KbFields').loadList().then((list) => @$scope.fields = list);

  Admin_CustomFields_Kb_Ctrl_List.EXPORT_CTRL()