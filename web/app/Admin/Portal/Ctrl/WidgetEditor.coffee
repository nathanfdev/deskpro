define ['Admin/Main/Ctrl/Base', 'DeskPRO/Util/Functions'], (Admin_Ctrl_Base, Functions) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'
    @DEPS    = ['$http']

    init: ->
      @$scope.code = ''
      @$scope.url = {
        widget_loader: '',
        widget_bundle: '',
        helpdesk: ''
      };
      @$scope.company = {
        name: 'Helpdesk',
        logo: ''
      }
      @$scope.global_settings = {
        chat: {
          require_login: false,
          email_validation: false
        }
      }
      @$scope.brand_settings = {
        widget: {
          type: 'column',
          position: 'right',
          agent_polling_timeout: 10
        },
        button: {
          size: 'medium',
          name: 'Help',
          colors: {
            background: '#62ad8c',
            text: '#ffffff',
            border: '#4e9576'
          }
        },
        chat: {
          enabled: true,
          request_user_info: true,
          proactive: true,
          popup: {
            title: 'DeskPRO Customer Support',
            message: 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.',
            reply_type: 'buttons'
          },
          begin_mode: 'form',
          waiting_timeout: 30
        }
      }

      @$scope.widgetLoaded = false
      @$scope.departments = []

    initialLoad: ->
      setupPromise = @$http.get('/api/v2/widget/setup')
      setupPromise.success((response) =>
        data = response.data

        @$scope.url = data.url;
        @$scope.company = $.extend(true, @$scope.company, data.company);
        @$scope.global_settings = $.extend(true, @$scope.global_settings, data.settings.global);
        @$scope.brand_settings = $.extend(true, @$scope.brand_settings, data.settings.brand);

        @initLiveDemo()
      );

      departmentsPromise = @$http.get('/api/v2/ticket_departments')
      departmentsPromise.success((response) =>
        @$scope.departments = response.data;
      );

      updateLiveDemoDebounce = Functions.debounce( =>
        @updateLiveDemo()
      , 350)

      @$scope.$watch('brand_settings', updateLiveDemoDebounce, true)
      @$scope.$watch('global_settings', updateLiveDemoDebounce, true)

      return @$q.all([setupPromise, departmentsPromise])

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {company: @$scope.company}, @$scope.brand_settings)
      if (liveDemo)
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

    getLiveDemoDocument: ->
      document.getElementById('live-demo').contentDocument

    updateChatCode: ->
      @$scope.code = ''
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
        () => @$scope.code = @getCode(@getOptions()),
        (response) => console.log(response.data)
      )

    initLiveDemo: ->
      demoDocument = @getLiveDemoDocument();
      demoDocument.write('<body>' + @getCode(@getOptions(true)) + '</body>');
      demoDocument.close();

      @emitter = null
      interval = setInterval( =>
        demoWindow = demoDocument.dp_loader;
        if (demoWindow.emitter)
          @emitter = demoWindow.emitter
          @emitter.on('loaded', =>
            @$scope.$apply( => @$scope.widgetLoaded = true)
          )
          clearInterval(interval)
      , 1000)


    updateLiveDemo: ->
      if (@emitter)
        @emitter.emit('reloadOptions', @getOptions(true))
        @emitter.emit('reloadSettings', @$scope.global_settings)

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
