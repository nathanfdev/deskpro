define ['angular', 'Admin/Main/Ctrl/Base'], (angular, Admin_Ctrl_Base) ->
  class Admin_Languages_Ctrl_TranslateMapModal extends Admin_Ctrl_Base
    @CTRL_ID   = 'Admin_Languages_Ctrl_TranslateMapModal'
    @CTRL_AS   = 'TranslateModal'
    @DEPS      = ['$timeout', '$modalInstance', 'phrase_map', 'save_map', 'languages']

    init: ->
      @active_lang = null
      @active_trans = null
      @hasPendingPromise = false

      @$scope.dismiss = =>
        @$modalInstance.dismiss('cancel')

      @$scope.save = =>
        if @active_lang
          @phrase_map[@active_lang] = @active_trans

          @save_map()
          @$modalInstance.close()

      @$scope.$watch(=>
        return @active_lang
      , (newLangId, oldLangId) =>
        if not oldLangId then return

        @phrase_map[oldLangId] ||= {}
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
      @ctrl_is_loading = false
      @langs = @languages

      @active_lang = @langs[0].id
      if @phrase_map[@active_lang]
        @active_trans = @phrase_map[@active_lang]
      else
        @active_trans = null

  Admin_Languages_Ctrl_TranslateMapModal.EXPORT_CTRL()