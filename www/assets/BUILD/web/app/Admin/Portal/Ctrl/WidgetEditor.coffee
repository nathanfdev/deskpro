define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions', 'jquery', 'angular'], (Admin_Ctrl_Base, Functions) ->
  class AdminPortalCtrlWidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'AdminPortalCtrlWidgetEditor'
    @CTRL_AS = 'Ctrl'
    @DEPS    = ['$http', '$location']

    init: ->
      @$scope.code = ''
      @$scope.url = {}
      @$scope.company = {}
      @$scope.remote_settings = {}
      @$scope.brand_settings = {}
      @$scope.global_settings = {}
      @$scope.chat_custom_fields = []
      @$scope.remote_chat_custom_fields = []
      @$scope.user_groups = []
      @$scope.everyone_group = false
      @$scope.reg_group = false
      @$scope.user_group_permission = []
      @$scope.remote_user_group_permission = []
      @$scope.enabled_on_portal = false
      @$scope.widgetLoaded = false
      @$scope.departments = []
      @$scope.languages = []
      @$scope.saving_code = false
      @$scope.applying_to_portal = false
      @$scope.show_embed_help = false
      @$scope.pending_changes = false
      @$scope.demo_state = 'button'
      @$scope.formErrors = {}
      @$scope.flag_has_changed = false
      @$scope.section = 'button_settings'
      @$scope.emailSendInstructions = ''
      @$scope.brand_id = @$stateParams.brandId

      @chatFieldsSortOptions = {
        axis: 'y',
        handle: '.drag-handle',
        update: (ev, data) =>
          @$scope.$apply =>
            displayOrder = 0
            $('.chat-custom-fields').children().each (i, item) =>
              for field in @$scope.chat_custom_fields
                if field.id == parseInt($(item).data('id'))
                  field.display_order = displayOrder
                  displayOrder += 10
      }

    initialLoad: (reset = false) ->
      # setup watchers
      # we should update live demo on form change
      updateLiveDemoDebounce = Functions.debounce( =>
        @$scope.formErrors = {}
        @updateLiveDemo()
      , 350)

      @$scope.$watch('brand_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('global_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('chat_custom_fields', updateLiveDemoDebounce, true)
      @$scope.$watch('user_group_permission', updateLiveDemoDebounce, true)

      # widget editor bootstrap promises
      # preload form data
      promises = []

      # todo better get stored data from local storage
      savedSettings = localStorage.getItem 'dpWidgetSettings'+@$scope.brand_id

      promise = @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/widget/setup')
      promise.then (response) =>
        data = response.data.data
        @$scope.remote_settings = JSON.parse(JSON.stringify(data.settings))

        @$scope.url = data.url
        @$scope.company = data.company
        if !reset
          if savedSettings
            savedSettings = JSON.parse savedSettings
        else
          savedSettings = null
        if savedSettings && savedSettings.global? && !jQuery.isEmptyObject savedSettings.global
          @$scope.global_settings = savedSettings.global
        else
          @$scope.global_settings = data.settings.global
        if savedSettings && savedSettings.brand? && !jQuery.isEmptyObject savedSettings.brand
          @$scope.brand_settings = savedSettings.brand
        else
          @$scope.brand_settings = data.settings.brand
        @$scope.enabled_on_portal = data.enabled_on_portal

        @$scope.saving_code = true
        @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data
          @$scope.saving_code = false
      promises.push(promise)

      promise = @Api2.sendGet('/languages')
      promise.then (res) =>
        @$scope.languages = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/user_chat_custom_fields?is_enabled=-1&order_by=display_order&order_dir=asc')
      promise.then (res) =>
        @$scope.chat_custom_fields = res.data.data
        @$scope.remote_chat_custom_fields = $.extend(true, [], @$scope.chat_custom_fields)
      promises.push(promise)

      promise = @Api2.sendGet('/ticket_departments?selectable=1')
      promise.then (res) =>
        @$scope.departments = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/widget/live_demo/sample_state')
      promise.then (res) =>
        @$scope.sample_state = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/user_groups')
      promise.then (res) =>
        @$scope.user_groups = res.data.data
        for group in res.data.data
          if group.sys_name == 'everyone'
            @$scope.everyone_group = group
          if group.sys_name == 'registered'
            @$scope.reg_group = group

          @$scope.user_group_permission[group.id] = false
          for permission in group.permissions
            if permission.name == 'chat.use'
              @$scope.user_group_permission[group.id] = permission.value and permission.is_active

          @$scope.remote_user_group_permission = @$scope.user_group_permission.slice()
      promises.push(promise)

      @$q.all(promises).then =>
        @initLiveDemo()
        @updateLiveDemo()

    getFrameNode: ->
      document.getElementById('live-demo')

    getLiveDemoDocument: ->
      @getFrameNode().contentDocument

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {company: @$scope.company}, @$scope.brand_settings)
      if (liveDemo? && options?.widget)
        options.widget.live_demo = true

      return options

    getDpWidget: ->
      @getFrameNode().contentWindow.DpWidget

    getWidgetSaveData: -> {
      global: @$scope.global_settings,
      brand: @$scope.brand_settings
    }

    getLanguage: (translation) ->
      for language in @$scope.languages
        if (translation.language == language.id)
          return language

    filterUsedLanguages: (translations) ->
      return (language) ->
        if not translations
          return true

        for translation in translations
          if language.id == translation.language
            return false

        return true

    addButtonTranslation: (languageId) ->
      if not languageId
        return

      if not @$scope.brand_settings.button.translations
        @$scope.brand_settings.button.translations = []

      @$scope.brand_settings.button.translations.push({
        language: parseInt(languageId),
        name: ''
      })

    addChatPopupTranslation: (languageId) ->
      if not languageId
        return

      @$scope.brand_settings.chat.popup.translations.push({
        language: parseInt(languageId),
        title: '',
        message: ''
      })

    discard: ->
      if confirm("Current edit on the settings will be overridden. Are your sure?")
        localStorage.removeItem 'dpWidgetSettings'+@$scope.brand_id
        @initialLoad(true).then =>
          @Growl.success "Settings reseted"

    reset: ->
      if confirm("Current edit on the settings will be reseted. Are your sure?")
        localStorage.removeItem 'dpWidgetSettings'+@$scope.brand_id
        @Api2.sendDelete('settings/brands/'+@$scope.brand_id+'/widget/setup').then =>
          @initialLoad(true).then =>
            @Growl.success "Settings reseted"

    changeFrameSource: () ->
      if document.getElementById('iframe-target').value
        @initLiveDemo()

    changeDemoState: (state) ->
      @$scope.demo_state = state
      if (@getDpWidget())
        @getDpWidget().dispatchCustomEvent('changeLiveDemoStage', state)

    changeRights: (group) ->
      everyone = @$scope.everyone_group.id
      reg = @$scope.reg_group.id
      if group.id == everyone && !@$scope.user_group_permission[group.id]
        for own id,g of @$scope.user_group_permission
          if id != everyone
            @$scope.user_group_permission[id] = true
        return true
      if group.id == reg && !@$scope.user_group_permission[group.id]
        for own id,g of @$scope.user_group_permission
          if id != everyone && id != reg
            @$scope.user_group_permission[id] = true
        return true
      return true

    checkRights: (group) ->
      if !@$scope.user_group_permission
        return false
      if @$scope.everyone_group.id == group.id
        return false
      if @$scope.reg_group.id == group.id
        return @$scope.user_group_permission[@$scope.everyone_group.id]
      return @$scope.user_group_permission[@$scope.reg_group.id]

    applyPortalWidgetSettings: ->
      @$scope.applying_to_portal = true
      @Api2.sendPostJson('widget/portal/apply', @getWidgetSaveData(), null, headers: {
        'X-Agent-Request': 'true'
      }).then(
        () =>
          @$scope.applying_to_portal = false
          @$scope.enabled_on_portal = true
      ,
        (response) =>
          @$scope.formErrors = response.data?.errors?.fields
          @$scope.applying_to_portal = false
      )

    removeFromPortal: ->
      @$scope.applying_to_portal = true
      @Api2.sendPost('widget/portal/remove').then(
        () =>
          @$scope.applying_to_portal = false
          @$scope.enabled_on_portal = false
      ,
        () =>
          @$scope.applying_to_portal = false
      )

    hasChanged: ->
      angular.toJson(@$scope.global_settings) != angular.toJson(@$scope.remote_settings.global) or
      angular.toJson(@$scope.brand_settings) != angular.toJson(@$scope.remote_settings.brand) or
      angular.toJson(@$scope.user_group_permission) != angular.toJson(@$scope.remote_user_group_permission) or
      angular.toJson(@$scope.chat_custom_fields) != angular.toJson(@$scope.remote_chat_custom_fields)

    loadCode: (withOptions = false) ->
      if withOptions
        @Api2.sendGet('settings/brands/'+@$scope.brand_id+'/widget/code?options=1')
      else
        @Api2.sendGet('settings/brands/'+@$scope.brand_id+'/widget/code')

    updateChatCode: ->
      @$scope.code = ''
      @$scope.saving_code = true

      promises = []
      promises.push @Api.sendPutJson(
        '/user_groups/permissions/chat.use',
        {permissions: @$scope.user_group_permission}, null, headers: { 'X-Agent-Request': 'true'}
      )

      # api/v2 batch controller doesn't handle PUT request for now
      # so do separate request for each field
      for field in @$scope.chat_custom_fields
        promises.push @Api2.sendPutJson('/user_chat_custom_fields/'+field.id, field, null, headers: {'X-Agent-Request': 'true'})

      promise = @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/widget/setup', @getWidgetSaveData(), null, headers: {'X-Agent-Request': 'true'})
      promise.then(
        () => @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data
          @$scope.saving_code = false
        ,
        (response) =>
          @$scope.formErrors = response.data?.errors?.fields
          @$scope.saving_code = false
      )

      promises.push(promise)

      @startSpinner('saving')
      @$q.all(promises).then( =>
        @stopSpinner('saving')
        @$scope.remote_settings = angular.copy(@getWidgetSaveData())
        @$scope.flag_has_changed = @hasChanged()
        localStorage.removeItem 'dpWidgetSettings'+@$scope.brand_id
        @Growl.success "Settings saved"
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

    initLiveDemo: () ->
      window.addEventListener('message', (event) =>
        if (event.data?.type == 'widgetStatus')
          @$scope.$apply =>
            @$scope.widgetLoaded = true
            @getDpWidget().dispatchCustomEvent('setLiveDemoSampleState', @$scope.sample_state)
            @updateLiveDemo()
        else if (event.data?.type == 'widgetDemoStage')
          @$scope.$apply =>
            @$scope.demo_state = event.data?.options
      , false)

      @loadCode(true).then (codeResponse) =>
        code = codeResponse.data.replace(/widget": {/, "widget\": {\n\"live_demo\": true,")

        demoDocument = @getLiveDemoDocument()
        demoDocument.write("<body>#{code}</body>")
        demoDocument.close()

        @getFrameNode().contentWindow.addEventListener('message', (event) ->
          parent.window.postMessage(event.data, '*')
        , false)

    updateLiveDemo: () ->
      @$scope.flag_has_changed = @hasChanged()
      localStorage.setItem 'dpWidgetSettings'+@$scope.brand_id, JSON.stringify(@getWidgetSaveData())
      if (@getDpWidget())
        @getDpWidget().dispatchCustomEvent('reloadOptions', @getOptions(true))
        @getDpWidget().dispatchCustomEvent('reloadSettings', @$scope.global_settings)
        @getDpWidget().dispatchCustomEvent('setLiveDemoChatCustomFields', JSON.parse(angular.toJson(@$scope.chat_custom_fields)))

    sendInstructions: () ->
      @Api2
        .sendPostJson('/widget/send-instructions', { email: @$scope.emailSendInstructions })
        .success () =>
          @Growl.success "Email sent successfully"
          @$scope.emailSendInstructions = ''
        .error () =>
          @Growl.error("An error occurred while sending instructions. Try again.")

  AdminPortalCtrlWidgetEditor.EXPORT_CTRL()
