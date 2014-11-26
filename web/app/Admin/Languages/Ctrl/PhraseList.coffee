define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_PhraseList extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Languages_Ctrl_PhraseList'
    @CTRL_AS = 'ListCtrl'

    init: ->
      @id = @$stateParams.id.replace(/^phrases\-/, '')

    initialLoad: ->
      promise = @Api.sendDataGet({
        lang_info:     "/langs/#{@id}",
        phrase_groups: "/langs/phrases-groups"
      }).then((result) =>
        if not result.data.lang_info.language
          @$state.go('setup.languages.install', {id: "install-#{@id}"})
          return

        @pack = result.data.lang_info.pack
        @lang = result.data.lang_info.language

        @phraseGroups = result.data.phrase_groups.phrase_groups
      )
      return promise

  Admin_Languages_Ctrl_PhraseList.EXPORT_CTRL()