define ->
  ###
    # Description
    # -----------
    #
    # Custom on-change directive
    #
    # Example
    # -------
    # <input dp-change="submit" />
    ###
  Admin_Main_Directive_DpChange = [ ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->
        element.bind('change', -> scope.$eval attrs.dpChange)
    }
  ]

  return Admin_Main_Directive_DpChange