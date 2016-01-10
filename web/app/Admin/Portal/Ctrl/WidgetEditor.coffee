define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'

    init: ->
      @$scope.code = ''
      @$scope.chat_options = {
        widget: {
          type: 'column',
          position: 'right',
          agentPollingTimeout: 10
        },
        company: {
          name: 'Acme Corp. Chat and a long name lorel ipsum dolor',
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
      data_promise = @Api.sendDataGet({
        hdinfo:     '/deskpro/info',
        chat_setup: '/chat_setup'
      }).then((res) =>
        @hdinfo = res.data.hdinfo
        @$scope.setup = res.data.chat_setup.chat_setup
      )

      @initLiveDemo()
      @$scope.$watch('chat_options', =>
        @updateLiveDemo()
      , true)

      return @$q.all([data_promise])

    getOptions: (liveDemo = false) ->
      options = $.extend(true, {}, @$scope.chat_options)
      if (liveDemo)
        options.widget.liveDemo = true

      options

    getCode: (options) ->
      """
        <!-- DeskPRO Chat -->
          <script>
              window.__DP_APP_SRC__ = 'http://localhost:9666/pub/build/DeskPRO_WidgetBundle.js';
              window.__DP_URL__ = 'http://deskpro.com.dev/';
              window.__DP_OPTIONS__ = #{JSON.stringify(options)};
          </script>

          <script type="text/javascript" charset="UTF-8" src="http://deskpro.com.dev/pub/build/widget_loader.js?1446397152"></script>
        <!-- /DeskPRO Chat -->
      """

    updateChatCode: ->
      @$scope.code = @getCode(@getOptions())

    initLiveDemo: ->
      demoDocument = document.getElementById('live-demo').contentDocument;
      demoDocument.write('<body>' + @getCode(@getOptions(true)) + '</body>');
      demoDocument.close();

    updateLiveDemo: ->
      demoWindow = document.getElementById('live-demo').contentDocument.dp_loader;
      if (demoWindow)
        demoWindow.DP_OPTIONS = @getOptions(true)
        demoWindow.reloadOptions()

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
