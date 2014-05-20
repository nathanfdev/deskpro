(function() {
  define(function() {

    /*
        * Description
        * -----------
        *
        * This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
        *
        * Example
        * -------
        * <button dp-open-phrase-editor="agent.general.departments">Edit Phrase</button>
     */
    var Admin_Main_Directive_DpOpenPhraseEditor;
    Admin_Main_Directive_DpOpenPhraseEditor = [
      '$modal', '$controller', function($modal, $controller) {
        return {
          restrict: 'A',
          link: function(scope, element, attrs) {
            element.on('click', function(ev) {
              var editorOptions, modalInstance;
              ev.preventDefault();
              editorOptions = scope.$eval(attrs.dpOpenPhraseEditor);
              return modalInstance = $modal.open({
                templateUrl: DP_BASE_ADMIN_URL + '/load-view/Languages/modal-translate-phrase.html',
                controller: 'Admin_Languages_Ctrl_TranslateModal',
                resolve: {
                  phraseId: function() {
                    return editorOptions.phraseId || null;
                  },
                  editorOptions: function() {
                    return editorOptions;
                  }
                }
              });
            });
          }
        };
      }
    ];
    return Admin_Main_Directive_DpOpenPhraseEditor;
  });

}).call(this);

//# sourceMappingURL=DpOpenPhraseEditor.js.map
