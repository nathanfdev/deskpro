define ['DeskPRO/Util/Arrays'], (Arrays) ->
  class DashboardPermissionsService
    constructor: (Api, $q) ->
      @Api = Api
      @$q = $q
      @storage = []

    getPermissions: (dashboard) ->
      deferred = @$q.defer()

      index = Arrays.findIndex @storage,
        (v) ->
          if v? and v.id is dashboard.id
            return true

      if @storage[index]?
        deferred.resolve @storage[index]
      else
        @getDashboardPermissions(dashboard).then (response) =>
          permissions = response.data
          @storage.push permissions
          deferred.resolve permissions
      deferred.promise

    getIndexById: (storage, id) ->
      index = -1
      index = Arrays.findIndex storage,
        (v) ->
          if v? and v.id is id
            return true
      return index

    getDashboardPermissions: (dashboard) ->
      if dashboard?
        @Api.sendGet("/dashboards/permissions/#{dashboard.id}")
      else
        @Api.sendGet("/dashboards/permissions")

    savePermissions: (agent, dashboard) ->
      @Api.sendPost("/dashboards/permissions/#{dashboard.id}", {agent_id: agent.id, permissions: agent.permissions})


