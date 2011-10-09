function DpDevbar() {
	$('#dp_devbar').click(function(ev) {
		ev.stopPropagation();
	});
	$('#dp_devbar .reload-active-tab').click(function(ev) {
		ev.preventDefault();
		DeskPRO_Window.reloadSelectedTab();
	});
};

var devbar = new DpDevbar();
