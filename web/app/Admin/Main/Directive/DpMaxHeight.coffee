define ['DeskPRO/Util/Functions'], (Functions) ->
  ###
    # Description
    # -----------
    #
    # This enables a calculated 'max-height' on an element based on the height of the screen.
    #
    # Example
    # -------
    # <div dp-max-height="40">...</div>
  ###
  Admin_Main_Directive_DpMaxHeight = [ '$timeout', '$interval', ($timeout, $interval) ->
    return {
      restrict: 'A',
      priority: -10,
      link: (scope, element, attrs) ->
        add = if attrs.dpHeightAdd then parseInt(attrs.dpHeightAdd || 0) else -100
        min = if attrs.dpMinHeight then parseInt(attrs.dpMinHeight || 0) else 300
        perc = parseInt(attrs.dpMaxHeight || 100)/100;

        element.addClass('with-dp-max-height');

        resize = ->
          top = element.offset().top + $('.dp-layout-appbody').scrollTop()
          winH = $(window).height()
          setH = (Math.ceil(winH * perc) - top) + add
          if setH < min then setH = min
          element.css('max-height', setH)

        resizeDebounce = Functions.debounce(resize, 100, true)

        interval = $interval( ->
          resize()
        , 500)

        $(window).on('resize', resizeDebounce)
        scope.$on('$destroy', ->
          $(window).off('resize', resizeDebounce)
          $interval.cancel(interval)
        )

        resize()
        $timeout(->
          resize()
          $timeout(
            -> resize()
            $timeout(
              -> resize()
            )
          )
        )
      }
  ]

  return Admin_Main_Directive_DpMaxHeight