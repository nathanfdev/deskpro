define ['DeskPRO/Util/Numbers', 'perfect-scrollbar'], (Numbers) ->
  DeskPRO_Directive_DpScrollable = [ '$timeout', '$interval', ($timeout, $interval) ->
    return {
      restrict: 'A',
      link: (scope, $el, attrs) ->

        hasSetup = false
        updateInterval = null

        getOpts = ->
          opt = {}
          for i in [
            'wheelSpeed', 'wheelPropagation', 'minScrollbarLength', 'useBothWheelAxes',
            'useKeyboard', 'suppressScrollX', 'suppressScrollY', 'scrollXMarginOffset',
            'scrollYMarginOffset', 'includePadding'
          ]
            if attrs[i]?
              opt[i] = attrs[i]
              if Numbers.isNumeric(opt[i])
                opt[i] = Numbers.parseNumber(attrs[i])
              else if opt[i] == "1" or opt[i] == "on" or opt[i] == "yes"
                opt[i] = true
              else if opt[i] == "0" or opt[i] == "off" or opt[i] == "no"
                opt[i] = false

          return opt

        update = ->
          return if not hasSetup
          $el.perfectScrollbar('update');

        setup = ->
          return if hasSetup
          $el.perfectScrollbar(getOpts())
          if attrs.autoUpdate && attrs.autoUpdate != "0" && attrs.autoUpdate != "false" && attrs.autoUpdate != "no"
            updateInterval = $interval(update, 350)
          hasSetup = true

        $timeout(setup)

        scope.$on('$destroy', ->
          if hasSetup
            $el.perfectScrollbar('destroy')
            if updateInterval
              $interval.cancel(updateInterval)
        )
    }
  ]

  return DeskPRO_Directive_DpScrollable