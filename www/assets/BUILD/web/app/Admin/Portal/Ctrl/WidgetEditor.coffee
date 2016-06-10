define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions', 'jquery'], (Admin_Ctrl_Base, Functions) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'
    @DEPS    = ['$http']

    init: ->
      @$scope.code = ''

      @$scope.url = {}
      @$scope.company = {}
      @$scope.brand_settings = {}
      @$scope.global_settings = {}
      @$scope.custom_fields = []
      @$scope.user_groups = []
      @$scope.everyone_group = false
      @$scope.reg_group = false
      @$scope.user_group_permission = []

      @$scope.enabled_on_portal = false

      @$scope.widgetLoaded = false
      @$scope.departments = []
      @$scope.languages = []

      @$scope.saving_code = false
      @$scope.applying_to_portal = false
      @$scope.show_embed_help = true
      @$scope.formErrors = {}

    initialLoad: ->
      setupPromise = @Api2.sendGet('/widget/setup').then (response) =>
        data = response.data.data

        @$scope.url = data.url;
        @$scope.company = data.company;
        savedSettings = localStorage.getItem 'widgetSettings'
        if savedSettings
          savedSettings = JSON.parse savedSettings
        if savedSettings.global? && !jQuery.isEmptyObject savedSettings.global
          @$scope.global_settings = savedSettings.global
        else
          @$scope.global_settings = data.settings.global;
        if savedSettings.brand? && !jQuery.isEmptyObject savedSettings.brand
          @$scope.brand_settings = savedSettings.brand
        else
          @$scope.brand_settings = data.settings.brand;
        @$scope.enabled_on_portal = data.enabled_on_portal;

        @initLiveDemo()
        @getChatCustomFields(savedSettings)
        @getUserGroups()
        @loadEditUserGroupPermissions(savedSettings)

        @$scope.saving_code = true
        @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data
          @$scope.saving_code = false

      departmentsPromise = @Api2.sendGet('/ticket_departments').then (response) =>
        @$scope.departments = response.data.data

      languagesPromise = @Api2.sendGet('/languages').then (response) =>
        @$scope.languages = response.data.data

      updateLiveDemoDebounce = Functions.debounce( =>
        @$scope.formErrors = {}
        @updateLiveDemo()
      , 350)

      @$scope.$watch('brand_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('global_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('custom_fields', updateLiveDemoDebounce, true)
      @$scope.$watch('user_group_permission', updateLiveDemoDebounce, true)

      return @$q.all([setupPromise, departmentsPromise, languagesPromise])

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {company: @$scope.company}, @$scope.brand_settings)
      if (liveDemo? && options?.widget)
          options.widget.live_demo = true

      return options

    loadCode: ->
      @Api2.sendGet('widget/code')

    reset: ->
      if confirm("Current edit on the settings will be overridden. Are your sure?")
        localStorage.removeItem 'widgetSettings'
        @initialLoad().then( =>
          @Growl.success "Settings reseted"
        )

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
        
    getChatCustomFields: (savedSettings) ->
      if savedSettings.custom_fields? && !jQuery.isEmptyObject savedSettings.custom_fields
        @$scope.custom_fields = savedSettings.custom_fields
      else
        @Api.sendDataGet([
          '/chat_fields'
        ]).then( (res) =>
          @$scope.custom_fields = []
          for f in res.data.api_chat_fields.custom_fields
            @$scope.custom_fields.push(f)
        )

    getUserGroups: ->
      @Api2.sendGet('/user_groups').then( (res) =>
        @$scope.user_groups = res.data.data
        for g in res.data.data
          if g.sys_name == 'everyone'
            @$scope.everyone_group = g
          if g.sys_name == 'registered'
            @$scope.reg_group = g

      )

    loadEditUserGroupPermissions: (savedSettings) ->
      if savedSettings.user_group_permission? && !jQuery.isEmptyObject savedSettings.user_group_permission
        @$scope.user_group_permission = savedSettings.user_group_permission
      else
        @Api2.sendGet('/user_groups/permissions/chat.use').then( (res) =>
          @$scope.user_group_permission = res.data.data
        )

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

    getFrameNode: ->
      document.getElementById('live-demo')

    getLiveDemoDocument: ->
      @getFrameNode().contentDocument

    getSaveData: -> {
      global: @$scope.global_settings,
      brand: @$scope.brand_settings,
      custom_fields: @$scope.custom_fields,
      user_group_permission: @$scope.user_group_permission
    }

    changeRights: (group) ->
      everyone = @$scope.everyone_group.id
      reg = @$scope.reg_group.id
      if group.id == everyone && !@$scope.user_group_permission[group.id]
        for id,g of @$scope.user_group_permission
          if `id != everyone`
            @$scope.user_group_permission[id] = true
        return true
      if group.id == reg && !@$scope.user_group_permission[group.id]
        for id,g of @$scope.user_group_permission
          if `id != everyone && id != reg`
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
      @Api2.sendPostJson('widget/portal/apply', @getSaveData(), null, headers: {
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

    updateChatCode: ->
      @$scope.code = ''
      @$scope.saving_code = true

      saveData = @getSaveData()
      permissionPromise = @Api.sendPutJson(
        '/user_groups/permissions/chat.use',
        JSON.stringify({permissions: saveData.user_group_permission}), null, headers: {
          'X-Agent-Request': 'true'
        })
      widgetPromise = @Api2.sendPostJson('/widget/setup', {global: saveData.global, brand: saveData.brand}, null, headers: {
        'X-Agent-Request': 'true'
      }).then(
        () => @loadCode().then (codeResponse) =>
          @$scope.code = codeResponse.data
          @$scope.saving_code = false
        ,
        (response) =>
          @$scope.formErrors = response.data?.errors?.fields
          @$scope.saving_code = false
      )

      @startSpinner('saving')
      @$q.all([permissionPromise, widgetPromise]).then( =>
        @stopSpinner('saving')
        localStorage.removeItem 'widgetSettings'
        @Growl.success "Settings saved"
      , (info) =>
        @stopSpinner('saving', true)
        @applyErrorResponseToView(info)
      )

    initLiveDemo: ->
      window.addEventListener('message', (event) =>
        if (event.data?.type == 'widgetStatus')
          @$scope.$apply( => @$scope.widgetLoaded = true)
      , false);

      @loadCode().then((codeResponse) =>
        code = codeResponse.data.replace(/widget": {/, "widget\": {\n\"live_demo\": true,")
        demoDocument = @getLiveDemoDocument();
        demoDocument.write('<body>' + code + '</body>');
        demoDocument.close();

        @getFrameNode().contentWindow.addEventListener('message', (event) =>
          parent.window.postMessage(event.data, '*')
        , false);
      )

      return;

    updateLiveDemo: ->
      DpWidget = @getFrameNode().contentWindow.DpWidget;
      localStorage.setItem 'widgetSettings', JSON.stringify(@getSaveData())
      if (DpWidget)
        DpWidget.dispatchCustomEvent('reloadOptions', @getOptions(true));
        DpWidget.dispatchCustomEvent('reloadSettings', @$scope.global_settings);

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
