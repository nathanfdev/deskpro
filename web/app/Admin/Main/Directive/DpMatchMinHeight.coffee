define ['DeskPRO/Util/Functions'], (Functions) ->
  ###
    # Description
    # -----------
    #
    # This enables an element to "pair" itself with another element so that its height is always at least
    # as height as the other.
    #
    # Example
    # -------
    # <div dp-match-min-height watched-as="sidebar">I'm a big sidebar</div>
    # <div dp-match-min-height watch="sidebar">I will have my min-height set to the height of sidebar</div>
  ###
  Admin_Main_Directive_DpMatchMinHeight = [ '$timeout', '$interval', ($timeout, $interval) ->

    registry = {}

    return {
      restrict: 'A',
      link: (scope, element, attrs) ->

        regId = attrs.watchScopeId || scope.$id

        if attrs.watchedAs
          registry[regId + "_" + attrs.watchedAs] = element
          scope.$on('$destroy', -> registry[attrs.watchedAs] && delete registry[regId + "_" + attrs.watchedAs])
        else
          watchId = regId + "_" + attrs.watch
          resize = ->
            return if not registry[watchId]?
            h = registry[watchId].height()
            element.css('min-height', h).addClass('with-dp-min-height')

          resizeDebounce = Functions.debounce(resize, 100, true)

          interval = $interval( ->
            resize()
          , 600)

          $(window).on('resize', resizeDebounce)
          scope.$on('$destroy', ->
            $(window).off('resize', resizeDebounce)
            $interval.cancel(interval)
          )

          resize()
      }
  ]

  return Admin_Main_Directive_DpMatchMinHeight