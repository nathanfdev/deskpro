define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
	class Admin_Portal_Ctrl_Embeds extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Portal_Ctrl_Embeds'
		@CTRL_AS = 'Embeds'

		init: ->
			@$scope.code_snippets = {
				overlay: '',
				chat: '',
				form_frame: ''
			}

			@$scope.overlay_options = {
				title: 'Support & Feedback',
				position: 'left'
			}

			@$scope.chat_options = {
				offline_url: '',
				show_offline: false,
				position: 'right',
				start_phrase: 'Click here to chat with us',
				resume_phrase: 'Open your chat',
				offline_phrase: 'Click here to contact us',
				open_window_phrase: 'Open this chat in a new window',
				language_id: '0'
			}

			@$scope.iframe_options = {
				simple_mode: true,
				initial_load: ''
			}

			@$scope.$watch('overlay_options', =>
				@updateWebsiteTabCode()
			, true)
			@$scope.$watch('chat_options', =>
				@updateChatCode()
			, true)
			@$scope.$watch('iframe_options', =>
				@updateIframeCode()
			, true)

			@$scope.embedEditorLoaded = (editor) ->
				$(editor.container).closest('div.editor').data('ace-editor', editor).addClass('with-ace-editor')

			@updateWebsiteTabCode()
			@updateChatCode()
			@updateFormFrameCode()
			@updateIframeCode()

		initialLoad: ->
			return null
			promise = @Api.sendDataGet({

			}).then( (res) =>

			)
			return promise


		################################################################################################################
    	# Website Tab Code
		################################################################################################################

		updateWebsiteTabCode: ->
			btn_title = Strings.addslashes(@$scope.overlay_options.title || 'Support')
			btn_pos   = @$scope.overlay_options.position || 'left'

			code = """
				<!-- DeskPRO Widget -->
				<script type="text/javascript">
					var DpOverlayWidget_Options = DpOverlayWidget_Options || {};
					DpOverlayWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
					DpOverlayWidget_Options.deskproUrl = DpOverlayWidget_Options.protocol + '://';
					DpOverlayWidget_Options.phrase = '#{btn_title}';
					DpOverlayWidget_Options.tabLocation = '#{btn_pos}';
					DpOverlayWidget_Options.topPosition = '200px';
					DpOverlayWidget_Options.btnStyle = {
						bgColor: '#3F3F3F',
						border: '2px solid #727272',
						textColor: '#FFFFFF',
						textShadow: '0px 0px 2px #000000',
						font: 'bold 13px Arial, sans-serif'
					};

					if (document.getElementsByTagName) {
						(function() {
							var scr   = document.createElement('script');
							scr.type  = 'text/javascript';
							scr.async = true;
							scr.src   = DpOverlayWidget_Options.protocol + '://javascripts/DeskPRO/User/WebsiteWidget/Overlay.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
						})();
					}
				</script>
				<!-- /DeskPRO Widget -->
			"""

			@$scope.code_snippets.overlay = code

		################################################################################################################
		# Chat Code
		################################################################################################################

		updateChatCode: ->

			start_phrase       = Strings.addslashes(@$scope.chat_options.start_phrase || 'Click here to chat with us')
			resume_phrase      = Strings.addslashes(@$scope.chat_options.resume_phrase || 'Open your chat')
			offline_phrase     = Strings.addslashes(@$scope.chat_options.offline_phrase || 'Click here to contact us')
			open_window_phrase = Strings.addslashes(@$scope.chat_options.open_window_phrase || 'Open this chat in a new window')
			btn_pos            = @$scope.chat_options.position || 'right'

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
					DpChatWidget_Options.languageId = 0;

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
					DpChatWidget_Options.deskproUrl = DpChatWidget_Options.protocol + '://';
					DpChatWidget_Options.currentPageUrl = window.location;
					DpChatWidget_Options.referrerPageUrl = document.referrer;
					if (document.getElementsByTagName) {
						(function() {
							var scr   = document.createElement('script');
							scr.type  = 'text/javascript';
							scr.async = true;
							scr.src   = DpChatWidget_Options.protocol + '://javascripts/DeskPRO/User/ChatWidget/ChatWidget.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
						})();
					}
				</script>
				<!-- /DeskPRO Chat -->
			"""

			@$scope.code_snippets.chat = code

		################################################################################################################
		# Form Frame
		################################################################################################################

		updateFormFrameCode: ->

			code = """
				<!-- DeskPRO Ticket Form -->
				<div id="dp_newticket_form" style="display: none;"></div>
				<script type="text/javascript">
				var DpNewTicket_Options = DpNewTicket_Options || {};
				DpNewTicket_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
				DpNewTicket_Options.deskproUrl = DpNewTicket_Options.protocol + '://support.deskpro.com/';
				DpNewTicket_Options.initialHeight = 700;
				DpNewTicket_Options.containerId = 'dp_newticket_form';
				DpNewTicket_Options.departmentId = 0;

				/**
				* The Language ID to load for users with no language preference
				*/
				DpNewTicket_Options.languageId = 0;

				/**
				* If the user name is already known, you can set it here.
				* When set, the name field is hidden in the form.
				*/
				DpNewTicket_Options.formUserName = '';

				/**
				* If the user email is already known, you can set it here.
				* When set, the email field is hidden in the form.
				*/
				DpNewTicket_Options.formUserEmail = '';

				if (document.getElementsByTagName) {
					(function() {
						var scr = document.createElement('script');
						scr.type = 'text/javascript';
						scr.async = true;
						scr.src = DpNewTicket_Options.protocol + '://javascripts/DeskPRO/User/TicketFormWidget/TicketFormWidget.js';
						(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
					})();
				}
				</script>
				<!-- /DeskPRO Ticket Form -->
			"""

			@$scope.code_snippets.form_frame = code

		################################################################################################################
		# iframe
		################################################################################################################

		updateIframeCode: ->

			simple_mode = if @$scope.iframe_options.simple_mode then 'true' else 'false'
			initial_load_path = '/' + @$scope.iframe_options.initial_load

			code = """
				<!-- DeskPRO Helpdesk Embed -->
				<div id="dp_helpdesk" style="display: none;"></div>
				<script type="text/javascript">
					var DpHelpdesk_Options = DpHelpdesk_Options || {};
					DpHelpdesk_Options.simpleMode = #{simple_mode};
					DpHelpdesk_Options.loadPath = '#{initial_load_path}';

					/**
					 * The Language ID to load for users with no language preference
					 */
					DpHelpdesk_Options.languageId = 0;

					DpHelpdesk_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
					DpHelpdesk_Options.deskproUrl = DpHelpdesk_Options.protocol + '://';
					DpHelpdesk_Options.initialHeight = 700;
					DpHelpdesk_Options.containerId = 'dp_helpdesk';

					if (document.getElementsByTagName) {
						(function() {
							var scr   = document.createElement('script');
							scr.type  = 'text/javascript';
							scr.async = true;
							scr.src   = DpHelpdesk_Options.protocol + '://javascripts/DeskPRO/User/HelpdeskWidget/HelpdeskWidget.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
						})();
					}
				</script>
				<!-- /DeskPRO Helpdesk Embed -->
			"""

			@$scope.code_snippets.iframe = code

	Admin_Portal_Ctrl_Embeds.EXPORT_CTRL()