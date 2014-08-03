define ['Admin/Main/Ctrl/Base', 'angular'], (Admin_Ctrl_Base, angular) ->
	class Admin_Icons_Ctrl_List extends Admin_Ctrl_Base
		@CTRL_ID   = 'Admin_Icons_Ctrl_List'
		@CTRL_AS   = 'Ctrl'
		@DEPS      = []

		init: ->
			@busy = false
			@categories = {}



		initialLoad: ->
			@initCategories()



		initCategories: ->

			@busy = true
			def = @$q.defer()

			@$timeout(
				=>
					for stylesheet in document.styleSheets
						continue if !stylesheet.href? || -1 == stylesheet.href.indexOf 'icons-style.css'

						for rule in stylesheet.rules
							continue if !rule.selectorText? || 0 != rule.selectorText.indexOf '.flaticon-'

							title = rule.selectorText.substr(10, rule.selectorText.length - 18)
#							console.log title

					@busy = false
					def.resolve()
				0
			)

			def.promise


	Admin_Icons_Ctrl_List.EXPORT_CTRL()