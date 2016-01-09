define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'

    init: ->
      @$scope.code_snippets = {
        code: ''
      }

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
            background: '',
            text: '',
            border: ''
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

      @$scope.$watch('chat_options', =>
        @updateLiveDemo()
      , true)

      return @$q.all([data_promise])

    getCode: (liveDemo = false) ->
      options = $.extend(true, {}, @$scope.chat_options);
      if (liveDemo)
        options.widget.demo = true

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
      @$scope.code_snippets.chat = @getCode()

    updateLiveDemo: ->
      demoDocument = document.getElementById('live-demo').contentDocument;
      demoDocument.write('<body>' + @getCode(true) + '</body>');
      demoDocument.close();

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
