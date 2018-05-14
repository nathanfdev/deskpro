define [
  'Admin/Main/Ctrl/Base',
  'Admin/Languages/PhraseSaver'
], (
  Admin_Ctrl_Base,
  PhraseSaver
) ->
  class Admin_Languages_Ctrl_PhraseResGroup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_PhraseResGroup'
    @CTRL_AS = 'EditCtrl'

    init: ->
      @langId  = @$stateParams.id.replace(/^phrases\-/, '')
      @groupId = @$stateParams.groupId.replace(/^res\-/, '')

    initialLoad: ->
      promise = @Api.sendDataGet({
        phrase_info: "/langs/#{@langId}/#{@groupId}"
      }).then((result) =>
        phrases = result.data.phrase_info.phrases

        for p in phrases
          if p.depth
            p.depth_items = Array.from(Array(p.depth).keys())

        # Add header item before elements for same field
        # For example if we have Title phrase element and Description phrase element for one field
        #  - then add header element to display before them
        phrasesWithHeaders = [];
        prevPhrase = null;
        fieldPhrasesCnt = 0;
        while phrase = phrases.pop()
          if prevPhrase
            if prevPhrase.type_id == phrase.type_id
              ++fieldPhrasesCnt
            else
              if fieldPhrasesCnt
                phrasesWithHeaders.push({
                  type: '_header'
                  title: prevPhrase.default
                })
              fieldPhrasesCnt = 0
          phrasesWithHeaders.push(phrase)
          prevPhrase = phrase
        if prevPhrase and fieldPhrasesCnt
          phrasesWithHeaders.push({
            type: '_header'
            title: prevPhrase.default
          })

        @phrases = phrasesWithHeaders.reverse();
      )
      return promise

    doSave: ->
      @startSpinner('saving')
      saver = new PhraseSaver(@Api, @$q)
      saver.savePhrases(@langId, @phrases.filter((x) => x.type != '_header')).then(=>
        @stopSpinner('saving')
      )

  Admin_Languages_Ctrl_PhraseResGroup.EXPORT_CTRL()