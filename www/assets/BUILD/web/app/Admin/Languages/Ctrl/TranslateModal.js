define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_TranslateModal extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Languages_Ctrl_TranslateModal'
    @CTRL_AS   = 'TranslateModal'
    @DEPS      = ['$timeout', '$modalInstance', 'phraseId', 'editorOptions']

    init: ->
      @phrase_map = {}
      @active_lang = null
      @active_trans = null
      @hasPendingPromise = false
      @options = @editorOptions

      @$scope.dismiss = =>
        @$modalInstance.dismiss('cancel')

      @$scope.save = =>
        if @active_lang
          @phrase_map[@active_lang] = @active_trans

        @savePhrases().then(=>
          @$modalInstance.close()
        )

      @$scope.$watch(=>
        return @active_lang
      , (newLangId, oldLangId) =>
        if not oldLangId then return

        @phrase_map[oldLangId] = @active_trans

        if @phrase_map[newLangId]
          @active_trans = @phrase_map[newLangId]
        else
          @active_trans = ''
      )

      @$scope.$watch(=>
        return @active_trans
      , =>
        if not @active_lang then return
        @phrase_map[@active_lang] = @active_trans
      )

    initialLoad: ->
      p = @Api.sendDataGet({
        langs: '/langs',
        lang_phrases: '/langs/phrases/' + @phraseId
      }).success( (data) =>
        @ctrl_is_loading = false
        if @options.exclude_own or @options.exclude_default
          @langs = []
          for l in data.langs.languages
            if @options.exclude_own and DP_PERSON_LANG_ID == l.id
              continue
            if @options.exclude_default and l.id == data.langs.default_lang_id
              continue;

            @langs.push(l)
        else
          @langs = data.langs.languages

        first = null
        for phrase in data.lang_phrases.lang_phrases
          if not first then first = phrase
          lang_id = phrase.language.id
          @phrase_map[lang_id] = phrase.phrase

        if first
          @active_lang  = first.language.id
          @active_trans = first.phrase
        else
          @active_lang = @langs[0].id
          @active_trans = null
      )

      return p

    savePhrases: ->
      phrase_map = angular.copy(@phrase_map)
      phrase_id = @phraseId

      api = @Api
      saveInfo = {
        phrase_id: phrase_id,
        phrase_map: phrase_map,
        saver: (phrase_id, phrase_map) ->
          postData = {'lang_phrases': []}

          for own k, v of phrase_map
            postData.lang_phrases.push({
              phrase: v || '',
              language_id: k
            })

          return api.sendPostJson('/langs/phrases/' + phrase_id, postData)
      }

      saveInfo.save = ->
        saveInfo.saver(saveInfo.phrase_id, saveInfo.phrase_map)

      if @editorOptions.saveHandler
        ret = @editorOptions.saveHandler(saveInfo.phrase_id, saveInfo.phrase_map, saveInfo.saver)
      else
        ret = saveInfo.save()

      if ret.then
        p = ret
        @$scope.is_loading = true
        ret.then(=> @$scope.is_loading = false)
      else
        defer = @$q.defer()
        defer.resolve()
        p = defer.promise

      return p

  Admin_Languages_Ctrl_TranslateModal.EXPORT_CTRL()