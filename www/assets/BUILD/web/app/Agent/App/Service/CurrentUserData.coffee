define ->
  class Agent_App_Service_CurrentUserData
    @$inject = ['$http', '$q']
    constructor: (@$http, @$q) ->
      @userInfo    = null
      @userPerms   = null

      @httpPromise = null

    getUserInfo: ->
      return @promise if @promise

      d = @$q.deferred()

      if @userInfo
        d.resolve(@userInfo)
      else
        @loadData().then( ->
          d.resolve(@userInfo)
        , (data, status) ->
          d.reject(data, status)
        )

      return d.promise

    getUserPermissions: ->
      return @promise if @promise

      d = @$q.defer()

      if @userPerms
        d.resolve(@userPerms)
      else
        @loadData().then( ->
          d.resolve(@userPerms)
        , (data, status) ->
          d.reject(data, status)
        )

      return d.promise

    loadData: ->
      return @httpPromise if @httpPromise

      @httpPromise = @$http.get('DP_URL/agent/me/info.js').success( (data) ->
        @userInfo  = data.agent
        @userPerms = data.perms
      )

      return @httpPromise

  return Agent_App_Service_CurrentUserData;
