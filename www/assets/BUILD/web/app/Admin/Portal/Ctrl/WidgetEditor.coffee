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

      @$scope.enabled_on_portal = false

      @$scope.widgetLoaded = false
      @$scope.departments = []

      @$scope.saving_code = false
      @$scope.applying_to_portal = false
      @$scope.formErrors = {}

    initialLoad: ->
      setupPromise = @Api2.sendGet('/widget/setup').then (response) =>
        data = response.data.data

        @$scope.url = data.url;
        @$scope.company = data.company;
        @$scope.global_settings = data.settings.global;
        @$scope.brand_settings = data.settings.brand;
        @$scope.enabled_on_portal = data.enabled_on_portal;

        @initLiveDemo()

      departmentsPromise = @Api2.sendGet('/ticket_departments').then (response) =>
        @$scope.departments = response.data.data

      updateLiveDemoDebounce = Functions.debounce( =>
        @$scope.formErrors = {}
        @updateLiveDemo()
      , 350)

      @$scope.$watch('brand_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('global_settings', updateLiveDemoDebounce, true)

      return @$q.all([setupPromise, departmentsPromise])

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {company: @$scope.company}, @$scope.brand_settings)
      if (liveDemo?.options?.widget)
          options.widget.live_demo = true

      return options

    loadCode: ->
      @Api2.sendGet('widget/code').then (response) =>
        @$scope.code = response.data

    getFrameNode: ->
      document.getElementById('live-demo')

    getLiveDemoDocument: ->
      @getFrameNode().contentDocument

    getSaveData: -> {
      global: @$scope.global_settings,
      brand: @$scope.brand_settings
    }

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

      @Api2.sendPostJson('/widget/setup', @getSaveData(), null, headers: {
        'X-Agent-Request': 'true'
      }).then(
        () => @loadCode().then =>
          @$scope.saving_code = false
        ,
        (response) =>
          @$scope.formErrors = response.data?.errors?.fields
          @$scope.saving_code = false
      )

    initLiveDemo: ->
      window.addEventListener('message', (event) =>
        if (event.data?.type == 'widgetLoaded')
          @$scope.$apply( => @$scope.widgetLoaded = true)
      , false);

      @loadCode().then(() =>
          demoDocument = @getLiveDemoDocument();
          demoDocument.write('<body>' + @$scope.code + '</body>');
          demoDocument.close();

          @getFrameNode().contentWindow.addEventListener('message', (event) =>
            parent.window.postMessage(event.data, '*')
          , false);
      )

      return;

    updateLiveDemo: ->
      frameWindow = @getLiveDemoDocument().dp_loader;
      if (frameWindow)
        frameWindow.postMessage({type: 'reloadOptions', options: @getOptions(true)}, '*');
        frameWindow.postMessage({type: 'reloadSettings', options: @$scope.global_settings}, '*');

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
