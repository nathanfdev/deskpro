define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
  class Admin_Portal_Ctrl_WidgetEditor extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_Portal_Ctrl_WidgetEditor'
    @CTRL_AS = 'Ctrl'

    init: ->
      @setup = null

      @$scope.code_snippets = {
        chat: '',
        code: ''
      }

      @$scope.chat_options = {
        offline_url: '',
        show_offline: false,
        position: 'right',
        start_phrase: 'Click here to chat with us',
        resume_phrase: 'Open your chat',
        offline_phrase: 'Click here to contact us',
        open_window_phrase: 'Open this chat in a new window',
        lang_id: '0'
      }

    initialLoad: ->
      data_promise = @Api.sendDataGet({
        hdinfo:     '/deskpro/info',
        chat_setup: '/chat_setup'
      }).then((res) =>
        @hdinfo = res.data.hdinfo
        @$scope.setup = res.data.chat_setup.chat_setup
      )

      return @$q.all([data_promise])

    toggleChat: () ->
      if @$scope.setup.chat_enabled
        val = '1'
      else
        val = '0'

      @Api.sendPost('/chat_setup/toggle_chat/' + val)

    updateChatCode: ->
      start_phrase       = Strings.addslashes(@$scope.chat_options.start_phrase || 'Click here to chat with us')
      resume_phrase      = Strings.addslashes(@$scope.chat_options.resume_phrase || 'Open your chat')
      offline_phrase     = Strings.addslashes(@$scope.chat_options.offline_phrase || 'Click here to contact us')
      open_window_phrase = Strings.addslashes(@$scope.chat_options.open_window_phrase || 'Open this chat in a new window')
      btn_pos            = @$scope.chat_options.position || 'right'
      lang_id            = @$scope.chat_options.lang_id || 0

      offline_url_code = ''
      if @$scope.chat_options.show_offline
        offline_url_code = "\n\tDpChatWidget_Options.offlineUrl = '#{Strings.addslashes(@$scope.chat_options.offline_url)}';"
        offline_url_code += "\n\tDpChatWidget_Options.offlinePhrase = '#{offline_phrase}';"

      code = """
        <!-- DeskPRO Chat -->
          <script>
              window.__DP_APP_SRC__ = 'http://localhost:9666/pub/build/DeskPRO_WidgetBundle.js';
              window.__DP_URL__ = 'http://deskpro.com.dev/';
              window.__DP_OPTIONS__ = {
                  hasChat: true,
                  agentAcceptTimeout: 30,
                  windowType: 'default',
                  windowPosition: '#{btn_pos}',
                  companyName: 'Acme Corp. Chat and a long name lorel ipsum dolor',
                  companyLogo: '',
                  helpButtonSize: 'large',
                  helpPopup: 'onlineAgents',
                  helpPopupTitle: 'DeskPRO Customer Support',
                  helpPopupMessage: 'Given a string consisting of printable ASCII chars, produce an output consisting of its unique chars in the original order.',
                  chatMode: 'form'
              };
          </script>

          <script type="text/javascript" charset="UTF-8" src="http://deskpro.com.dev/pub/build/widget_loader.js?1446397152"></script>
        <!-- /DeskPRO Chat -->
      """

      @$scope.code_snippets.chat = code

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
