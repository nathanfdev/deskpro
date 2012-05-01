$(document).ready(function() {
    PortalAdmin.init();
});

var PortalAdmin = {
    init: function() {

		var self = this;

		//----------------------------------------
		// Logo
		//----------------------------------------

		var header = $('#dp_header');
		var headerCtrl = new PortalAdmin_SimpleHeader(header);
		header.data('portal-ctrl', headerCtrl);

		//----------------------------------------
		// Placeholder editing
		//----------------------------------------

		$('.dp-portal-placeholder').each(function() {
			var pp = new PortalAdmin_Placeholder(this);
			$(this).data('portal-place-ctrl', pp);
		});

		//----------------------------------------
		// Content sections
		//----------------------------------------

		this.contentCol = $('#dp_content');
		this.sideCol = $('#dp_sidebar');

		//this.initBlocks(this.contentCol, '.dp-content-block');
		this.initBlocks(this.sideCol, '.dp-sidebar-block');

		//----------------------------------------
		// Alert admin that we're ready
		//----------------------------------------

		var oldHeight = $('body').outerHeight();
		this.tellAdmin('loaded', {
			height: oldHeight
		});

		window.setInterval(function() {
			var h = $('body').outerHeight();
			if (h != oldHeight) {
				self.tellAdmin('update_height', {
					height: h
				});
				oldHeight = h;
			}
		}, 300);
    },


	/**
	 * Init sortable/editable blocks within a container
	 *
	 * @param wrapper
	 * @param blockSelector
	 */
	initBlocks: function(wrapper, blockSelector) {
		var self = this;
		var contentBlocks = $(blockSelector, wrapper);

		contentBlocks.each(function() {
			var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span class="lbloff">OFF</span><span class="lblon">ON</span></li></div>');
			$(this).prepend(controls);
			$(this).append('<div class="dp-drag-overlay" />');

			if ($(this).height() > 250) {
				$(this).addClass('dp-height-collapse');
				$(this).append('<div class="dp-height-collapse-expand"></div><em class="dp-expand-block">Show entire block</em><em class="dp-collapse-block">Collapse block</em>');
			}
		});

		wrapper.on('click', '.dp-toggle-block', function() {
			var block = $(this).closest('.dp-content-block, .dp-sidebar-block');
			block.toggleClass('disabled');

			var pid = $(this).closest('.dp-p').data('dp-pid');
			if (pid) {
				self.tellAdmin('block_toggled', { pid: pid, enabled: !block.hasClass('disabled') });
			}
		});

		wrapper.on('click', '.dp-expand-block, .dp-collapse-block', function() {
			var block = $(this).closest('.dp-content-block, .dp-sidebar-block');
			block.toggleClass('expanded');

			var pid = $(this).closest('.dp-p').data('dp-pid');
			if (pid) {
				self.tellAdmin('block_toggled', { pid: pid, enabled: !block.hasClass('disabled') });
			}
		});

		// Init to current state
		$('.dp-p-disabled', wrapper).each(function() {
			var block = $(this).find('.dp-content-block, .dp-sidebar-block');
			block.toggleClass('disabled');
		});

		wrapper.sortable({
			items: '> .dp-p',
			handle: '.dp-drag-overlay',
			opacity: 0.7,
			zIndex: 1000,
			cursor: 'move',
			appendTo: '#deskpro',
			forcePlaceholderSize: true,
			refreshPositions: true,
			helper: function(event, el) {
				var helper = el.clone();
				$('.dp-block-controls', helper).remove();
				return helper;
			},
			create: function() {
				//$(this).height($(this).height());
			},
			update: function() {
				var ids = [];
				$('div.dp-p').each(function() {
					var pid = $(this).data('dp-pid');
					if (pid) {
						ids.push(pid);
					}
				});

				self.tellAdmin('update_orders', {orderedIds: ids});
			}
		});
	},

	/**
	 * Change visibility of specific app elements (ie if theyre disabled in the admin page)
	 *
	 * @param app
	 * @param is_enabled
	 */
	changeAppVisibility: function(app, is_enabled) {
		switch (app) {
			case 'kb':
				var e = $('.dp-content-block.dp-kb-page, .dp-sidebar-block.dp-kb-block').hide();
				if (is_enabled) e.show(); else e.hide();
				break;
			case 'downloads':
				var e = $('.dp-content-block.dp-downloads-page, .dp-sidebar-block.dp-downloads-block').hide();
				if (is_enabled) e.show(); else e.hide();
				break;
			case 'news':
				var e = $('.dp-content-block.dp-news-page, .dp-sidebar-block.dp-news-block').hide();
				if (is_enabled) e.show(); else e.hide();
				break;
			case 'feedback':
				var e = $('.dp-content-block.dp-feedback-page, .dp-sidebar-block.dp-feedback-block').hide();
				if (is_enabled) e.show(); else e.hide();
				break;
		}
	},


	/**
	 * Accept a message from the parent admin page
	 *
	 * @param id
	 * @param data
	 */
	acceptMessage: function(id, data) {
		switch (id) {
			case 'app_enabled':
				this.changeAppVisibility(data.name, true);
				break;
			case 'app_disabled':
				this.changeAppVisibility(data.name, false);
				break;
			case 'reload_css':
				var link = $('#dp_stylesheet');
				var newlink = link.clone();
				newlink.attr('href', newlink.attr('href') + '&' + (new Date()).getTime());

				link.remove();
				$('head').append(newlink);

				break;
			case 'header_updated':
				$('#dp_custom_header_placeholder').hide();
				$('#dp_custom_header').empty().html(data.html);
				$('#dp_custom_header_wrap').show();
				break;
		}
	},


	/**
	 * Send a message to the parent admin page
	 *
	 * @param id
	 * @param data
	 */
	tellAdmin: function(id, data) {
		if (window.parent && window.parent.PortalEditor) {
			window.parent.PortalEditor.acceptMessage(id, data);
		}
	},


	/**
	 * Call a method on the parent admin page
	 *
	 * @param id
	 * @param data
	 */
	callAdmin: function(id, data) {
		if (window.parent && window.parent.PortalEditor) {
			return window.parent.PortalEditor[id](data);
		}
	}
};

