define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
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
      @$scope.configuration = {
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

    initialLoad: ->
      @$http.get('/api/v2/widget/setup').success((response) =>
        @$scope.url = response.url;
        @$scope.company = $.extend(true, @$scope.company, response.company);
        @$scope.configuration = $.extend(true, @$scope.configuration, response.configuration);

        @initLiveDemo()
      );

      @$scope.$watch('configuration', =>
        @updateLiveDemo()
      , true)

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {company: @$scope.company}, @$scope.configuration)
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
        data: @$scope.configuration
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

    updateLiveDemo: ->
      demoWindow = @getLiveDemoDocument().dp_loader;
      if (demoWindow)
        demoWindow.emitter.emit('reloadOptions', @getOptions(true))

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
