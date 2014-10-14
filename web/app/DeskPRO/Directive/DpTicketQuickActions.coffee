define ->
	###
    #
    ###
	DeskPRO_Directive_DpTicketQuickActions = ['$parse', 'formatTimestampAgoFilter', '$timeout', ($parse, formatTimestampAgo, $timeout) ->

		options =
			preview_text_height: 62   # 4 lines
			widget_hide_delay: 200    # ms


		# todo better to create widget's directive with it own controller
		# so we need one main directive with compiled template above, which would show current ticket details
		# and multiple lightweight directives to set main directive's ticket context (on hover)
		# also we need a shared ticket service to get available actions and trigger them on click
		promise = null
		$widget = $('
				<div class="dp-stickytip">
						<div class="previewtext-row">
                <cite>
                    <img />
										<span>person name</span>
										<span>action (created/replied/wrote note)</span>
										<span>time ago</span>
                </cite>
                <br />
                <span>preview text</span>
            </div>
            <ul class="previewtext-row actions">
                <li><a href="#" data-type="assign-me">Assign Me</a></li>
								<li><a href="#" data-type="assign-agent">Assign Agent</a></li>
								<li><a href="#" data-type="assign-team">Assign Team</a></li>
								<li><a href="#" data-type="set-awaiting-user">Set Awaiting User</a></li>
								<li><a href="#" data-type="set-resolved">Set Resolved</a></li>
            </ul>
				</div>
		')
		.on 'mousemove', =>
			promise && $timeout.cancel promise
		.on 'mouseleave', =>
			promise = $timeout (=> $widget.hide()), options.widget_hide_delay
		.on 'click', '.actions a', (e) ->
			e.stopPropagation();
			e.preventDefault();



		.hide().appendTo('body')


		elements =
			icon: $widget.find('cite > img:eq(0)')[0]
			name: $widget.find('cite > span:eq(0)')[0]
			status: $widget.find('cite > span:eq(1)')[0]
			time: $widget.find('cite > span:eq(2)')[0]
			msg: $widget.find('div > span:eq(0)').dotdotdot({elipsis: '...', wrap: 'word', height: options.preview_text_height})




		return {
			restrict: 'A'
			scope: false
			replace: false
			link: ($scope, $el, attr) ->
				ticket = $parse(attr.dpTicketQuickActions)($scope)
				return if !ticket || !ticket.previews.length

				$el.on 'mouseover', =>
					promise && $timeout.cancel promise
					$widget.show().css({left: $el.offset().left, top: $el.offset().top + $el.height()})

					DP_DEBUG && console.time('Ticket quick actions render')
					elements.icon.src = ticket.previews[0].person.picture_url_16
					elements.name.innerText = ticket.previews[0].person.display_name
					elements.status.innerText = ticket.previews[0].message.status
					elements.time.innerText = formatTimestampAgo(ticket.previews[0].message.date_created_ts)
					elements.msg.text(ticket.previews[0].message.preview_text).trigger('update')
					DP_DEBUG && console.timeEnd('Ticket quick actions render')


				$el.on 'mouseleave', (e) => $widget.trigger 'mouseleave'
		}
	]
