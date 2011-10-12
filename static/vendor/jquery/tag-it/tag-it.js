(function($) {

	$.fn.tagit = function(options) {

		var el = this;

		var BACKSPACE		= 8;
		var ENTER			= 13;
		var SPACE			= 32;
		var COMMA			= 44;
		var TAB			    = 9;

		// option defaults
		if (!options.fieldName) options.fieldName = 'tags';
		if (!options.inputFieldHtml) options.inputFieldHtml = "<input class=\"tagit-input\" type=\"text\" data-placeholder=\"add...\" value=\"add...\" />";
		if (!options.inputFieldAppendTo) options.inputFieldAppendTo = el;
		if (options.enableBackspace === undefined) options.enableBackspace = true;
		if (!options.onchange) options.onchange = function() {};

		var in_init = true;

		var real_onchange = options.onchange;
		options.onchange = function() {
			if (in_init) return;
			return real_onchange();
		};

		// add the tagit CSS class.
		el.addClass("tagit");

		// create the input field.
		if (options.inputFieldAppendTo == el) {
			var tmp = $('<li class=\"tagit-new\">' + options.inputFieldHtml + '</li>');
			$('input', tmp).focus(function() {
				if ($(this).val() == $(this).data('placeholder')) {
					$(this).val('');
				}
				$(this).addClass('editting');

				if (options.focusShowAutocomplete) {
					tag_input.focus(function() {
						$(this).trigger('keydown.autocomplete');
						$(this).autocomplete('widget').show();
					});
				}
			}).blur(function() {
				if (!$(this).val().trim().length) {
					$(this).val($(this).data('placeholder'));
					$(this).removeClass('editting');
				}
			});
			el.append(tmp);
		} else {
			var tmp = $(options.inputFieldHtml);
			options.inputFieldAppendTo.append(tmp);
		}

		var tag_ul = el;
		var tag_input = $('.tagit-input', options.inputFieldAppendTo);

		// add existing tags
		el.children("li:not(.tagit-new)").each(function(ev){
			if (!$(this).hasClass('tagit-new')) {
				create_choice($('span', this).eq(0).html());
				$(this).remove();
			}
		});

		$(this).delegate('a.close', 'click', function() {
			if ($(this).parent().is('li')) {
				$(this).parent().remove();
			} else {
				$(this).parent().parent().remove();
			}
			options.onchange();
		});
		$(this).click(function(e){
			if (e.target.tagName == 'A') {

			}
			else {
				// Sets the focus() to the input field, if the user clicks anywhere inside the UL.
				// This is needed because the input field needs to be of a small size.
				tag_input.focus();
			}
		});

		tag_input.keydown(function(event){
			var keyCode = event.keyCode || event.which;
			// Backspace is not detected within a keypress, so using a keyup
			if (keyCode == BACKSPACE && options.enableBackspace) {
				if (tag_input.val() == "") {
					// When backspace is pressed, the last tag is deleted.
					$(el).children(".tagit-choice:last").remove();
				}
			}
		});
		tag_input.keypress(function(event){
			var keyCode = event.keyCode || event.which;
			// Comma/Enter/Tab are all valid delimiters for new tags
			if (keyCode == COMMA || keyCode == ENTER || keyCode == TAB) {

				event.preventDefault();

				var typed = tag_input.val();
				typed = typed.replace(/,+$/,"").trim().replace(/^"/, "").replace(/"$/, "");
				typed = typed.trim();

				if (typed != "") {
					if (is_new (typed)) {
						create_choice (typed);
					}

					// Cleaning the input.
					tag_input.blur();
					tag_input.val(tag_input.data('placeholder')).removeClass('editting');
					//tag_input.focus();
				}
			}
		});

		var autocomplete_opts = options.autocompleteOptions;
		if (autocomplete_opts) {
			autocomplete_opts.select = function(event,ui) {
				if (is_new (ui.item.value)) {
					create_choice (ui.item.value, ui.item.label);
				}

				window.setTimeout(function() {
					tag_input.blur();
					tag_input.val(tag_input.data('placeholder')).removeClass('editting');
					tag_input.focus();

					$(tag_input).trigger('keydown.autocomplete');
				}, 100);

				// Preventing the tag input to be updated with the chosen value.
				return false;
			}

			tag_input.autocomplete(autocomplete_opts);
		}

		function assigned_tags(){
			var tags = [];
			tag_input.parents("ul").children(".tagit-choice").each(function(){
				tags.push($(this).children("input").val());
			});
			return tags;
		}

		function subtract_array(a1,a2){
			var result = new Array();
			for(var i = 0; i < a1.length; i++) {
				if (a2.indexOf(a1[i]) == -1) {
					result.push(a1[i]);
				}
			}
			return result;
		}

		function is_new (value){
			var is_new = true;
			tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n) {
					is_new = false;
				}
			});
			return is_new;
		}
		function create_choice (value, label){
			var el = "";

			if (!label) label = value;

			el  = "<li class=\"tagit-choice\">\n<span>";
			el += label || value + "</span>\n";
			el += "<a class=\"close\">x</a>\n";
			el += "<input class=\"tagit-val\" type=\"hidden\" style=\"display:none;\" value=\""+value+"\" name=\"" + options.fieldName + "[]\">\n";
			el += "</li>\n";

			if (options.inputFieldAppendTo == tag_ul) {
				var li_search_tags = tag_input.parent();
				$(el).insertBefore (li_search_tags);
			} else {
				$(el).appendTo(tag_ul);
			}
			tag_input.val(tag_input.data('placeholder'));
			options.onchange();
		}

		this.add = function(value, label) {
			create_choice(value, label);
			return this;
		};
		this.remove = function(value) {
			tag_input.parents("ul").children(".tagit-choice").each(function(i){
				n = $(this).children("input").val();
				if (value == n)
					$(this).children('a').click();
			});
			return this;
		};
		this.getInput = function() {
			return tag_input;
		};

		this.getLabels = function() {
			var labels = [];

			$('input.tagit-val', tag_ul).each(function() {
				labels.push($(this).val().trim());
			});

			return labels;
		};

		this.getFormData = function () {
			return $('input.tagit-val', tag_ul).serializeArray();
		};

		in_init = false;

		return this;
	};

	String.prototype.trim = function() {
		return this.replace(/^\s+|\s+$/g,"");
	};

})(jQuery);
