define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
  class Admin_ChatSetup_Ctrl_ChatSetup extends Admin_Ctrl_Base
    @CTRL_ID = 'Admin_ChatSetup_Ctrl_ChatSetup'
    @CTRL_AS = 'ChatSetup'
    @DEPS    = []

    ###
  #
    ###

    init: ->

      @setup = null

      @$scope.code_snippets = {
        chat: '',
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

    ###
  #
    ###

    initialLoad: ->

      data_promise = @Api.sendDataGet({

        hdinfo:     '/deskpro/info',
        chat_setup: '/chat_setup'

      }).then((res) =>

        @hdinfo = res.data.hdinfo
        @$scope.setup = res.data.chat_setup.chat_setup

        @$scope.$watch('chat_options', =>
          @updateChatCode()
        , true)
      )

      return @$q.all([data_promise])

    ###
    #
    ###

    toggleChat: () ->

      if @$scope.setup.chat_enabled
        val = '1'
      else
        val = '0'

      @Api.sendPost('/chat_setup/toggle_chat/' + val)

    ################################################################################################################
    # Chat Code
    ################################################################################################################

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
        <script type="text/javascript">
          var DpChatWidget_Options = DpChatWidget_Options || {};
          DpChatWidget_Options.startPhrase = '#{start_phrase}';#{offline_url_code}
          DpChatWidget_Options.tabLocation = '#{btn_pos}';

          DpChatWidget_Options.openInWindowPhrase = '#{open_window_phrase}';
          DpChatWidget_Options.resumePhrase = '#{resume_phrase}';
          DpChatWidget_Options.languageId = #{lang_id};

          /**
           * Specify department IDs that should be the only options the user can select.
           * Note: If only one department is available, the Department field will be hidden completely.
           */
          //DpChatWidget_Options.onlyShowDepartments = [];

          /**
           * Style for the chat button
           */
          DpChatWidget_Options.btnStyle = {
            bgColor: '#3F3F3F',
            border: '2px solid #727272',
            textColor: '#FFFFFF',
            textShadow: '0px 1px 2px #000000',
            font: 'bold 12px Arial, sans-serif'
          },

          /**
           * Style for the chat border/frame that goes
           * around the chat window.
           */
          DpChatWidget_Options.frameStyle = {
            bgColor: '#2A69A9',
            textColor: '#FFFFFF'
          };

          DpChatWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
          DpChatWidget_Options.deskproUrl = DpChatWidget_Options.protocol + ':#{@hdinfo.widget_url}';
          DpChatWidget_Options.currentPageUrl = window.location;
          DpChatWidget_Options.referrerPageUrl = document.referrer;
          if (document.getElementsByTagName) {
            (function() {
            var scr   = document.createElement('script');
            scr.type  = 'text/javascript';
            scr.async = true;
            scr.src   = DpChatWidget_Options.protocol + ':#{@hdinfo.asset_url}javascripts/DeskPRO/User/ChatWidget/ChatWidget.js';
            (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
          })();
        }
        </script>
        <!-- /DeskPRO Chat -->
      """

      @$scope.code_snippets.chat = code


  Admin_ChatSetup_Ctrl_ChatSetup.EXPORT_CTRL()