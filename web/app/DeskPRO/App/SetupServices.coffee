define [
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings',
  'DeskPRO/Main/Service/AppState',
  'DeskPRO/Main/Service/DpApi',
  'DeskPRO/Main/Service/Growl',
  'DeskPRO/Main/Service/InhelpState',
], (
  Util,
  Strings,
  DeskPRO_Main_Service_AppState,
  DeskPRO_Main_Service_DpApi,
  DeskPRO_Main_Service_Growl,
  DeskPRO_Main_Service_InhelpState,
) ->
  return (Module) ->
    Module.service('AppState', ['$rootScope', '$state', ($rootScope, $state) ->
      return new DeskPRO_Main_Service_AppState($rootScope, $state)
    ])

    Module.service('Api', ['$http', ($http) ->
      return new DeskPRO_Main_Service_DpApi(
        $http,
        window.DP_BASE_API_URL,
        window.DP_API_TOKEN
      )
    ])

    Module.service('InhelpState', ['Api', (Api) ->
      return new DeskPRO_Main_Service_InhelpState(Api)
    ])

    Module.service('Growl', [ ->
      return new DeskPRO_Main_Service_Growl()
    ])

    Module.filter('escape_url', [ ->
      return (text) ->
        return encodeURIComponent(text)
    ])

    Module.filter('murmurhash', [ ->
      return (text) ->
        return Strings.murmurhash3(text)
    ])

    Module.filter('filesize_display', [ ->
      return (bytes, precision = 2) ->
        if not bytes
          bytes = 0
        if not Util.isNumber(bytes)
          if Util.isString(bytes)
            bytes = parseFloat(bytes)
            if not bytes
              bytes = 0
          else
            bytes = 0

        if bytes == 0
          return '0 B'

        symbols = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

        exp = Math.floor(Math.log(bytes) / Math.log(1024))
        result = bytes / Math.pow(1024, Math.floor(exp))
        result = result.toFixed(precision)

        if symbols[exp] then result += ' ' + symbols[exp]

        return result
    ])

    Module.filter('fulltime', ['$filter', ($filter) ->
      return (timestamp) ->
        return $filter('date')(timestamp, 'EEEE, MMMM d, y h:mm a')
    ])

    # Add logging to digest loop
    Module.config(['$provide', ($provide) ->

      # This var is used in browser tests so we can
      # properly wait for a page to be finished rendering
      window.DP_DIGEST_RUNNING = false
      startRunning = ->
        window.DP_DIGEST_RUNNING = true
      stopRunning = ->
        window.DP_DIGEST_RUNNING = false

      $provide.decorator('$rootScope', ['dpInterfaceTimer', '$delegate', (dpInterfaceTimer, $delegate) ->
        origDigest = $delegate.$digest
        $delegate.$digest = ->
          startRunning()
          dpInterfaceTimer.startDigest()
          ret = origDigest.apply($delegate, arguments)
          dpInterfaceTimer.endDigest()
          stopRunning()
          return ret

        return $delegate
      ])
    ])

    # Add fcall() to $q service (like Kris Kowal's Q: https://github.com/kriskowal/q)
    # Add isPromise
    Module.config(['$provide', ($provide) ->
      $provide.decorator('$q', ['$delegate', ($delegate) ->
        $delegate.fcall = (fn) ->
          d = $delegate.defer()
          d.resolve(fn())
          return d.promise

        $delegate.isPromise = (val) ->
          return val.then?

        return $delegate
      ])
    ])

    Module.config(['$provide', ($provide) ->
      $provide.decorator('$state', ['$delegate', '$stateParams', ($delegate, $stateParams) ->
        ###
        # Checks to see if a certain state is currently active
        #
        # @param {String} stateId The state to check. If it begins with a leading dot, we'll cehck
        #                         if the id exists anywhere in the current state. E.g., shorter to write '.create' than 'x.y.z.create'
        # @param {Object} stateParams If provided, then the params specified must also match
        ###
        $delegate.isStateActive = (stateId, stateParams = null) ->
          if not $delegate.current then return false

          if stateParams
            if stateId.charAt(0) == '.'
              if $delegate.current.name.indexOf(stateId) == -1
                return false
            else
              if $delegate.current.name != stateId
                return false

            if not $delegate.$current.params then return false
            for own k, v of stateParams
              if not $stateParams[k]? or $stateParams[k] != v
                return false

            return true

          else
            if stateId.charAt(0) == '.'
              return $delegate.current.name.indexOf(stateId) != -1
            else
              return $delegate.current.name == stateId

        return $delegate
      ])
    ])