define ->
	###
  #
  ###
	DeskPRO_Directive_DpTicketQuickActions = ($timeout, PersonService, AgentTeamService) ->

		options =
			preview_text_height: 62   # 4 lines
			widget_hide_delay: 200    # ms

		return {
			restrict: 'E'
			scope: {}
			replace: true
			template: """
				<div class="dp-stickytip">
					<header>
						<cite>
							<img src="{{ icon }}" />
							<span>{{ name }}</span>
							<span>{{ status }}</span>
							<span>{{ time }}</span>
						</cite>
					</header>
					<article><div class="preview-text"></div></article>
					<footer>
						<ul class="actions">
							<li ng-repeat="action in actions" style="position: relative;">
								<a href="#" ng-click="$event.preventDefault(); action.select2 && showDropdown(action, $event) || handleAction(action)">{{ action.title }}<i ng-if="action.select2" class="fa fa-caret-down"></i></a>
								<div ng-if="action.select2" ng-show="action === visible" style="position: absolute; width: 250px; bottom: -28px; left: -1px;">
									<input type="hidden" ui-select2="action.select2" ng-model="action.model" style="width: 100%;" ng-change="handleAction(action)" />
								</div>
							</li>
						</ul>
					<footer>
				</div>
			"""
			controller: ($scope, $filter, $http) ->
				$scope.actions = []
				me = $scope.$root.app_person_id

				format = (state) ->
					return state.text if !state.id
					"<img src='#{state.picture_url}' style='vertical-align: middle' /> <span style='display:inline-block;vertical-align: middle'>#{state.text}</span></div>"

				agentsSelectOptions =
					query: (query) ->
						PersonService.find(query.term).then (res) ->
							res.map (entry) -> entry.text = entry.display_name
							data = {results: res}
							query.callback data
					formatResult: format
					formatSelection: format
					escapeMarkup: (m) -> m

				teamsSelectOptions =
					query: (query) ->
						AgentTeamService.find(query.term).then (res) ->
							res.map (entry) -> entry.text = entry.name
							data = {results: res}
							query.callback data
					formatResult: format
					formatSelection: format
					escapeMarkup: (m) -> m

				$scope.setTicket = (ticket) ->
					t = ticket

					# update scope vars
					$scope.icon = t.previews[0].person.picture_url_16
					$scope.name = t.previews[0].person.display_name
					$scope.status = t.previews[0].message.status
					$scope.time = $filter('formatTimestampAgo')(t.previews[0].message.date_created_ts)

					$scope.actions.length = 0
					$scope.ticket_id = t.id

					# if locked by other agent or no actions allowed
					return if !ticket.actions_allowed?.length || (t.locked_by_agent && t.locked_by_agent.id != me)

					# append actions
					isAllowed = (action) ->
						return -1 != ticket.actions_allowed.indexOf(action)

					if isAllowed('assign_self') && (!t.agent || t.agent.id != $scope.$root.app_person_id)
						$scope.actions.push {title: 'Assign Me', params: {agent_id: me} }

					if isAllowed('assign_agent') && t.agent
						$scope.actions.push {title: 'Unassign', params: {agent_id: 0} }

					if isAllowed('assign_agent')
						$scope.actions.push {title: 'Assign Agent', prop: 'agent_id', params: {}, select2: agentsSelectOptions }

					if isAllowed('assign_team')
						$scope.actions.push {title: 'Assign Team', prop: 'agent_team_id', params: {}, select2: teamsSelectOptions}

					if isAllowed('set_awaiting_user') && 'awaiting_user' != t.status
						$scope.actions.push {title: 'Set Awaiting User', params: {status: 'awaiting_user', hidden_status: false}}

					if isAllowed('set_awaiting_agent') && 'awaiting_agent' != t.status
						$scope.actions.push {title: 'Set Awaiting Agent', params: {status: 'awaiting_agent', hidden_status: false}}

					if isAllowed('set_resolved') && 'resolved' != t.status
						$scope.actions.push {title: 'Set Resolved', params: {status: 'resolved', hidden_status: false}}

					$scope.updateWidth()

				$scope.handleAction = (action) ->
					return if action.select2 && !action.model
					if action.model then action.params[action.prop] = action.model.id

					$http.post(BASE_URL + "agent/tickets/#{$scope.ticket_id}/ajax-save-actions", {actions: action.params}).success () ->
						window.DeskPRO_Window.getMessageChanneler().poller.send();
					$scope.$root.$emit 'tickets.quick_actions.hide'



			link: ($scope, $el) ->
				# init
				$el.hide()
				promise = null
				$preview = $el.find('.preview-text').first()
				$preview.dotdotdot {elipsis: '...', wrap: 'word', height: options.preview_text_height}
				$actions = $el.find 'footer > ul.actions:first'

				# custom mask used to take clicks because the default select2 prevents
				# event bubbling that we need to detemine if a click happened on the overlay or outside of it
				$mask = $('<div></div>').addClass('select2-drop-mask').css({bottom: 0, right: 0}).hide().appendTo('body')

				isOpenSelect = false
				isClicked = false

				$mask.on 'click', (e) ->
					$('#select2-drop-mask').trigger('mousedown', e)

				$el.on 'click', -> isClicked = true

				$scope.updateWidth = () ->
					$el.css 'max-width', '650px'
					$timeout (-> $el.css 'max-width', $actions.outerWidth(true) + 'px'), 1

				showTimeout = null

				# events
				$scope.$root.$on 'tickets.quick_actions.show', (angularEvent, e, ticket, delay) ->
					isClicked = false
					promise && $timeout.cancel promise
					return if !ticket?.previews?.length

					if showTimeout then $timeout.cancel(showTimeout)
					showTimeout = null

					showTimeout = $timeout(->
						$scope.setTicket ticket
						$preview.text(ticket.previews[0].message?.preview_text)

						$el.show().css('visibility', 'hidden')
						offset = $(e.target).offset()
						offset.top += $(e.target).height()

						$el.css offset

						$timeout(->
							if (offset.top + $el.outerHeight()) > $(window).height()
								offset.top -= $el.outerHeight() + $(e.target).height()
								$el.css offset

							$el.css('visibility', 'visible')
						)
					, delay)

				$scope.showDropdown = (action, $event) ->
					$scope.visible = action

					# open dropdown
					$input = $($event.target).next().children('input')
					$timeout(
						->
							$input.off 'select2-open'
							$input.off 'select2-close'
							$input.on 'select2-open', ->
								promise && $timeout.cancel promise
								isOpenSelect = true
								isClicked = false
								$(document).on 'mousemove.quick-actions-select2', '#select2-drop-mask, #select2-drop', (e) ->
									$el.trigger e

								$('#select2-drop-mask').hide();
								$mask.show()

							$input.on 'select2-close', (e) ->
								promise && $timeout.cancel promise
								isOpenSelect = false
								$mask.hide()
								$(document).off 'mousemove.quick-actions-select2'
								$(document).off 'click.quick-actions-select2'
								$scope.visible = null

								# If clicked outside of the element, then it should
								# close the overlay
								$timeout(-> !isClicked && $el.trigger 'mouseleave', 10)

							$input.select2 'open'
						1
					) if $input.length

				$scope.$root.$on 'tickets.quick_actions.hide', ->
					$el.trigger 'mouseleave'
					if showTimeout then $timeout.cancel(showTimeout)
					showTimeout = null

				$el.on 'mousemove', (e) ->
					promise && $timeout.cancel promise

				$el.on 'mouseleave', (e) ->
					promise = $timeout (=>
						!isOpenSelect && $el.hide()
						isClicked = false
					), options.widget_hide_delay
		}
