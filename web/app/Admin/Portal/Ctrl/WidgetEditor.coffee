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
        position: 'right',
        widget_type: 'column',
        button_size: 'medium',
        button_word: 'Help',
        button_background_color: '',
        button_text_color: '',
        button_border_color: '',

        offline_url: '',
        show_offline: false,
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
      widget_position         = @$scope.chat_options.position || 'right'
      widget_type             = @$scope.chat_options.widget_type || 'column'
      button_size             = @$scope.chat_options.button_size || 'medium'
      button_word             = @$scope.chat_options.button_word || 'Help'
      button_background_color = @$scope.chat_options.button_background_color
      button_text_color       = @$scope.chat_options.button_text_color
      button_border_color     = @$scope.chat_options.button_border_color

      start_phrase       = Strings.addslashes(@$scope.chat_options.start_phrase || 'Click here to chat with us')
      resume_phrase      = Strings.addslashes(@$scope.chat_options.resume_phrase || 'Open your chat')
      offline_phrase     = Strings.addslashes(@$scope.chat_options.offline_phrase || 'Click here to contact us')
      open_window_phrase = Strings.addslashes(@$scope.chat_options.open_window_phrase || 'Open this chat in a new window')
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
                  windowType: '#{widget_type}',
                  windowPosition: '#{widget_position}',
                  companyName: 'Acme Corp. Chat and a long name lorel ipsum dolor',
                  companyLogo: '',
                  helpButton: {
                      size: '#{button_size}',
                      name: '#{button_word}',
                      backgroundColor: '#{button_background_color}',
                      textColor: '#{button_text_color}',
                      borderColor: '#{button_border_color}'
                  },
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
