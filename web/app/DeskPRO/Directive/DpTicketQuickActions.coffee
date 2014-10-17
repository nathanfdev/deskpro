define ->
	###
  #
  ###
	DeskPRO_Directive_DpTicketQuickActions = ($timeout) ->

		options =
			preview_text_height: 62   # 4 lines
			widget_hide_delay: 200    # ms

		return {
			restrict: 'E'
			scope: {}
			replace: true
			template: '
					<div class="dp-stickytip">
					    <div class="previewtext-row">
					        <cite>
					            <img src="{{ icon }}" />
					            <span>{{ name }}</span>
					            <span>{{ status }}</span>
					            <span>{{ time }}</span>
					        </cite>
					        <br />
					        <span></span>
					    </div>
					    <ul class="previewtext-row actions">
									<li ng-repeat="action in actions">
											<a href="#" ng-click="$event.preventDefault(); handleAction(action)">{{ action.title }}</a>
									</li>
							</ul>
							<input type="hidden" style="width: 200px;position: absolute;bottom: -30px;" />
					</div>
			'
			controller: ($scope, $filter, PersonService, $http) ->
				$scope.actions = []
				me = $scope.$root.app_person_id

				$scope.setTicket = (ticket) ->

					service = PersonService
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
						$scope.actions.push {title: 'Assign Me', params: {agent_id: me}}

					if isAllowed('assign_agent')
						$scope.actions.push {title: 'Assign Agent', params: {agent_id: null}}

					if isAllowed('assign_team')
						$scope.actions.push {title: 'Assign Team', params: {agent_team_id: null}}

					if isAllowed('set_awaiting_user') && 'awaiting_user' != t.status
						$scope.actions.push {title: 'Set Awaiting User', params: {status: 'awaiting_user', hidden_status: false}}

					if isAllowed('set_awaiting_agent') && 'awaiting_agent' != t.status
						$scope.actions.push {title: 'Set Awaiting Agent', params: {status: 'awaiting_agent', hidden_status: false}}

					if isAllowed('set_resolved') && 'resolved' != t.status
						$scope.actions.push {title: 'Set Resolved', params: {status: 'resolved', hidden_status: false}}


				$scope.handleAction = (action) ->

					callback = () ->
						$http.post("/agent/tickets/#{$scope.ticket_id}/ajax-save-actions", {actions: action.params}).success () ->
							window.DeskPRO_Window.getMessageChanneler().poller.send();
						$scope.$root.$emit 'tickets.quick_actions.hide'


					switch true
						when action.params.status? || action.params.agent_id == me
							console.info 'set status or assign self'
							callback()

						when null == action.params.agent_id
							console.info 'assign agent'
							# show agents dropdown
							1

						when null == action.params.agent_team_id
							console.info 'assign team'
						# show teams dropdown
							1



			link: ($scope, $el) ->

				# init
				$el.hide()
				promise = null
				$preview = $el.find '.previewtext-row > span:eq(0)'
				$preview.dotdotdot {elipsis: '...', wrap: 'word', height: options.preview_text_height}
				$select2 = $el.find('input').select2 {data: [{id:0, text: 'test'}]}


				# events
				$scope.$root.$on 'tickets.quick_actions.show', (angularEvent, e, ticket) ->
					promise && $timeout.cancel promise
					return if !ticket?.previews?.length

					# show and update position
					$el.show()
					offset = $(e.target).offset()
					offset.top += $(e.target).height()
					$el.css offset

					DP_DEBUG && console.time 'bind quick actions data'
					$scope.setTicket ticket
					DP_DEBUG && console.timeEnd 'bind quick actions data'

					DP_DEBUG && console.time 'update quick actions preview text'
					$preview.text(ticket.previews[0].message?.preview_text).trigger 'update'
					DP_DEBUG && console.timeEnd 'update quick actions preview text'

				$scope.$root.$on 'tickets.quick_actions.hide', ->
					$el.trigger 'mouseleave'

				$el.on 'mousemove', (e) ->
					promise && $timeout.cancel promise

				$el.on 'mouseleave', (e) ->
					console.info 'leave'
					promise = $timeout (=> $el.hide()), options.widget_hide_delay


				console.info $select2.data 'select2'
#				select2 hacks

				$(document).on 'mousemove', '#select2-drop-mask', (e) ->
					$el.trigger e
		}
