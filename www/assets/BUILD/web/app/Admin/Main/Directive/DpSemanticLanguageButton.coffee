define ->
  ###
    # Description
    # -----------
    #
    # This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
    #
    # Example
    # -------
    # <dp-semantic-language-button translation="" key=""></dp-semantic-language-button>
    ###
  Admin_Main_Directive_DpSemanticLanguageButton = [() ->
    return {
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Main/language-button.html',
      scope: {
        translations: '=',
        key: '@',
        languages: '='
      },
      link: (scope, element, attrs) ->
        scope.languages ||= []
        calcDone = ->
          if !scope.translations
            return 0
          nb = 0
          for l in scope.translations
            if l[scope.key] && l[scope.key] != ''
              nb++
          return nb

        scope.getProgress = ->
          if !scope.languages
            return ''
          return calcDone() + '/' + scope.languages.length

        scope.getPercent = ->
          if !scope.languages
            return 0
          return calcDone() / scope.languages.length * 100
    }
  ]

  return Admin_Main_Directive_DpSemanticLanguageButton