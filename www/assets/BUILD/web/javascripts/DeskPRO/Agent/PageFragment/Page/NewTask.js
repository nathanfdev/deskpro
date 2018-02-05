Orb.createNamespace('DeskPRO.Agent.PageFragment.Page');
DeskPRO.Agent.PageFragment.Page.NewTask = new Orb.Class({

	Extends: DeskPRO.Agent.PageFragment.Basic,

	initializeProperties: function() {
		this.parent();
		this.TYPENAME = 'newtask';
	},

	initPage: function(el) {

		this.noIgnoreForm = true;
		var self = this;
		this.wrapper = el;

		var nolink = false;


		var form = this.getEl('form');
		form.on('submit', Orb.cancelEvent);

		var rowContainer = this.getEl('tasks');
		rowContainer.empty();

		var openForEl = null;
		rowContainer.on('click', '.remove-row-trigger', function(ev) {
			var row = $(this).closest('.task-row');
			row.slideUp('fast', function() {
				row.remove();
				self.updateUi();
			});
		});

		rowContainer.on('click', '.opt-trigger.time_due', function(ev) {

			var row = $(this).closest('.task-row');
			var field = $('input.input-date-time', row);
			var timeLi = $(this).closest('ul').find('.time_due');
			var label = timeLi.find('label');

			var currentTime = field.val() || null;
			var currentH = null, currentM = null;
			if (currentTime && currentTime.indexOf(':') != -1) {
				currentTime = currentTime.split(':');
				currentH = parseInt(currentTime[0]);
				currentM = parseInt(currentTime[1]);
			}

			var optOverlay = $('<div class="field-overlay"><div class="close-trigger"></div><select class="time_hour"><option value="NONE"></option></select>:<select class="time_min"><option value="NONE"></option></select></div>');
			var backdrop = $('<div class="dp-popover-backdrop"></div>');
			var hourEl = optOverlay.find('.time_hour');
			var minEl = optOverlay.find('.time_min');

			for (var i = 0; i <= 23; i++) {
				var opt = $('<option></option>');
				opt.text(i < 10 ? '0' + i : i+'');
				opt.val(i);
				opt.appendTo(hourEl);

				if (currentH != null && currentH === i) {
					opt.attr('selected', true);
				}
			}
			for (var i = 0; i <= 55; i += 5) {
				var opt = $('<option></option>');
				opt.text(i < 10 ? '0' + i : i+'');
				opt.val(i);
				opt.appendTo(minEl);

				if (currentM != null && currentM === i) {
					opt.attr('selected', true);
				}
			}

			optOverlay.css({
				'z-idnex': 9999999,
				left: $(this).offset().left,
				top: $(this).offset().top
			});
			backdrop.css({
				'z-idnex': 9999998
			});
			optOverlay.appendTo('body');
			backdrop.appendTo('body');

			var close = function() {
				var hourVal = hourEl.find(':selected').val();
				var minVal  = minEl.find(':selected').val();

				var setTime, setTimeDisplay;

				if (hourVal === 'NONE') {
					setTime = '';
					setTimeDisplay = 'No specific time';
				} else {
					hourVal = parseInt(hourVal);
					minVal = parseInt(minVal) || 0;

					setTime = hourVal + ':' + minVal;
					setTimeDisplay = (hourVal < 10 ? '0'+hourVal : hourVal) + ':' + (minVal < 10 ? '0'+minVal : minVal);
				}

				field.val(setTime);
				label.text(setTimeDisplay);

				optOverlay.remove();
				backdrop.remove();
			};

			backdrop.on('click', close);
			optOverlay.find('.close-trigger').on('click', close);
		});
		rowContainer.on('click', '.opt-trigger.date_due', function(ev) {
			var label = $('label', this);
			var dateFormat = self.meta.dateFormat;

			var timeLi = $(this).closest('ul').find('.time_due');
			var label2 = timeLi.find('label');

			var row = $(this).closest('.task-row');
			var field = $('input.input-date-due', row);
			var field_real = $('input.input-date-due-real', row);
			var field2 = $('input.input-date-time', row);
			var date = $('input.input-date-due', row).val();
			if (!date) {
				date = new Date();
			}

			field.datepicker('dialog', date, function(date, inst) {
				$('input.input-date-due', row).val(date);
				label.text(date);
				timeLi.show();
			}, {
				dateFormat: dateFormat,
				altFormat: 'yy-mm-dd',
				altField: field_real,
				showButtonPanel: true,
				beforeShow: function(input) {
					setTimeout(function() {
						var buttonPane = $(input).datepicker("widget").find(".ui-datepicker-buttonpane");

						$('button', buttonPane).remove();

						var btn = $('<button class="ui-datepicker-current ui-state-default ui-priority-secondary ui-corner-all" type="button">'
                      + DeskPRO_Window.getTranslate().phrase('agent.general.clear')
                      + '</button>');
						btn.unbind("click").bind("click", function () {
							$.datepicker._clearDate( input );
							field2.val('');
							timeLi.hide();
							label2.text(DeskPRO_Window.getTranslate().phrase('agent.tasks.no_due_time'));
							label.text(DeskPRO_Window.getTranslate().phrase('agent.tasks.no_due_date'));
						});
						btn.appendTo( buttonPane );

						$(input).datepicker("widget").css('z-index', 30101);
					},1);
				}
			}, ev);
		});

		var addTaskRow = function() {
			var tpl = DeskPRO_Window.util.getPlainTpl(self.getEl('task_row_tpl'));
			var row = $(tpl);

			if (!nolink) {
				var activeTab = DeskPRO_Window.getTabWatcher().getActiveTabIfType('ticket');
				if (activeTab) {
					var linkEl = $('.linked-ticket', row);
					$('label', linkEl).text(activeTab.page.meta.title);
					$('input.input-ticket-id', row).val(activeTab.page.meta.ticket_id);
					linkEl.show();
					$('.remove-link-trigger', row).on('click', function() {
						nolink = true;
						linkEl.hide();
						$('input.input-ticket-id', row).val(0);
						$('.linked-container', row).hide();
					});
				}

				if (linkEl) {
					$('.linked-container', row).show();
				}
			}

			rowContainer.append(row);

			var agent_sel = row.find('.agents_sel');
			DP.select(agent_sel);

			agent_sel.on('change', function() {
				var val = $(this).val();
				var label = $(this).find(':selected').text().trim();

				if (!val) {
					val = '';
					label = 'Me';
				}

				row.find('.assigned_agent').find('label').text(label);
				$('input.input-agent', row).val(val);
			});

			var visibility_sel = row.find('.visibility_sel');
			DP.select(visibility_sel);

			visibility_sel.val(1);
			visibility_sel.on('change', function() {
				var val = $(this).val();
				var label = $(this).find(':selected').text().trim();

				if (!val) {
					val = '';
					label = '';
				}

				row.find('.visibility').find('label').text(label);
				$('input.input-vis', row).val(val);
			});

			self.updateUi();
		};

		this.getEl('add_btn').on('click', addTaskRow);

		addTaskRow();

		var footer = $('footer', el);
		$('.submit-trigger', el).on('click', function() {
			var postData = form.serializeArray();

			footer.addClass('loading');

			$.ajax({
				url: form.attr('action'),
				type: 'POST',
				dataType: 'json',
				data: postData,
				complete: function() {
					footer.removeClass('loading');
				},
				success: function(data) {
					if (DeskPRO_Window.sections.tasks_section) {
						DeskPRO_Window.sections.tasks_section.refresh();
					}
					self.closeSelf();
				}
			});
		});

		this.addEvent('popover-closed', function() {
			rowContainer.empty();
			addTaskRow();
		});
	}
});
