define ->
	Admin_Main_Directive_DpDevBar = [ ->
		return {
			restrict: 'E',
			replace: true,
			template: """
				<div style="position: relative">
					<div id="dp_dev_bar_btn">
						<button><i class="fa fa-flask"></i> Devbar</button>
					</div>
					<div id="dp_dev_bar" style="display:none;">
						<button class="trigger_reloadcss"><i class="fa fa-eye"></i> Reload CSS</button>
						<button class="trigger_reloadpage"><i class="fa fa-refresh"></i> Reload Page</button>
						<button class="trigger_close"><i class="fa fa-times-circle"></i></button>
					</div>
				</div>
			""",
			link: (scope, element, attrs) ->
				element.find('#dp_dev_bar_btn').on('click', (ev) ->
					element.find('#dp_dev_bar_btn').hide();
					element.find('#dp_dev_bar').show();
				)

				element.find('.trigger_close').on('click', (ev) ->
					element.find('#dp_dev_bar_btn').show();
					element.find('#dp_dev_bar').hide();
				)

				element.find('.trigger_reloadcss').on('click', (ev) ->
					ev.preventDefault();
					qs = '?reload=' + new Date().getTime();
					$('link[rel="stylesheet"]').each(->
						this.href = this.href.replace(/\?.*|$/, qs);
					);
				)

				element.find('.trigger_reloadpage').on('click', (ev) ->
					ev.preventDefault();
					window.location.reload(false)
				)
			}
	]

	return Admin_Main_Directive_DpDevBar