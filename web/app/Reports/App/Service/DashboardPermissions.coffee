define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardPermissionsService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @storage = {}

    getPermissions: (dashboard) ->
      deferred = @$q.defer()
      if @storage[dashboard]?
        deferred.resolve @storage[dashboard]
      else
        @getDashboardPermissions(dashboard).then (response) =>
          permissions = response.data
          @storage[dashboard] = permissions
          deferred.resolve(@storage[dashboard])
      deferred.promise

    getDashboardPermissions: (dashboard) ->
      @Api.sendGet("/dashboards/permissions/#{dashboard.id}")

    savePermissions: (agent, dashboard) ->
      @Api.sendPost("/dashboards/permissions/#{dashboard.id}", {agent_id: agent.id, permissions: agent.permissions})


