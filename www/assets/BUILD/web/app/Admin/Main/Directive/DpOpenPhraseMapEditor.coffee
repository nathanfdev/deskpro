define ->
  ###
    # Description
    # -----------
    #
    # This attaches a click handler to the element that opens up a phrase editor for the specified phrase.
    #
    # Example
    # -------
    # <button dp-open-phrase-map-editor="{phrase_map: translations, key: name, languages: languages}">Edit Phrase</button>
    ###
  Admin_Main_Directive_DpOpenPhraseMapEditor = ['$modal', '$controller', ($modal, $controller) ->
    return {
      restrict: 'A',
      link: (scope, element, attrs) ->
        element.on('click', (ev) ->
          ev.preventDefault()

          editorOptions = scope.$eval(attrs.dpOpenPhraseMapEditor)

          phrase_map = {}
          for l in editorOptions.phrase_map
            phrase_map[l.language] = l[editorOptions.key]

          save_map = ->
            for l in editorOptions.phrase_map
              if phrase_map[l.language]
                l[editorOptions.key] = phrase_map[l.language]
                delete phrase_map[l.language]

            if Object.keys(phrase_map).length
              for own id, l of phrase_map
                if l
                  lang = {}
                  lang.language = id
                  lang[editorOptions.key] = l
                  editorOptions.phrase_map.push lang

          modalInstance = $modal.open({
            templateUrl: DP_BASE_ADMIN_URL+'/load-view/Languages/modal-translate-phrase.html',
            controller: 'Admin_Languages_Ctrl_TranslateMapModal',
            resolve: {
              phrase_map: ->
                return phrase_map

              languages: ->
                return editorOptions.languages

              save_map: ->
                return save_map
            }
          })
        )
        return
    }
  ]

  return Admin_Main_Directive_DpOpenPhraseMapEditor
