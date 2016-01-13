define ['Admin/Main/Ctrl/Base'], (Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'
    @DEPS    = ['$http']

    init: ->
      @$scope.code = ''
      @$scope.base_options = {
        url: {
          widget_loader: '',
          widget_bundle: '',
          helpdesk: ''
        }
      }
      @$scope.custom_options = {
        widget: {
          type: 'column',
          position: 'right',
          agentPollingTimeout: 10
        },
        company: {
          name: 'Helpdesk',
          logo: ''
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
          requestUserInfo: true,
          proactive: true,
          popup: {
            title: 'DeskPRO Customer Support',
            message: 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.',
            replyType: 'buttons'
          },
          beginMode: 'form',
          waitingTimeout: 30,
          agentPollingTimeout: 10
        }
      }

    initialLoad: ->
      @$http.get('/api/v2/widget/setup').success((response) =>
        @$scope.base_options = response;
        @$scope.custom_options.company = response.company;
        @initLiveDemo()
      );

      @$scope.$watch('custom_options', =>
        @updateLiveDemo()
      , true)

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {}, @$scope.custom_options)
      if (liveDemo)
        options.widget.liveDemo = true

      return options

    getCode: (options) ->
      """
        <!-- DeskPRO Chat -->
          <script>
              window.__DP_APP_SRC__ = '#{@$scope.base_options.url.widget_bundle}';
              window.__DP_URL__ = '#{@$scope.base_options.url.helpdesk}';
              window.__DP_OPTIONS__ = #{JSON.stringify(options)};
          </script>

          <script type="text/javascript" charset="UTF-8" src="#{@$scope.base_options.url.widget_loader}"></script>
        <!-- /DeskPRO Chat -->
      """

    getLiveDemoDocument: ->
      document.getElementById('live-demo').contentDocument

    updateChatCode: ->
      @$scope.code = @getCode(@getOptions())

    initLiveDemo: ->
      demoDocument = @getLiveDemoDocument();
      demoDocument.write('<body>' + @getCode(@getOptions(true)) + '</body>');
      demoDocument.close();

    updateLiveDemo: ->
      demoWindow = @getLiveDemoDocument().dp_loader;
      if (demoWindow)
        demoWindow.emitter.emit('reloadOptions', @getOptions(true))

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
