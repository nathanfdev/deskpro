var PortalAdmin = {
    init: function() {

		var self = this;
		var h = $('body').outerHeight();
		this.tellAdmin('loaded', {
			height: h
		});

		//----------------------------------------
		// Header/footer editing
		//----------------------------------------

		this.injectControlsInto($('#dp_custom_header_wrap'));

		$('#dp_custom_header, #dp_custom_header_placeholder').click(function() {
			self.tellAdmin('open_header_editor');
		});

		$('#dp_custom_header, #dp_custom_header_placeholder').click(function() {
			self.tellAdmin('open_footer_editor');
		});

		//----------------------------------------
		// Content blocks
		//----------------------------------------

		this.contentCol = $('#dp_content');

		this.contentBlocks = $('.dp-content-block', this.contentCol);

		this.contentBlocks.each(function() {
			var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span>toggle</span></li><li class="dp-edit"><span>edit</span></li></div>');
			$(this).prepend(controls);
			$(this).append('<div class="dp-drag-overlay" />');

			if ($(this).height() > 250) {
				$(this).addClass('dp-height-collapse');
				$(this).append('<div class="dp-height-collapse-expand"></div><em class="dp-expand-block">Show entire block</em><em class="dp-collapse-block">Collapse block</em>');
			}
        });

		this.contentCol.delegate('.dp-toggle-block', 'click', function() {
			var block = $(this).closest('.dp-content-block');
			block.toggleClass('disabled');
		});

		this.contentCol.delegate('.dp-expand-block, .dp-collapse-block', 'click', function() {
				var block = $(this).closest('.dp-content-block');
				block.toggleClass('expanded');
			});

		this.contentCol.sortable({
			items: '> .dp-content-block',
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
				$(this).height($(this).height());
			}
		});


		//----------------------------------------
		// Nav blocks
		//----------------------------------------

		this.sideCol = $('#dp_sidebar');

		this.sideBlocks = $('.dp-sidebar-block', this.sideCol);

		this.sideBlocks.each(function() {
			var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span>toggle</span></li><li class="dp-edit"><span>edit</span></li></div>');
			$(this).prepend(controls);
			$(this).append('<div class="dp-drag-overlay" />');

			if ($(this).height() > 250) {
				$(this).addClass('dp-height-collapse');
				$(this).append('<div class="dp-height-collapse-expand"></div><em class="dp-expand-block">Show entire block</em><em class="dp-collapse-block">Collapse block</em>');
			}
		});

		this.sideCol.delegate('.dp-toggle-block', 'click', function() {
			var block = $(this).closest('.dp-sidebar-block');
			block.toggleClass('disabled');
		});

		this.sideCol.delegate('.dp-expand-block, .dp-collapse-block', 'click', function() {
			var block = $(this).closest('.dp-sidebar-block');
			block.toggleClass('expanded');
		});

		this.sideCol.sortable({
			items: '> .dp-sidebar-block',
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
			}
		});
    },

	injectControlsInto: function(wrapperEl) {
		var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span>toggle</span></li><li class="dp-edit"><span>edit</span></li></div>');
		wrapperEl.prepend(controls);
		wrapperEl.append('<div class="dp-drag-overlay" />');
	},

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
			case 'ideas':
				var e = $('.dp-content-block.dp-ideas-page, .dp-sidebar-block.dp-ideas-block').hide();
				if (is_enabled) e.show(); else e.hide();
				break;
		}
	},

	acceptMessage: function(id, data) {
		switch (id) {
			case 'app_enabled':
				this.changeAppVisibility(data.name, true);
				break;
			case 'app_disabled':
				this.changeAppVisibility(data.name, false);
				break;
			case 'header_updated':
				$('#dp_custom_header_placeholder').hide();
				$('#dp_custom_header').empty().html(data.html);
				$('#dp_custom_header_wrap').show();
				break;
		}
	},

	tellAdmin: function(id, data) {
		if (window.parent && window.parent.PortalEditor) {
			window.parent.PortalEditor.acceptMessage(id, data);
		}
	},

	callAdmin: function(id, data) {
		if (window.parent && window.parent.PortalEditor) {
			return window.parent.PortalEditor[id](data);
		}
	}
};

$(document).ready(function() {
    PortalAdmin.init();
});
