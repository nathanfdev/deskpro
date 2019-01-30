// TODO: This file was created by bulk-decaffeinate.
// Sanity-check the conversion and remove this comment.
/*
 * decaffeinate suggestions:
 * DS102: Remove unnecessary code created because of implicit returns
 * Full docs: https://github.com/decaffeinate/decaffeinate/blob/master/docs/suggestions.md
 */
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
  const Admin_Main_Directive_DpOpenPhraseEditor = ['$modal', '$controller', ($modal, $controller) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        element.on('click', function(ev) {
          let modalInstance;
          ev.preventDefault();

          const editorOptions = scope.$eval(attrs.dpOpenPhraseEditor);

          return modalInstance = $modal.open({
            templateUrl: DP_BASE_ADMIN_URL+'/load-view/Languages/modal-translate-phrase.html',
            controller: 'Admin_Languages_Ctrl_TranslateModal',
            resolve: {
              phraseId() {
                return editorOptions.phraseId || null;
              },

              editorOptions() {
                return editorOptions;
              }
            }
          });
        });
      }
    })
  
  ];

  return Admin_Main_Directive_DpOpenPhraseEditor;
});