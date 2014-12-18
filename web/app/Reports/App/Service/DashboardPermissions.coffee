define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardPermissionsService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @storage = {}

    getPermissions: (dashboard) ->
      deferred = @$q.defer()
      if @storage[dashboard.id]?
        deferred.resolve @storage[dashboard.id]
      else
        @getDashboardPermissions(dashboard).then (permissions) ->
          @storage[dashboard.id] = permissions
          deferred.resolve(@sotrage[dashboard.id])
      deferred.promise

    getDashboardPermissions: (dashboard) ->
      @Api.sendGet("/dashboards/permissions/#{dashboard.id}")


