(function() {
  var __hasProp = {}.hasOwnProperty;

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
                  getWaitOnPromise: function() {
                    var promise;
                    if (attrs.dpSavePhrasePromise) {
                      promise = scope.$eval(attrs.dpSavePhrasePromise);
                      return promise;
                    } else {
                      return null;
                    }
                  },
                  editorOptions: function() {
                    return editorOptions;
                  },
                  getPhraseIdGen: function() {
                    return function() {
                      if (attrs.dpPhraseIdGen) {
                        return function(id) {
                          var find_k, id_vars, re, replace_v;
                          id_vars = scope.$eval(attrs.dpPhraseIdGen);
                          for (find_k in id_vars) {
                            if (!__hasProp.call(id_vars, find_k)) continue;
                            replace_v = id_vars[find_k];
                            re = new RegExp('%' + find_k + '%', 'g');
                            id = id.replace(re, replace_v);
                          }
                          return id;
                        };
                      } else {
                        return null;
                      }
                    };
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
