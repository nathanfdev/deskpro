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
        @phrases = result.data.phrase_info.phrases

        for p in @phrases
          if p.depth
            p.depth_items = new Array(p.depth)
      )
      return promise

    doSave: ->
      @startSpinner('saving')
      saver = new PhraseSaver(@Api, @$q)
      saver.savePhrases(@langId, @phrases).then(=>
        @stopSpinner('saving')
      )

  Admin_Languages_Ctrl_PhraseResGroup.EXPORT_CTRL()