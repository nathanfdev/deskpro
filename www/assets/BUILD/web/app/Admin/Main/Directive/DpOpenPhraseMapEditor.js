define(() => {
  /*
    * Description
    * -----------
    *
    * This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
    *
    * Example
    * -------
    * <button dp-open-phrase-map-editor="{phrase_map: translations, key: name, languages: languages}">Edit Phrase</button>
    */
  const Admin_Main_Directive_DpOpenPhraseMapEditor = ['$modal', '$controller', ($modal, $controller) =>
    ({
      restrict: 'A',
      link(scope, element, attrs) {
        element.on('click', (ev) => {
          let modalInstance;
          ev.preventDefault();

          const editorOptions = scope.$eval(attrs.dpOpenPhraseMapEditor);

          const phrase_map = {};
          for (var l of Array.from(editorOptions.phrase_map)) {
            phrase_map[l.language] = l[editorOptions.key];
          }

          const save_map = function () {
            for (l of Array.from(editorOptions.phrase_map)) {
              if (phrase_map[l.language]) {
                l[editorOptions.key] = phrase_map[l.language];
                delete phrase_map[l.language];
              }
            }

            if (Object.keys(phrase_map).length) {
              return (() => {
                const result = [];
                for (const id of Object.keys(phrase_map || {})) {
                  l = phrase_map[id];
                  if (l) {
                    const lang = {};
                    lang.language = id;
                    lang[editorOptions.key] = l;
                    result.push(editorOptions.phrase_map.push(lang));
                  } else {
                    result.push(undefined);
                  }
                }
                return result;
              })();
            }
          };

          return modalInstance = $modal.open({
            templateUrl: `${DP_BASE_ADMIN_URL}/load-view/Languages/modal-translate-phrase.html`,
            controller:  'Admin_Languages_Ctrl_TranslateMapModal',
            resolve:     {
              phrase_map() {
                return phrase_map;
              },

              languages() {
                return editorOptions.languages;
              },

              save_map() {
                return save_map;
              }
            }
          });
        });
      }
    })

  ];

  return Admin_Main_Directive_DpOpenPhraseMapEditor;
});
