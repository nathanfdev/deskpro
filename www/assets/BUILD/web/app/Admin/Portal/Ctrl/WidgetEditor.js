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
      @$scope.jwt_settings = {}
      @$scope.chat_custom_fields = []
      @$scope.user_groups = []
      @$scope.user_group_permission = []
      @$scope.everyone_group = false
      @$scope.reg_group = false
      @$scope.enabled_on_portal = false
      @$scope.widgetLoaded = false
      @$scope.departments = []
      @$scope.chat_departments = []
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
              for field in @$scope.brand_settings.chat.custom_fields
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

      @$scope.$watch('enabled_on_portal', updateLiveDemoDebounce, true)
      @$scope.$watch('brand_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('global_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('jwt_settings', updateLiveDemoDebounce, true)

      # widget editor bootstrap promises
      # preload form data
      promises = []

      # todo better get stored data from local storage
      savedSettings = localStorage.getItem 'dpWidgetSettings'+@$scope.brand_id

      promise = @Api2.sendGet('/settings/brands/'+@$scope.brand_id+'/widget/setup')
      promise.then (response) =>
        data = response.data.data
        @$scope.remote_settings = JSON.parse(JSON.stringify(data.settings))
        @$scope.remote_settings.enabled_on_portal = data.enabled_on_portal
        @$scope.remote_settings.jwt = angular.copy(data.jwt_settings)

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
        @$scope.jwt_settings = data.jwt_settings

        @$scope.saving_code = true
        @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data.data
          @$scope.saving_code = false
      promises.push(promise)

      promise = @Api2.sendGet('/languages')
      promise.then (res) =>
        @$scope.languages = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/user_chat_custom_fields?is_enabled=-1')
      promise.then (res) =>
        @$scope.chat_custom_fields = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/ticket_departments?selectable=1')
      promise.then (res) =>
        @$scope.departments = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/chat_departments?selectable=1')
      promise.then (res) =>
        @$scope.chat_departments = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/widget/live_demo/sample_state')
      promise.then (res) =>
        @$scope.sample_state = res.data.data
      promises.push(promise)

      promise = @Api2.sendGet('/user_groups')
      promise.then (res) =>
        @$scope.user_groups = res.data.data

        # global user group permissions
        for group in res.data.data
          if group.sys_name == 'everyone'
            @$scope.everyone_group = group
          if group.sys_name == 'registered'
            @$scope.reg_group = group

          @$scope.user_group_permission[group.id] = false
          for permission in group.permissions
            if permission.name == 'chat.use'
              @$scope.user_group_permission[group.id] = (permission.value and permission.is_active)

        for group in res.data.data
          if @$scope.user_group_permission[@$scope.everyone_group.id]
            @$scope.user_group_permission[group.id] = true
          if @$scope.user_group_permission[@$scope.reg_group.id] and group.sys_name != 'everyone'
            @$scope.user_group_permission[group.id] = true

      promises.push(promise)

      @$q.all(promises).then =>
        @initLiveDemo()
        @updateLiveDemo()

        # order custom fields by brand display order
        for field in @$scope.chat_custom_fields
          brandField = @getBrandCustomField(field.id)
          if brandField
            field.display_order = brandField.display_order

        @$scope.chat_custom_fields.sort((a, b) =>
          if a.display_order < b.display_order
            return -1
          if a.display_order > b.display_order
            return 1
          return 0
        )

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
      enabled_on_portal: @$scope.enabled_on_portal
      settings: {
        global: @$scope.global_settings
        brand: @$scope.brand_settings
      }
      jwt_settings: @$scope.jwt_settings
    }

    getBrandCustomField: (fieldId) ->
      for field in @$scope.brand_settings.chat.custom_fields
        if field.id == parseInt(fieldId)
          return field

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

    changeRights: (group, $event) ->
      $event.preventDefault()

      index = @$scope.brand_settings.chat.user_groups.indexOf(group.id)
      if index != -1
        @$scope.brand_settings.chat.user_groups.splice(index, 1)
      else
        @$scope.brand_settings.chat.user_groups.push(group.id)

        everyone = @$scope.everyone_group.id
        reg = @$scope.reg_group.id
        if group.id == everyone
          for own i,g of @$scope.user_groups
            if g.id != everyone && @$scope.brand_settings.chat.user_groups.indexOf(g.id) == -1
              @$scope.brand_settings.chat.user_groups.push(g.id)
          return true
        else if group.id == reg
          for own i,g of @$scope.user_groups
            if g.id != everyone && g.id != reg && @$scope.brand_settings.chat.user_groups.indexOf(g.id) == -1
              @$scope.brand_settings.chat.user_groups.push(g.id)
          return true
        return true

    hasChanged: ->
      angular.toJson(@$scope.enabled_on_portal) != angular.toJson(@$scope.remote_settings.enabled_on_portal) or
      angular.toJson(@$scope.global_settings) != angular.toJson(@$scope.remote_settings.global) or
      angular.toJson(@$scope.brand_settings) != angular.toJson(@$scope.remote_settings.brand) or
      angular.toJson(@$scope.jwt_settings) != angular.toJson(@$scope.remote_settings.jwt)

    loadCode: ->
      @Api2.sendGet('settings/brands/'+@$scope.brand_id+'/widget/code')

    loadLiveDemoCode: ->
      @Api2.sendGet('settings/brands/'+@$scope.brand_id+'/widget/live_demo_code')

    updateChatCode: ->
      @$scope.code = ''
      @$scope.saving_code = true

      promises = []
      promise = @Api2.sendPostJson('/settings/brands/'+@$scope.brand_id+'/widget/setup', @getWidgetSaveData(), null, headers: {'X-Agent-Request': 'true'})
      promise.then(
        () => @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data.data
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
        widgetData = @getWidgetSaveData();
        @$scope.remote_settings = {
          global: angular.copy(widgetData.settings.global)
          brand: angular.copy(widgetData.settings.brand)
          enabled_on_portal: angular.copy(widgetData.enabled_on_portal)
          jwt: angular.copy(widgetData.jwt_settings)
        }
        @$scope.flag_has_changed = @hasChanged()
        localStorage.removeItem 'dpWidgetSettings'+@$scope.brand_id
        @Growl.success "Settings saved"
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

    initLiveDemo: () ->
      (window.parent || window).addEventListener('message', (event) =>
        if (event.data?.type == 'widgetStatus')
          @$scope.$apply =>
            @$scope.widgetLoaded = true
            @getDpWidget().dispatchCustomEvent('setLiveDemoSampleState', {
              sampleState:      @$scope.sample_state,
              options:          @getOptions(true),
              settings:         @$scope.global_settings,
              chatCustomFields: JSON.parse(angular.toJson(@$scope.chat_custom_fields))
            })
            @updateLiveDemo()
        else if (event.data?.type == 'widgetDemoStage')
          @$scope.$apply =>
            @$scope.demo_state = event.data?.options
      , false)

      @loadLiveDemoCode().then (codeResponse) =>
        code = codeResponse.data.data.replace(/widget": {/, "widget\": {\n\"live_demo\": true,")

        demoDocument = @getLiveDemoDocument()
        demoDocument.write("<body>#{code}</body>")
        demoDocument.close()

        @getFrameNode().contentWindow.addEventListener('message', (event) ->
          parent.window.postMessage(event.data, '*')
        , false)

    updateLiveDemo: () ->
      @$scope.flag_has_changed = @hasChanged()
      try
        localStorage.setItem 'dpWidgetSettings'+@$scope.brand_id, JSON.stringify(@getWidgetSaveData())
      catch
        console.log('dpWidgetSettings were not saved in local storage')

      if (@getDpWidget())
        @getDpWidget().dispatchCustomEvent('reloadLiveDemoOptions', @getOptions(true))
        @getDpWidget().dispatchCustomEvent('reloadLiveDemoSettings', @$scope.global_settings)
        @getDpWidget().dispatchCustomEvent('setLiveDemoChatCustomFields', JSON.parse(angular.toJson(@$scope.chat_custom_fields)))

    sendInstructions: () ->
      @Api2
        .sendPostJson('/settings/brands/' + @$stateParams.brandId + '/widget/send-instructions', { email: @$scope.emailSendInstructions })
        .success () =>
          @Growl.success "Email sent successfully"
          @$scope.emailSendInstructions = ''
        .error () =>
          @Growl.error("An error occurred while sending instructions. Try again.")

  AdminPortalCtrlWidgetEditor.EXPORT_CTRL()
