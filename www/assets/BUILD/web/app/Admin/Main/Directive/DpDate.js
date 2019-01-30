define ['moment'], (moment)->
  ###
    # Description
    # -----------
    #
    # This converts model datetime from UTC to local timezone when rendered and back when saved
    #
    ###
  Admin_Main_Directive_DpDate = ['DpDateService', '$parse', (ds, $parse) ->
    return {
      restrict: 'A'
      scope: {
        dpDate: "=dpDate"
      },
      link: (scope, el, attr) ->
        format = attr.format || "fulltime"

        update = ->
          datestr = scope.dpDate
          result  = null

          if datestr
            result = ds.format(datestr, format)

          if result
            el.text(ds.format(datestr, format))
          else if datestr
            el.text(datestr)

        update()

        scope.$watch('dpDate', -> update())
    }
  ]

  return Admin_Main_Directive_DpDate