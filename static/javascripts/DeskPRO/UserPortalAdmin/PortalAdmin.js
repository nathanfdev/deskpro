var PortalAdmin = {
    init: function() {

		//----------------------------------------
		// Content blocks
		//----------------------------------------

		this.contentCol = $('#dp_content');

		this.contentBlocks = $('.dp-content-block', this.contentCol);

		this.contentBlocks.each(function() {
			var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span>toggle</span></li><li class="dp-edit"><span>edit</span></li><li class="dp-drag-handle"><span>move</span></li></div>');
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
			handle: '.dp-drag-handle, .dp-drag-overlay',
			opacity: 0.7,
			zIndex: 1000,
			cursor: 'move',
			appendTo: 'body',
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
			var controls = $('<div class="dp-block-controls"><ul><li class="dp-toggle-block"><span>toggle</span></li><li class="dp-edit"><span>edit</span></li><li class="dp-drag-handle"><span>move</span></li></div>');
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
			handle: '.dp-drag-handle, .dp-drag-overlay',
			opacity: 0.7,
			zIndex: 1000,
			cursor: 'move',
			appendTo: 'body',
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
    }
};

$(document).ready(function() {
    PortalAdmin.init();
});
