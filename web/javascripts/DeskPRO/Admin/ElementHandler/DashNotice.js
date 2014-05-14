Orb.createNamespace('DeskPRO.Admin.ElementHandler');

DeskPRO.Admin.ElementHandler.DashNotice = new Orb.Class({
	Extends: DeskPRO.ElementHandler,

	init: function() {
		var el = this.el;
		$.ajax({
			url: BASE_URL + 'admin/dashboard/load-version-notice.html',
			dataType: 'html',
			cache: false,
			success: function(html) {
				var fn, timeout;
				if (html) {
					fn = function() {
						html = html.replace('%DP_BASE_URL%', window.BASE_URL.replace(/\/$/, ''));
						el.html(html);
						var replaces = el.find('.replace-version');
						if (replaces[0]) {
							$('#version_info_expanded').html('')
							replaces.each(function() {
								var me = $(this);
								me.closest('.dp-page-box').remove();
								me.detach();
								$('#version_info_expanded').append(me);
							});
						}
						el.show();
					};

					if (window.dpHasVersionInfo) {
						fn();
					} else {
						timeout = window.setInterval(function() {
							if (window.dpHasVersionInfo) {
								window.clearInterval(timeout);
								fn();
							}
						}, 100);
					}
				}
			}
		});
	}
});
