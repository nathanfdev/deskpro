define(function() {
  /*
    * Description
    * -----------
    *
    * This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
    *
    * Example
    * -------
    * <dp-semantic-language-button translation="" key=""></dp-semantic-language-button>
    */
  const Admin_Main_Directive_DpSemanticLanguageButton = [() =>
    ({
      restrict: 'E',
      templateUrl: DP_BASE_ADMIN_URL + '/load-view/Main/language-button.html',
      scope: {
        translations: '=',
        key: '@',
        languages: '='
      },
      link(scope, element, attrs) {
        if (!scope.languages) { scope.languages = []; }
        const calcDone = function() {
          if (!scope.translations) {
            return 0;
          }
          let nb = 0;
          for (let l of Array.from(scope.translations)) {
            if (l[scope.key] && (l[scope.key] !== '')) {
              nb++;
            }
          }
          return nb;
        };

        scope.getProgress = function() {
          if (!scope.languages) {
            return '';
          }
          return calcDone() + '/' + scope.languages.length;
        };

        return scope.getPercent = function() {
          if (!scope.languages) {
            return 0;
          }
          return (calcDone() / scope.languages.length) * 100;
        };
      }
    })
  
  ];

  return Admin_Main_Directive_DpSemanticLanguageButton;
});