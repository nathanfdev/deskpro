define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
  class Admin_Settings_Ctrl_ResetDemo extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Settings_Ctrl_ResetDemo'
    @CTRL_AS   = 'Ctrl'
    @DEPS      = []

    init: ->

    initialLoad: ->

    save: ->
      return if $scope.form_props.$invalid


  Admin_Settings_Ctrl_ResetDemo.EXPORT_CTRL()