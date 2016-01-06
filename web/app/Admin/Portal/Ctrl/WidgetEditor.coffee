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
        widget_position: 'right',
        widget_type: 'column',

        button_size: 'medium',
        button_word: 'Help',
        button_background_color: '',
        button_text_color: '',
        button_border_color: '',

        chat_enabled: true,
        chat_request_user_info: true,
        chat_begin_mode: 'conversation',
        chat_proactive: true,
        chat_agent_avatar: 'avatars',
        chat_agent_icon_message: '',
        chat_waiting_timeout: 30,

        ticket_department: 'default',

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

    updateChatCode: ->
      options =  @$scope.chat_options;

      widget_position         = options.widget_position || 'right'
      widget_type             = options.widget_type || 'column'

      button_size             = options.button_size || 'medium'
      button_word             = options.button_word || 'Help'
      button_background_color = options.button_background_color
      button_text_color       = options.button_text_color
      button_border_color     = options.button_border_color

      chat_enabled            = options.chat_enabled && 'true' || 'false'
      chat_begin_mode         = options.chat_request_user_info && options.chat_begin_mode || 'simple'
      chat_proactive          = options.chat_proactive && 'true' || 'false'
      chat_agent_avatar       = options.chat_agent_icon
      chat_agent_icon_message = options.chat_agent_icon_message
      chat_waiting_timeout    = parseInt(options.chat_waiting_timeout, 10) || 30

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
                  widget: {
                      type: '#{widget_type}',
                      position: '#{widget_position}'
                  },
                  company: {
                      name: 'Acme Corp. Chat and a long name lorel ipsum dolor',
                      logo: ''
                  },
                  button: {
                      size: '#{button_size}',
                      name: '#{button_word}',
                      backgroundColor: '#{button_background_color}',
                      textColor: '#{button_text_color}',
                      borderColor: '#{button_border_color}'
                  },
                  chat: {
                      enabled: #{chat_enabled},
                      popup: {
                          type: '#{chat_agent_avatar}',
                          title: 'DeskPRO Customer Support',
                          message: '#{chat_agent_icon_message}'
                      },
                      beginMode: '#{chat_begin_mode}',
                      waitingTimeout: #{chat_waiting_timeout}
                  }
              };
          </script>

          <script type="text/javascript" charset="UTF-8" src="http://deskpro.com.dev/pub/build/widget_loader.js?1446397152"></script>
        <!-- /DeskPRO Chat -->
      """

      @$scope.code_snippets.chat = code

  Admin_Portal_Ctrl_WidgetEditor.EXPORT_CTRL()
