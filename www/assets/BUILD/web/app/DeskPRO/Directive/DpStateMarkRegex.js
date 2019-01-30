define [
  'DeskPRO/Util/Util',
  'DeskPRO/Util/Strings'
], (Util, Strings) ->
  ###
    # Description
    # -----------
    #
    # Like DpStateMark except the attr is expected to be a regex pattern.
    ###
  DeskPRO_Directive_DpStateMarkRegex = ['$state', ($state) ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->
        myStateId   = attrs.dpStateMarkRegex
        currentStateVars = null

        myStateIdRe1 = new RegExp(myStateId)

        # This sets the active state immediately on click
        # which makes the UI feel faster
        element.on('click', ->
          element.closest('.dp-layout-appnav').find('.state-on').removeClass('state-on active')
          element.closest('.dp-layout-list-listpane').find('.state-on').removeClass('state-on active')
          element.addClass('state-on active')
        )

        updateMarker = ->
          checkStateId = $state.current.name
          checkStateId2 = null

          if $state.current.data?.stateMarkId
            checkStateId2 = $state.current.data.stateMarkId

          isOn = false

          for currentStateId in [checkStateId, checkStateId2]
            if isOn or not currentStateId then continue

            if currentStateVars
              for v in currentStateVars
                if $state.params[v]?
                    currentStateId += '.' + $state.params[v]
                else
                  currentStateId += '.0'
            else
              if $state.params['id']?
                currentStateId += '.' + $state.params['id']

            if currentStateId.match(myStateIdRe1)
              isOn = true

          if isOn
            element.addClass('state-on active')
            if element.closest('[dp-nav-subnav]')
              element.closest('[dp-nav-subnav]').show().closest('li').addClass('sublist-open')
          else
            element.removeClass('state-on active')

        scope.$on('$stateChangeSuccess', ->
          updateMarker()
        );

        updateMarker()
    }
  ]

  return DeskPRO_Directive_DpStateMarkRegex
