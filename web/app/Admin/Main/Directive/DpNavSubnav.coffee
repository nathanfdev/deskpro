define ->
	Admin_Main_Directive_DpNavSubnav = ['$rootScope', '$state', ($rootScope, $state) ->
		return {
			restrict: 'A',
			link: (scope, element, attrs) ->
				$parent = element.parent();
				$toggler = $parent.find('> a');
				$toggler.on('click', (ev) ->
					ev.preventDefault();
					ev.stopPropagation();

					if $parent.hasClass('sublist-open')
						$parent.removeClass('sublist-open')
						element.slideUp();
					else
						$parent.addClass('sublist-open')
						element.slideDown();

				)
				return
		}
	]

	return Admin_Main_Directive_DpNavSubnav