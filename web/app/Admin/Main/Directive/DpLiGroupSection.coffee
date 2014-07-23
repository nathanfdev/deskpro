define ->
	Admin_Main_Directive_DpLiGroupSection = [ '$timeout', ($timeout) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				mainList = element.closest('ul')
				element.addClass('group-section')

				contentEls = element.find('.group-section-content')

				a = element.find('a.toggle').first();
				a.on('click', ->
					mode = if element.hasClass('group-open') then 'close' else 'open'

					if mode == 'open'
						mainList.find('li.group-section.group-open').each(->
							$(this).removeClass('group-open').find('.group-section-content').addClass('with-no-height')
						)

						element.addClass('group-open')
						contentEls.removeClass('with-no-height')
					else
						element.removeClass('group-open')
						contentEls.addClass('with-no-height')
				)
		}
	]

	return Admin_Main_Directive_DpLiGroupSection