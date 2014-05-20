define ['AdminStart/Ctrl/StartBase'], (StartBase) ->
	class AdminStart_Ctrl_Finish extends StartBase
		@CTRL_ID = 'AdminStart_Ctrl_Finish'

		init: ->
			@done_set = true
			@set_prom = @Api.sendPost('/start-settings/set-initial').success(=>
				@done_set = true
			)
			return

		goAdmin: (ev, el) ->
			if not @done_set
				ev.preventDefault()
				@set_prom.success(=>
					el.click()
				)

	AdminStart_Ctrl_Finish.EXPORT_CTRL()