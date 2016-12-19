define [
  'Admin/Main/Ctrl/Base',
  'Admin/Usersources/Helper/UsersourceTypeDecider'
], (
  Admin_Ctrl_Base,
  Admin_Usersources_Helper_UsersourceTypeDecider
) ->
  class Admin_Usersources_Ctrl_New extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Usersources_Ctrl_New'
    @CTRL_AS = 'NewCtrl'
    @DEPS = ['$state']

    init: ->
      @usersourceType = Admin_Usersources_Helper_UsersourceTypeDecider.decide(@$state);
      @$scope.install_url = if @usersourceType == 'user' then 'crm.usersources.install' else 'agents.usersources.install'

    initialLoad: ->
      url = '/usersources/available/app-packages/' + @usersourceType

      @Api.sendGet(url).then((res) =>
        @packages = res.data
      )

  Admin_Usersources_Ctrl_New.EXPORT_CTRL()