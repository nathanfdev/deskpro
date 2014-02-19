define ->
	###
    # Description
    # -----------
    #
    # This should be applied to the body portion of a tabbed interface. When the dp-tab-btn with this ID
    # is enabled, this body is displayed and others are removed.
    #
    # See dp-tab-btn for a full example.
    #
    # Example View
    # ------------
    # <section dp-tab-body="edit.main">...</section>
    ###
	DeskPRO_Directive_DpTabBody = [ ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				if not scope.dp_tab_ids
					scope.dp_tab_ids = {}

				id_segs = attrs['dpTabBody']
				if not id_segs
					return

				id_segs   = id_segs.split('.')
				tab_val   = id_segs.pop()
				tab_group = id_segs.join('.')

				if scope.dp_tab_ids[tab_group] == tab_val
					element.show()
				else
					element.hide()

				scope.$watch(->
					return scope.dp_tab_ids[tab_group]
				, (newVal) ->
					if newVal == tab_val
						element.show()

						# If the ace editor is display:none (eg hidden tab) when the view
						# is loaded, then its possible it may be blank when trying to load it.
						# This is a workaround to the bug that refreshes the ui when the tab becomes
						# active.
						element.find('.with-ace-editor').each(->
							editor = $(this).data('ace-editor')
							editor.renderer.updateFull()
						)
					else
						element.hide()
				)
		}
	]

	return DeskPRO_Directive_DpTabBody
