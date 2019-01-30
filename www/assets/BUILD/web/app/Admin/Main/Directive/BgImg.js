define ->
  ###
    # Description
    # -----------
    #
    # This just adds a style background-image to an element using the evaluated value.
    #
    # Example
    # -------
    # <span bg-img="{{agent.picture_url}}"></span>
  ###
  Admin_Main_Directive_BgImg = [ ->
    return {
      restrict: 'A',
      scope: {
        'bgImg': '&'
      },
      link: (scope, element, attrs) ->
        element.css({
          'background-image': 'url("' + scope.$eval(scope.bgImg) + '")'
        })
    }
  ]

  return Admin_Main_Directive_BgImg