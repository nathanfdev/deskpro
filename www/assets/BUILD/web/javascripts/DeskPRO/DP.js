if (typeof DP_DEBUG == 'undefined' || !DP_DEBUG) DP_DEBUG = false;

var DP = {
	convertTextToWysiwygHtml: function(text, pOneLine) {
		if (!text.length) {
			return '';
		}

		text = text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');

		if (pOneLine) {
			text = '<p>' + text.replace(/\r?\n/g, "</p>\n\n<p>") + '</p>';
		} else {
			text = '<p>' + text.replace(/\r?\n/g, '<br>\n').replace(/<br>\n<br>\n/g, "</p>\n\n<p>") + '</p>';
		}

		if (!$.browser.msie) {
			// IE renders the empty <p> tags
			text = text.replace(/<p><\/p>/g, '<p><br></p>');
		}

		return text;
	},

	select: function(el, options, noDefer) {
		if (el.length && el.length > 1) {
			el.each(function() {
				DP.select($(this), options);
			});
			return el;
		}

    if (el.data('select2') || el.data('no-select2')) {
      return el;
    }

    // Select2 boxes where the trigger is invisible (aka not the real select2 box),
    // we can defer init until later or until first click
    if (!noDefer && (el.attr('data-invisible-trigger') || el.attr('data-invisible-trigger-right'))) {
    	var init = {
    		hasInit: false,
				run: function () {
					if (!this.hasInit && document.body.contains(el[0])) {
						DP.select(el, options, true);
						el.parent().off('click', init.click);
					}
				},
				click: function() {
    			this.run();
    			el.select2('open');
				}
			};
    	init.run = init.run.bind(init);
    	init.click = init.click.bind(init);

    	if (!el.attr('data-lazy-init')) {
				window.requestIdleCallback(init.run, {timeout: 15000});
			}
			el.parent().one('click', init.click);
			return el;
		}

		options = options || {};

		if (el.data('style-type')) {
			switch (el.data('style-type')) {
				case 'icons':
					var iconSize = el.data('select-icon-size');
					if (!iconSize) iconSize = 16;
					iconSize = parseInt(iconSize);

					var addCss = '';
					var addCssLh = '';
					if (iconSize != 16) {
						addCss = 'padding-left: ' + (iconSize + 4) + 'px; line-height: ' + iconSize + 'px';
						addCssLh = 'line-height: ' + iconSize + 'px';
					}

					options.addWidth = iconSize + 35;

					el.addClass('dpe_select-with-icon dpe_select-with-icon-' + iconSize);

					options.addResultClass = 'with-icon';
					options.formatResult = function(result) {
						if (typeof result.id === 'undefined') {
							return result.text;
						}

						var opt = el.find('option[value="' + result.id + '"]');
						var name = Orb.escapeHtml(opt.text());
						if (opt.data('icon')) {
							return '<div class="result-icon" style="background-image: url(' + opt.data('icon') + ');'+addCss+'">' + name + '</div>';
						} else {
							return '<div class="result-icon no-icon" style="'+addCssLh+'">' + name + '</div>';
						}
					};
					options.formatSelection = function(data) {
						if (!data || typeof data.id == 'undefined') {
							return '';
						}
						var opt = el.find('option[value="' + data.id + '"]');
						if (!opt) {
							return '';
						}
						var name = Orb.escapeHtml(opt.text());
						if (opt.data('icon')) {
							return {type: 'html', value: '<span class="choice-icon" style="background-image: url(' + opt.data('icon') + '); padding-left: ' + (iconSize + 5) + 'px">' + name + '</span>' };
						} else {
							return opt.text();
						}
					};
					break;

				case 'urgency':
					options.formatResult = function(data) {
						var name = Orb.escapeHtml(data.text);
						if (data.id <= 0) {
							return name;
						}

						return '<span class="urgency urgency-' + data.id + '"><i>' + name + '</i></span>';
					};

					options.formatSelection = function(data) {
						var name = Orb.escapeHtml(data.text);
						if (data.id <= 0) {
							return name;
						}

						return {type: 'html', value: '<span class="urgency urgency-' + data.id + '"><i>' + name + '</i></span>'};
					};
					break;
			}
		} else {

			var withFullTitle = el.find('option[data-full-title]');
			if (withFullTitle[0]) {
				withFullTitle.each(function() {
					$(this).data('single-title', $.trim($(this).text()));
					$(this).text($(this).data('full-title'));
				});

				options.formatSelection = function(data) {
					data = data || {};
					var n = $('<span/>').text(data.text);
					return {type: 'el', value: n};
				};
				options.formatResult = function(result) {
					var opt = el.find('option[value="' + result.id + '"]');
					if (!opt || !opt[0]) {
						return $('<span/>').text(result.text || '');
					}
					var name = opt.data('single-title') || result.text;
					name = $('<span/>').text(name);
					return name;
				};
			}
      options.escapeMarkup = function(result) {
        return result.replace(/&amp;/, '&').replace(/&quot;/, '"');
      };
		}

		if (el.data('select-nogrouptitle')) {
			options.noGroupTitle = true;
		}

		if (el.data('select-width') == 'auto') {
			var shrink;
			if (el.data('select-width-shrink')) {
				shrink = parseInt(el.data('select-width-shrink'), 10);
			} else {
				shrink = 15;
			}

			if (el.parent().width()) {
				options.width = el.parent().width() - shrink + 'px';
			} else {
				// hidden farther up the change so walk up until we can see something
				// and then come back down
				var path = [];
				var shown = [];
				var testEl = el.parent();
				while (testEl && testEl.is(':hidden')) {
					path.push(testEl);
					testEl = testEl.parent();
				}

				if (testEl) {
					// this is visible
					for (var i = path.length - 1; i >= 0; i--) {
						if (path[i].is(':hidden')) {
							path[i].show();
							shown.push(path[i]);
						}
					}

					options.width = (el.parent().width() - shrink) + 'px';

					for (var i = 0; i < shown.length; i++) {
						shown[i].hide();
					}
				} // otherwise we didn't find something that wasn't hidden
			}
		} else if (el.data('select-width')) {
			options.width = el.data('select-width');
		} else {
			options.width = function() {
				var select_el = el;
				var largest = 0, label, charsize = 6, tmp;
				select_el.find('> *').each(function() {
					var el = $(this);
					if (el.is('optgroup')) {
						tmp = $.trim(el.attr('label')).length * charsize;
						if (tmp > largest) {
							largest = tmp;
						}

						el.find('option').each(function() {
							var s_el = $(this);
							tmp = ($.trim(s_el.text()).length * charsize) + 22; // +15 for optgroup indent
							if (tmp > largest) {
								largest = tmp;
							}
						});
					} else {
						tmp = ($.trim(el.text()).length * charsize) + 10;
						if (tmp > largest) {
							largest = tmp;
						}
					}
				});

				largest += 35;
				var maxWidth = parseInt(el.css('max-width'));

				if (largest > maxWidth) {
					largest = maxWidth;
				}

				return largest + 'px';
			};
		}

		if (el.data('select-clear')) {
			options.allowClear = true;
		}

		if (el.data('placeholder')) {
			options.placeholder = el.data('placeholder');
		}

		if (el.data('autocomplete-url')) {
			options = $.extend(true, {
				ajax: {
					url: el.data('autocomplete-url'),
					dataType: 'json',
					quietMillis: 250,
					data: function(term, page) {
						return {
							q: term
						};
					},
					results: function(data, page) {
						if (data.results) {
								data = data.results;
							}
						var results = [];
						for (var i = 0; i < data.length; i++) {
							results.push({
								id: data[i].id,
								text: data[i].name
							});
						}

						return {
							more: false,
							results: results
						};
					}
				},
				initSelection: function(element, callback) {
					var data = [];
					if (element.val().length) {
						$.ajax({
							url: el.data('autocomplete-url'),
							method: 'get',
							data: {ids: element.val().split(',')},
							success: function(json) {
								if (json.results) {
									json = json.results;
								}
								var data = [];
								for (var i = 0; i < json.length; i++) {
									data.push({
										id: json[i].id,
										text: json[i].name
									});
								}
								callback(data);
							},
							failure: function() {
								callback([]);
							}
						});
					} else {
						callback([]);
					}

					callback(data);
				}
			}, options);

			if (el.data('multiple')) {
				options.multiple = true;
			}
		}

		el.find('option').each(function() {
			var opt = $(this);
			if (!$.trim(opt.text())) {
				opt.html('&nbsp;');
				opt.attr('value', '');
			}
		});

		if (!options.addWidth) {
			options.addWidth = 18;
		}

		if (el.is('[multiple]') || options.multiple) {
			options.addWidth = null;
			if (!options.width || options.width) {
				options.width = '95%';
			}
		}

		options.formatNoMatches = options.formatNoMatches || function() { return ''; }

		if (el.data('invisible-trigger')) {
			options.containerCssClass = (options.containerCssClass || '') + ' invisible-trigger';
			options.dropdownCssClass = (options.dropdownCssClass || '') + ' invisible-trigger';
		} else if (el.data('invisible-trigger-right')) {
			options.containerCssClass = (options.containerCssClass || '') + ' invisible-trigger right';
			options.dropdownCssClass = (options.dropdownCssClass || '') + ' invisible-trigger right';
		}

		if (el.data('dropdown-css-class')) {
			options.dropdownCssClass = (options.dropdownCssClass || '') + ' ' + el.data('dropdown-css-class');
		}

		if (el.data('dropdown-nosearch')) {
			options.dropdownCssClass = (options.dropdownCssClass || '') + ' dp-select2-nosearch';
		}

		if (el.data('label-bound')) {
			var boundEl = $(el.data('label-bound'));
			var updateBoundEl = function() {
				var text = [];
				el.find(':selected').each(function() {
					text.push($.trim($(this).text()));
				});
				boundEl.text(text.join(', '));
			};
			$(el).on('change', updateBoundEl);
			updateBoundEl();
		}

		if (el[0] && 'SELECT' !== el[0].tagName && 1 !== el.data('allow-new') && !options.allowCreate) {
			options.createSearchChoice = null;
		}

		if (el.find('optgroup')[0]) {
			options.matcher = function(term, text, opt){
				var optg   = opt.parent("optgroup");

				term = STRINGS.stripDiacritics(term).toUpperCase();
				text = STRINGS.stripDiacritics(text).toUpperCase();
				var opt_group_text = optg ? STRINGS.stripDiacritics(optg.attr('label')||"").toUpperCase() : null;

				return text.indexOf(term) !== -1 || (opt_group_text && opt_group_text.indexOf(term) !== -1)
			};
		}

		el.addClass('with-select2');
		el.select2(options);
		return el;
	}
};
