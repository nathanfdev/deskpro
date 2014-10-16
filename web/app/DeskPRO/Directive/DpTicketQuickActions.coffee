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
									<li ng-repeat="action in actions"><a href="#">{{ action.title }}</a></li>
							</ul>
					</div>
			'
			controller: ($scope, $filter, Person) ->
				$scope.actions = []
				me = $scope.$root.app_person_id

				$scope.setTicket = (ticket) ->

					service = Person
					t = ticket

					# update scope vars
					$scope.icon = t.previews[0].person.picture_url_16
					$scope.name = t.previews[0].person.display_name
					$scope.status = t.previews[0].message.status
					$scope.time = $filter('formatTimestampAgo')(t.previews[0].message.date_created_ts)
					$scope.text = t.previews[0].message.preview_text.substr 0, 1000 # reduce possible length

					$scope.actions.length = 0

					# if locked by other agent or no actions allowed
					return if !ticket.actions_allowed?.length || (t.locked_by_agent && t.locked_by_agent.id != me)

					# append actions
					isAllowed = (action) ->
						return -1 != ticket.actions_allowed.indexOf(action)

					if isAllowed('assign_self') && (!t.agent || t.agent.id != $scope.$root.app_person_id)
						$scope.actions.push {title: 'Assign Me', type: 'assign-agent', params: {id: me}}

					if isAllowed('assign_agent')
						$scope.actions.push {title: 'Assign Agent', type: 'assign-agent', params: {id: 1}}

					if isAllowed('assign_team')
						$scope.actions.push {title: 'Assign Team', type: 'assign-team', params: {id: 1}}

					if isAllowed('set_awaiting_user') && 'awaiting_user' != t.status
						$scope.actions.push {title: 'Set Awaiting User', type: 'set-status', params: {status: 'awaiting_user'}}

					if isAllowed('set_awaiting_agent') && 'awaiting_agent' != t.status
						$scope.actions.push {title: 'Set Awaiting Agent', type: 'set-status', params: {status: 'awaiting_agent'}}

					if isAllowed('set_resolved') && 'resolved' != t.status
						$scope.actions.push {title: 'Set Resolved', type: 'set-status', params: {status: 'resolved'}}


			link: ($scope, $el) ->

				# init
				$el.hide()
				promise = null
				$preview = $el.find '.previewtext-row > span:eq(0)'
				$preview.dotdotdot {elipsis: '...', wrap: 'word', height: options.preview_text_height}


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
					$preview.text($scope.text).trigger 'update'
					DP_DEBUG && console.timeEnd 'update quick actions preview text'

				$scope.$root.$on 'tickets.quick_actions.hide', ->
					$el.trigger 'mouseleave'

				$el.on 'mousemove', ->
					promise && $timeout.cancel promise

				$el.on 'mouseleave', ->
					promise = $timeout (=> $el.hide()), options.widget_hide_delay

				$el.on 'click', '.actions a', (e) ->
					e.preventDefault()
					e.stopPropagation()
		}