var PortalAdmin_SimpleHeader = new Orb.Class({
	initialize: function(header) {
		var  self = this;

		this.header = $(header);
		this.titleArea = $('#dp_header_title');

		var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span class="lbloff">OFF</span><span class="lblon">ON</span></li><li class="dp-edit"><span>edit</span></li></div>');
		this.header.prepend(controls);
		this.header.append('<div class="dp-drag-overlay" id="dp_header_portal_off_drag_overlay" />');

		var titleControls = $('<div class="dp-block-controls"><ul><li class="dp-edit"><span>edit</span></li></div>');
		this.titleArea.prepend(titleControls);

		titleControls.find('.dp-edit').on('click', function() {
			PortalAdmin.tellAdmin('open_portal_title', { controller: self });
		});

		this.header.on('click', '.dp-toggle-block', function() {
			self.header.toggleClass('disabled');
		});
		this.header.on('click', '.dp-edit', function() {
			PortalAdmin.tellAdmin('open_logo_editor', { controller: self });
		});

		var updateHeader = function() {
			if (self.header.hasClass('disabled')) {
				PortalAdmin.tellAdmin('disable_logo_area', { controller: this });
			} else {
				PortalAdmin.tellAdmin('enable_logo_area', { controller: this });
			}
		}

		$('#dp_header_portal_off').on('click', function() {
			self.header.toggleClass('disabled');
		});
		$('#dp_header_portal_off_drag_overlay').on('click', function() {
			if (self.header.hasClass('disabled')) {
				self.header.toggleClass('disabled');
			}
		});
	},

	/**
	 * Set the logo image url
	 *
	 * @param url
	 */
	setLogo: function(url) {
		if (!url) {
			this.header.toggleClass('disabled');
			return;
		}
		$('#dp_header img.logo').attr('src', url);
		$('#dp_header').addClass('dp-with-logo');
	},

	/**
	 * Sets the logo text
	 *
	 * @param title
	 * @param tagline
	 */
	setLogoText: function(title, tagline) {

		if (!title.length) {
			this.header.addClass('disabled');
			return;
		}

		$('#dp_header').removeClass('dp-with-logo');
		$('#dp_header h1').text(title || '');

		if (!tagline.length) {
			$('#dp_header h2').text('').hide();
		} else {
			$('#dp_header h2').text(tagline || '').show();
		}
	},

	setTitle: function(title) {
		$('#dp_header_title h1').text(title);
	}
});

var PortalAdmin_Placeholder = new Orb.Class({
	initialize: function(place) {
		var self = this;
		this.place = $(place);
		this.name = this.place.data('portal-block');
		this.id = this.place.attr('id');

		this.mode = this.place.data('mode') || 'placeholder';

		this.el = $(this.place.data('portal-for'));
		this.wrap = this.el.parent();

		var controls = $('<div class="dp-block-controls"><ul><li class="dp-remove-block"><span>reset</span></li><li class="dp-edit-html"><span>edit</span></li></div>');
		this.wrap.prepend(controls);
		this.wrap.append('<div class="dp-drag-overlay" style="cursor: default;" />');

		this.place.on('click', function() {
			PortalAdmin.tellAdmin('open_placeholder_editor', { controller: self });
		});

		$('.dp-edit-html', controls).on('click', function() {
			PortalAdmin.tellAdmin('open_placeholder_editor', { controller: self });
		});

		$('.dp-remove-block', controls).on('click', function() {
			if (confirm('Are you sure you want to delete the custom HTML you already have set?')) {
				self.clickReset();
			}
		});
	},

	clickReset: function() {
		PortalAdmin.tellAdmin('reset_placeholder', { controller: this });
		this.reset();
	},

	reset: function() {
		this.el.empty();
		this.wrap.hide();
		this.place.show();
	},

	update: function() {
		$.ajax({
			url: BASE_URL + 'admin-render-template/' + this.name,
			context: this,
			success: function(content) {
				this.setContent(content);
			}
		});
	},

	setContent: function(content) {
		DP.console.log("[Block %s] Set content", this.name);
		this.el.empty().html(content);

		if (this.mode == 'placeholder') {
			this.place.hide();
			this.wrap.show();
		}
	}
});
