define ['DeskPRO/Util/Strings', 'Admin/Main/Ctrl/Base'], (Strings, Admin_Ctrl_Base) ->
	class Admin_Portal_Ctrl_Embeds extends Admin_Ctrl_Base
		@CTRL_ID = 'Admin_Portal_Ctrl_Embeds'
		@CTRL_AS = 'Embeds'
		@DEPS    = ['Api']

		init: ->
			@$scope.type = @$stateParams.type;

			@$scope.code_snippets = {
				overlay: '',
				chat: '',
				form_frame: '',
				iframe: ''
			}

			@$scope.overlay_options = {
				title: 'Support & Feedback',
				position: 'left',
				lang_id: '0'
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

			@$scope.form_frame_options = {
				lang_id: '0',
				dep_id: '0'
			}

			@$scope.iframe_options = {
				simple_mode: true,
				initial_load: '',
				lang_id: '0'
			}

		initCode: ->
			@$scope.$watch('overlay_options', =>
				@updateWebsiteTabCode()
			, true)
			@$scope.$watch('chat_options', =>
				@updateChatCode()
			, true)
			@$scope.$watch('form_frame_options', =>
				@updateFormFrameCode()
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
			promise = @Api.sendDataGet({
				hdinfo:             '/deskpro/info',
				ticket_deps:        '/ticket_deps',
				langs:              '/langs',
				widget_selections:  '/widget/selections',
			}).then( (res) =>
				@hdinfo = res.data.hdinfo
				@langs = res.data.langs.languages
				@widget_selections = res.data.widget_selections

				selections = {
					articles: {}
					downloads: {}
					news: {}
				}

				if @widget_selections?.selections?.articles?
					for key in @widget_selections.selections.articles
						selections.articles[key] = true

				if @widget_selections?.selections?.downloads?
					for key in @widget_selections.selections.downloads
						selections.downloads[key] = true

				if @widget_selections?.selections?.news?
					for key in @widget_selections.selections.news
						selections.news[key] = true

				@widget_selections.selections = selections

				@deps = []

				for d in res.data.ticket_deps.departments
					if not d.has_children
						d.title = d.title_parts.join(' > ')
						@deps.push(d)

				@initCode()
			)
			return promise

		###
 	#
 	###

		updateSelections: ->

			inst = @$modal.open({
				templateUrl: @getTemplatePath('Portal/selections-modal.html'),
				controller: ['$scope', '$modalInstance',	'widget_selections', ($scope, $modalInstance, widget_selections) ->

						$scope.widget_selections = widget_selections

						$scope.confirm = ->
							$modalInstance.close($scope.widget_selections.selections)

						$scope.dismiss = ->
							$modalInstance.dismiss()
				],
				resolve: {
					widget_selections: =>
						return @widget_selections
				}
			});

			inst.result.then( (selections_data) =>

				postData = {
					articles: []
					downloads: []
					news: []
				}

				for key, value of selections_data.articles
					postData.articles.push(key) if value

				for key, value of selections_data.downloads
					postData.downloads.push(key) if value

				for key, value of selections_data.news
					postData.news.push(key) if value

				@Api.sendPostJson('/widget/selections', {selections: postData})
			)


		################################################################################################################
    	# Website Tab Code
		################################################################################################################

		updateWebsiteTabCode: ->
			btn_title = Strings.addslashes(@$scope.overlay_options.title || 'Support')
			btn_pos   = @$scope.overlay_options.position || 'left'
			lang_id   = @$scope.overlay_options.lang_id || 0

			code = """
				<!-- DeskPRO Widget -->
				<script type="text/javascript">
					var DpOverlayWidget_Options = DpOverlayWidget_Options || {};
					DpOverlayWidget_Options.phrase = '#{btn_title}';
					DpOverlayWidget_Options.tabLocation = '#{btn_pos}';
					DpOverlayWidget_Options.languageId = #{lang_id};
					DpOverlayWidget_Options.topPosition = '200px';
					DpOverlayWidget_Options.btnStyle = {
						bgColor: '#3F3F3F',
						border: '2px solid #727272',
						textColor: '#FFFFFF',
						textShadow: '0px 0px 2px #000000',
						font: 'bold 13px Arial, sans-serif'
					};

					DpOverlayWidget_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
					DpOverlayWidget_Options.deskproUrl = DpOverlayWidget_Options.protocol + ':#{@hdinfo.widget_url}';
					if (document.getElementsByTagName) {
						(function() {
							var scr   = document.createElement('script');
							scr.type  = 'text/javascript';
							scr.async = true;
							scr.src   = DpOverlayWidget_Options.protocol + ':#{@hdinfo.asset_url}javascripts/DeskPRO/User/WebsiteWidget/Overlay.js';
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

		################################################################################################################
		# Form Frame
		################################################################################################################

		updateFormFrameCode: ->
			lang_id = @$scope.form_frame_options.lang_id || 0
			dep_id  = @$scope.form_frame_options.dep_id || 0

			code = """
				<!-- DeskPRO Ticket Form -->
				<div id="dp_newticket_form" style="display: none;"></div>
				<script type="text/javascript">
				var DpNewTicket_Options = DpNewTicket_Options || {};
				DpNewTicket_Options.departmentId = #{dep_id};
				DpNewTicket_Options.languageId = #{lang_id};

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

				DpNewTicket_Options.containerId = 'dp_newticket_form';
				DpNewTicket_Options.initialHeight = 700;

				DpNewTicket_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
				DpNewTicket_Options.deskproUrl = DpNewTicket_Options.protocol + ':#{@hdinfo.widget_url}';
				if (document.getElementsByTagName) {
					(function() {
						var scr = document.createElement('script');
						scr.type = 'text/javascript';
						scr.async = true;
						scr.src = DpNewTicket_Options.protocol + ':#{@hdinfo.asset_url}javascripts/DeskPRO/User/TicketFormWidget/TicketFormWidget.js';
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

			simple_mode       = if @$scope.iframe_options.simple_mode then 'true' else 'false'
			initial_load_path = '/' + @$scope.iframe_options.initial_load
			lang_id           = @$scope.iframe_options.lang_id || 0

			code = """
				<!-- DeskPRO Helpdesk Embed -->
				<div id="dp_helpdesk" style="display: none;"></div>
				<script type="text/javascript">
					var DpHelpdesk_Options = DpHelpdesk_Options || {};
					DpHelpdesk_Options.simpleMode = #{simple_mode};
					DpHelpdesk_Options.loadPath = '#{initial_load_path}';
					DpHelpdesk_Options.languageId = #{lang_id};

					DpHelpdesk_Options.initialHeight = 700;
					DpHelpdesk_Options.containerId = 'dp_helpdesk';

					DpHelpdesk_Options.protocol = ('https:' == document.location.protocol ? 'https' : 'http');
					DpHelpdesk_Options.deskproUrl = DpHelpdesk_Options.protocol + ':#{@hdinfo.widget_url}';
					if (document.getElementsByTagName) {
						(function() {
							var scr   = document.createElement('script');
							scr.type  = 'text/javascript';
							scr.async = true;
							scr.src   = DpHelpdesk_Options.protocol + ':#{@hdinfo.asset_url}javascripts/DeskPRO/User/HelpdeskWidget/HelpdeskWidget.js';
							(document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(scr);
						})();
					}
				</script>
				<!-- /DeskPRO Helpdesk Embed -->
			"""

			@$scope.code_snippets.iframe = code

	Admin_Portal_Ctrl_Embeds.EXPORT_CTRL()