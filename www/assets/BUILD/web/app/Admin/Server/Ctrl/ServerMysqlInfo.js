define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo extends Admin_Ctrl_Base

    @CTRL_ID   = 'Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo'
    @CTRL_AS   = 'ServerMysqlInfo'
    @DEPS      = []

    init: ->
      @$scope.is_loading_schemadiff = true


    initialLoad: ->
      @Api.sendGet('/server_mysql_info/schema-diff').then( (res) =>
        @$scope.schema_diff = res.data.mysql_schema_diff
        @$scope.is_loading_schemadiff = false
      )

      return @Api.sendGet('/server_mysql_info').then( (res) =>
        @$scope.server_mysql_info = res.data.server_mysql_info
      )

  Admin_ServerMysqlInfo_Ctrl_ServerMysqlInfo.EXPORT_CTRL()