Orb.createNamespace('DeskPRO.Agent.Widget');

DeskPRO.Agent.Widget.AgentSelector = new Orb.Class({

	Implements: [Orb.Util.Options, Orb.Util.Events],

	initialize: function(options) {
		this.options = {
			agentList: null,
			multipleChoice: false,
			zIndex: 10000
		};

		this.setOptions(options);

		this.previousSelection = '';
	},

	_initWrapper: function() {
		if (this.wrapper) return;

		this.backdrop = $('<div class="backdrop"></div>');
		this.backdrop.click(this.close.bind(this));
		
		this.wrapper = $('<div class="field-overlay agent-selector" style="display:none;"><div class="close-trigger"></div></div>');

		this.filter = $('<div class="filter"><input type="text" style="width:100%; value="" /></div>').appendTo(this.wrapper);

		var isMulti = this.options.multipleChoice;

		var agentList = $('<ul />');
		$('li', this.options.agentList).each(function() {
			var li = $(this);
			var agentId = li.data('agent-id');
			var agentName = $('a:first', li).text();
			var image = $('img:first', li);

			var newLi = $('<li class="agent-"' + agentId + '" data-agent-id="' + agentId + '" />');

			if (image.length) {
				var imgContainer = $('<div class="avatar" />');
				image.clone().appendTo(imgContainer);
				imgContainer.appendTo(newLi);
			}

			var nameContainer = $('<div class="name" />');
			nameContainer.text(agentName);
			nameContainer.appendTo(newLi);

			var choiceContainer = $('<div class="choice" />');
			if (isMulti) {
				var choice = $('<input type="checkbox" name="agents[]" value="'+agentId+'" class="agent-choice-' + agentId + '" />"');
			} else {
				var choice = $('<input type="radio name="agents[]" value="'+agentId+'" class="agent-choice-' + agentId + '" />"');
			}
			choice.appendTo(choiceContainer);
			choiceContainer.appendTo(newLi);

			newLi.appendTo(agentList);
		});

		this.agentList = agentList;

		var self = this;
		$('input[type="checkbox"], input[type="radio"]').click(function() {
			var agentId = $(this).val();
			var checked = $(this).is(':checked');

			var eventData = {
				agentSelector: self,
				agentId: agentId,
				checked: checked
			};

			self.fireEvent('selectionClick', [eventData]);
		});

		agentList.appendTo(this.wrapper);

		this.wrapper.appendTo('body');

		this.wrapperWidth = this.wrapper.outerWidth();
		this.wrapperHeight = this.wrapper.outerHeight();

		var eventData = {
			agentSelector: this,
			wrapper: this.wrapper
		};

		this.fireEvent('initWrapper', [eventData]);
	},

	open: function(event) {

		this._initWrapper();

		var target = $(event.target);

		var width = this.wrapperWidth;
		var height = this.wrapperHeight;

		var pageWidth = $(document).width();
		var pageHeight = $(document).height();

		var pageX = target.offset().top;
		var pageY = target.offset().left;

		// Determine which way to open the menu,
		// We do this so the menu doesn't go off-screen if
		// its near the edge
		if (pageX+width < pageWidth) {
			var left = pageX+4;
		} else {
			var left = pageX - width - 4;
		}

		if (pageY+height < pageHeight) {
			var top = pageY;
		} else {
			var top = pageY - height + 4;
		}

		if (top < 0) {
			top = 5;
		}

		this.backdrop.show();
		this.wrapper.addClass('open');
		this.wrapper.css({
			'z-index': this.options.zIndex,
			'position': 'absolute',
			'top': top,
			'left': left,
			'display': 'block'
		});

		var eventData = {
			agentSelector: this,
			event: event
		};

		this.fireEvent('open', [eventData]);
	},

	close: function() {

		if (!this.wrapper.is('.open')) return;

		var eventData = {
			agentSelector: this,
			event: event,
			cancelClose: false
		};

		this.fireEvent('beforeClose', [eventData]);

		if (eventData.cancelClose) {
			return;
		}

		delete eventData.cancelClose;

		this.backdrop.hide();
		this.wrapper.hide().removeClass('open');

		this.fireEvent('close', [eventData]);

		var selectionString = this.getSelection();
		if (this.options.multipleChoice) {
			selectionString = selectionString.join(',');
		}

		if (this.previousSelection != selectionString) {
			eventData.selection = this.getSelection();
			this.fireEvent('selectionChanged', [eventData]);

			this.previousSelection = selectionString;
		}
	},

	isOpen: function() {
		return this.wrapper.is('.open');
	},

	getWrapper: function() {
		return this.wrapper;
	},

	getSelection: function() {
		if (this.options.multipleChoice) {
			var ids = [];
			$('input[type="checkbox"]:checked', this.agentList).each(function() {
				ids.push($(this).val());
			});

			return ids;

		} else {
			var id = $('input[type="checkbox"]:checked:first', this.agentList).val();
			return id;
		}
	}
});