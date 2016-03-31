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
      setupPromise = @$http.get('/api/v2/widget/setup')
      setupPromise.success((response) =>
        data = response.data

        @$scope.url = data.url;
        @$scope.company = data.company;
        @$scope.global_settings = data.settings.global;
        @$scope.brand_settings = data.settings.brand;
        @$scope.enabled_on_portal = data.enabled_on_portal;

        @initLiveDemo()
      );

      departmentsPromise = @$http.get('/api/v2/ticket_departments')
      departmentsPromise.success((response) =>
        @$scope.departments = response.data;
      );

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

    getCode: (options) ->
      """
        <!-- DeskPRO Chat -->
          <script>
              window.__DP_APP_SRC__ = '#{@$scope.url.widget_bundle}';
              window.__DP_URL__ = '#{@$scope.url.helpdesk}';
              window.__DP_OPTIONS__ = #{JSON.stringify(options)};
          </script>

          <script type="text/javascript" charset="UTF-8" src="#{@$scope.url.widget_loader}"></script>
        <!-- /DeskPRO Chat -->
      """

    getFrameNode: ->
      document.getElementById('live-demo')

    getLiveDemoDocument: ->
      @getFrameNode().contentDocument

    applyPortalWidgetSettings: ->
      @$scope.applying_to_portal = true
      @$http({
        method: 'POST',
        url: '/api/v2/widget/portal/apply',
        data: {
          global: @$scope.global_settings,
          brand: @$scope.brand_settings
        }
        headers: {
          'X-Agent-Request': 'true'
        }
      })
      .then(
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
      @$http({
        method: 'POST',
        url: '/api/v2/widget/portal/remove'
      })
      .then(
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
      @$http({
        method: 'POST',
        url: '/api/v2/widget/setup',
        data: {
          global: @$scope.global_settings,
          brand: @$scope.brand_settings
        }
        headers: {
          'X-Agent-Request': 'true'
        }
      })
      .then(
        () =>
          @$scope.code = @getCode(@getOptions())
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

      demoDocument = @getLiveDemoDocument();
      demoDocument.write('<body>' + @getCode(@getOptions(true)) + '</body>');
      demoDocument.close();

      @getFrameNode().contentWindow.addEventListener('message', (event) =>
        parent.window.postMessage(event.data, '*')
      , false);

      return;

    updateLiveDemo: ->
      frameWindow = @getLiveDemoDocument().dp_loader;
      if (frameWindow)
        frameWindow.postMessage({type: 'reloadOptions', options: @getOptions(true)}, '*');
        frameWindow.postMessage({type: 'reloadSettings', options: @$scope.global_settings}, '*');

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
