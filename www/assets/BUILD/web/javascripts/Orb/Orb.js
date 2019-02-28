var Orb = {};

/**
 * Create a namespace.
 *
 * @param {String} namespace The namespace to create. Ex: Orb.something.whatever
 */
Orb.createNamespace = function(namespace, obj) {
	var parts = namespace.split('.');
	var objcheck = window;

	parts.forEach(function(part) {
		if (!objcheck[part]) {
			objcheck[part] = {};
		}

		objcheck = objcheck[part];
	});
};



/**
 * Gets the actual object from a string namespace.
 *
 * @param {String} fullname The full namespace path
 * @return {Object}
 */
Orb.getNamespacedObject = function(fullname) {

	var obj = window;

	fullname_parts = fullname.split('.');

	var part = null;
	while (part = fullname_parts.shift()) {
		if (obj[part] === undefined) {
			console.warn('Orb.getNamespacedObject(%s) is an invalid name', fullname);
			return null;
		}

		obj = obj[part];
	}

	return obj;
};



/**
 * Generate a new unique ID.
 *
 * @param {String} prefix An optional prefix
 * @return {String}
 */
Orb.getUniqueId = function(prefix) {

	if (!prefix) {
		prefix = '';
	}

	var id = '';
	do {
		id = prefix + Orb.uuid();
	} while (document.getElementById(id));

	return id;
};



/**
 * Get a unique ID that can be used app-wide. This is completely different from element
 * ID's; these should be used as other JS-type identifiers.
 *
 * @return {String}
 */
Orb.uuid = function() {
	return 'orb_uuid_' + (++Orb.uuid_num);
};
Orb.uuid_num = 0;

/**
 * Like uuid but has a random and a time component as well. Usefil when you need an actual unique id
 * on a page.
 */
Orb.uuidRand = function() {
	var time = (new Date()).getTime();
	var rand = Number.random(1, 999);
	return 'uuid_' + (++Orb.uuid_num) + '_' + time + '_' + rand;
};



/**
 * Get an element. If the param IS an element, returns that.
 *
 * @param {String/HTMLElement} el The ID of an element, or an actual element to return as-is
 * @retrun {HTMLElement}
 */
Orb.getEl = function(el) {
	if (Orb.typeOf(el) == 'element') {
		return el;
	}

	return document.getElementById(el);
};
$el = function(el) { return Orb.getEl(el); };

/**
 * Find the highest z-index value.
 *
 * @param {jQuery} els A jQuery collection. If non specified, then all elements on the page are scanned.
 * @return {Integer}
 */
Orb.findHighestZindex = function(els) {
	if (!els) {
		els = $('body > *');
	}

	var highest = 0;
	els.each(function() {
		var z = parseInt($(this).css('z-index'));
		if (z < 1000000000 && z > highest) {
			highest = z;
		}
	});

	return highest;
};



/**
 * Escape special HTML characters.
 *
 * @param str
 */
Orb.escapeHtml = function(str) {
	str = str || '';

	if (Orb.typeOf(str) === 'element') {
		str = $(str).text();
	} else if (Orb.typeOf(str) !== 'string') {
		console.error("Invalid type passed to Orb.escapeHtml: %o", str);
		console.trace();
		if (str.toString) {
			str = str.toString();
		} else {
			str = (typeof str) + '';
		}
	}

	return str.replace(/&/g, "&amp;")
		.replace(/>/g, "&gt;")
		.replace(/</g, "&lt;")
		.replace(/"/g, "&quot;");
};


/**
 * Convert newlines into HTML breaks
 *
 * @param str
 * @return string
 */
Orb.nl2br = function(str) {
	return str.replace(/\r\n|\n/g, "<br />\n");
};


/**
 * Link URLs in texts
 *
 * @param str
 */
Orb.linkUrls = function(str) {
	str = str||'';
	return str.replace(/\b(https?:\/\/|www\.)([^\s]+)\b(.?)/gi, function(match, o1, o2, o3, offset, s) {
		if (o3 && o3 == '/') {
			o2 += '/';
		}
		if (o1 == 'www.') {
			return '<a href="http://www.'+o2+'">' + match + '</a>';
		} else {
			return '<a href="'+o1+o2+'">' + match + '</a>';
		}
	});
};

/**
 * Appends query data to a URL which might already have query data. So this
 * appends an ? or an & depending.
 *
 * @param url
 * @param k
 * @param v
 */
Orb.appendQueryData = function(url, k, v) {
	var kev = k;
	if (v !== undefined) {
		kev += '=' + encodeURI(v);
	}

	if (url.indexOf('?') === -1) {
		url += '?' + kev;
	} else {
		url += '&' + kev;
	}

	return url;
};

/**
 * Serialize form elements wihtin context
 */
Orb.serializeFormElements = function(context, visitedEls) {
	var postData = [];

	if (!visitedEls) visitedEls = [];

	context.each(function() {
		$(this).find('input, select, textarea').each(function() {

			if (visitedEls.indexOf(this) !== -1) {
				return;
			}

			visitedEls.push(this);

			var el = $(this);
			var name = el.attr('name');

			if (!name) {
				return;
			}

			if (el.is(':checkbox, :radio')) {
				if (el.is(':checked')) {
					postData.push({name: name, value: el.val() });
				}
			} else if (el.is('input, textarea')) {
				postData.push({name: name, value: el.val() });
			} else if (el.is('select')) {
				el.find('option').filter(':selected').each(function() {
					postData.push({name: name, value: $(this).val() });
				});
			}
		});
	});

	return postData;
};


/**
 * Repeat a string `str` `count` times
 *
 * @param str
 * @param count
 * @return string
 */
Orb.strRepeat = function(str, count) {
	var finalStr = [];
	while (count-- > 0) {
		finalStr.push(str);
	}

	return finalStr.join('');
};

/**
 * Takes a regular expression string and escapes special characters
 *
 * @param strRegex
 * @return string
 */
Orb.regexQuote = function(strRegex) {
	return strRegex.replace(/([.?*+^$[\]\\(){}-])/g, "\\$1");
};

/**
 * Enables a phrase element by phraseId.
 *
 * This .show()'s a phrase element and .hide()'s any other
 * phrases in the same element.
 *
 * @param phraseId
 * @param parentEl
 */
Orb.enablePhraseEl = function(phraseId, parentEl) {
	var phraseEl, phraseClass = phraseId.replace(/\./g, '_');
	if (parentEl) {
		$(parentEl).find('.dp-phrase-switch').removeClass('dp-phrase-on');
		$(parentEl).find('.'+phraseClass).addClass('dp-phrase-on');
	} else {
		phraseEl = $('.' + phraseClass);
		if (phraseEl[0]) {
			$(phraseEl.parent()).find('.dp-phrase-text').removeClass('dp-phrase-on');
			phraseEl.addClass('dp-phrase-on');
		}
	}
};


/**
 * Re-executes a phrase and modifies the text of the element with the new value.
 *
 * @param {jQuery} el
 * @param {Object} vars
 */
Orb.phraseTextEl = function(el, vars) {
	if (!DeskPRO_Window.translate) {
		return;
	}

	var phraseText = el.data('phrase-text');
	phraseText = DeskPRO_Window.translate.phraseWithString(phraseText, vars, true);

	if (el.data('phrase-html')) {
		el.html(phraseText);
	} else {
		el.text(phraseText);
	}

	return el;
};

/**
 * Get selection from a range object
 *
 * @param sel
 */
Orb.getSelectionCoords = function(sel) {
	var range, rect;
	if (sel.rangeCount) {
		range = sel.getRangeAt(0).cloneRange();
		try {
			if (range.getClientRects) {
				range.collapse(true);
				rect = range.getClientRects()[0];
				if (rect) {
					return null;
				}

				return {
					left: rect.left,
					top: rect.top
				};
			}
		} catch (e) {
			return null;
		}
	} else if (sel.type && sel.type != "Control" && sel.createRange) {
		range = sel.createRange();
		range.collapse(true);

		if (range.boundingLeft && range.boundingTop) {
			return {
				left: range.boundingLeft,
				top: range.boundingTop
			}
		}
	}

	return null;
};

Orb.fnPass = function(fn, args, bind) {
	if (args) {
		args = Array.from(args);
	}
	return function() {
		return fn.apply(bind, args || arguments);
	};
};

Orb.fnDelay = function(fn, delay, bind, args) {
	return setTimeout(Orb.fnPass(fn, args, bind), delay);
};

Orb.arrPushUnique = function(arr, val) {
	if (arr.indexOf(val) === -1) {
		arr.push(val);
	}
};

Orb.arrRemoveValue = function(arr, val) {
	var what, a = arguments, L = a.length, ax;
	while (L > 1 && arr.length) {
		what = a[--L];
		while ((ax= arr.indexOf(what)) !== -1) {
			arr.splice(ax, 1);
		}
	}
	return arr;
};

Orb.typeOf = function(i) {
	if (i == null) {
		return "null";
	}
	if (i.nodeName) {
		if (i.nodeType === 1) {
			return "element";
		}
		if (i.nodeType === 3) {
			return (/\S/).test(i.nodeValue) ? "textnode" : "whitespace";
		}
	} else {
		if (Array.isArray(i)) {
			return "array";
		}
		if (typeof i.length == "number") {
			if (i.callee) {
				return "arguments";
			}
		}
	}
	return typeof i;
};

/**
 * Cancel an event. Stops bubbling and prevents default.
 * @param ev
 */
Orb.cancelEvent = function(ev) {
	ev.stopPropagation();
	ev.preventDefault();
};


Orb.shimClickCallback_shim  = null;
Orb.shimClickCallback_stack = [];
/**
 * This inserts a transparent shim at zIndex that is meant to capture click events.
 *
 * @param callback
 * @param zIndex
 */
Orb.shimClickCallback = function(callback, zIndex, opts) {
	if (!Orb.shimClickCallback_shim) {
		Orb.shimClickCallback_shim = $('<div/>').hide();
		Orb.shimClickCallback_shim.css({
			position: 'absolute',
			top: 0,
			right: 0,
			left: 0,
			bottom: 0,
			background: 'transparent'
		});
		Orb.shimClickCallback_shim.appendTo('body');

		Orb.shimClickCallback_shim.on('click', function(ev) {
			Orb.cancelEvent(ev);
			Orb.shimClickCallbackPop(false, [ev]);
		});
	}

	if (opts == 'fromtop') {
		Orb.shimClickCallback_shim.css('top', '51px');
	} else {
		Orb.shimClickCallback_shim.css('top', '0');
	}

	Orb.shimClickCallback_stack.push([callback, zIndex]);

	if (Orb.shimClickCallback_shim.data('zindex-class')) {
		Orb.shimClickCallback_shim.removeClass(Orb.shimClickCallback_shim.data('zindex-class'));
	}
	Orb.shimClickCallback_shim.addClass(zIndex).data('zindex-class', zIndex);
	Orb.shimClickCallback_shim.show();
};

Orb.shimClickCallbackPop = function(no_callback, args) {
	var lvl = Orb.shimClickCallback_stack.pop();

	if (!Orb.shimClickCallback_shim) {
		return;
	}

	if (lvl && !no_callback) {
		lvl[0].call(args);
	}

	if (Orb.shimClickCallback_stack.length) {
		if (Orb.shimClickCallback_shim.data('zindex-class')) {
			Orb.shimClickCallback_shim.removeClass(Orb.shimClickCallback_shim.data('zindex-class'));
		}
		Orb.shimClickCallback_shim.addClass(Orb.shimClickCallback_stack[Orb.shimClickCallback_stack.length-1][1])
			.data('zindex-class', Orb.shimClickCallback_stack[Orb.shimClickCallback_stack.length-1][1]);
		Orb.shimClickCallback_shim.show();
	} else {
		Orb.shimClickCallback_shim.hide();
	}
};

jQuery.fn.extend({
	insertAtCaret: function(myValue) {
		return this.each(function(i) {
			if (document.selection) {
				this.focus();
				sel = document.selection.createRange();
				sel.text = myValue;
				this.focus();
			}
			else if (this.selectionStart || this.selectionStart == '0') {
				var startPos = this.selectionStart;
				var endPos = this.selectionEnd;
				var scrollTop = this.scrollTop;
				this.value = this.value.substring(0, startPos)+myValue+this.value.substring(endPos,this.value.length);
				this.focus();
				this.selectionStart = startPos + myValue.length;
				this.selectionEnd = startPos + myValue.length;
				this.scrollTop = scrollTop;
			} else {
				this.value += myValue;
				this.focus();
			}
		});
	},

	setCaretPosition: function(caretPos) {
		return $.proxy(function() {
			if(this.createTextRange) {
				var range = this.createTextRange();
				range.move('character', caretPos);
				range.select();
			}
			else {
				if(this.selectionStart) {
					this.focus();
					this.setSelectionRange(caretPos, caretPos);
				}
				else {
					this.focus();
				}
			}
		}, this.get(0));
	},

	getCaretPosition: function() {
		return $.proxy(function() {
			if (document.selection && document.selection.createRange) {
				var range = document.selection.createRange();
				var bookmark = range.getBookmark();
				return bookmark.charCodeAt(2) - 2;
			} else if (this.setSelectionRange) {
				return this.selectionStart;
			} else {
				return 0;
			}
		}, this.get(0));
	}
});

/*
 * More info at: http://phpjs.org
 *
 * Licensed under MIT License http://phpjs.org/pages/license/#MIT
 */
function strtotime(str, now) {
    // http://kevin.vanzonneveld.net
    // +   original by: Caio Ariede (http://caioariede.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: David
    // +   improved by: Caio Ariede (http://caioariede.com)
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +   bugfixed by: Wagner B. Soares
    // +   bugfixed by: Artur Tchernychev
    // %        note 1: Examples all have a fixed timestamp to prevent tests to fail because of variable time(zones)
    // *     example 1: strtotime('+1 day', 1129633200);
    // *     returns 1: 1129719600
    // *     example 2: strtotime('+1 week 2 days 4 hours 2 seconds', 1129633200);
    // *     returns 2: 1130425202
    // *     example 3: strtotime('last month', 1129633200);
    // *     returns 3: 1127041200
    // *     example 4: strtotime('2009-05-04 08:30:00');
    // *     returns 4: 1241418600
    var i, match, s, strTmp = '',
        parse = '';

    strTmp = str;
    strTmp = strTmp.replace(/\s{2,}|^\s|\s$/g, ' '); // unecessary spaces
    strTmp = strTmp.replace(/[\t\r\n]/g, ''); // unecessary chars
    if (strTmp == 'now') {
        return (new Date()).getTime() / 1000; // Return seconds, not milli-seconds
    } else if (!isNaN(parse = Date.parse(strTmp))) {
        return (parse / 1000);
    } else if (now) {
        now = new Date(now * 1000); // Accept PHP-style seconds
    } else {
        now = new Date();
    }

    strTmp = strTmp.toLowerCase();

    var __is = {
        day: {
            'sun': 0,
            'mon': 1,
            'tue': 2,
            'wed': 3,
            'thu': 4,
            'fri': 5,
            'sat': 6
        },
        mon: {
            'jan': 0,
            'feb': 1,
            'mar': 2,
            'apr': 3,
            'may': 4,
            'jun': 5,
            'jul': 6,
            'aug': 7,
            'sep': 8,
            'oct': 9,
            'nov': 10,
            'dec': 11
        }
    };

    var process = function (m) {
        var ago = (m[2] && m[2] == 'ago');
        var num = (num = m[0] == 'last' ? -1 : 1) * (ago ? -1 : 1);

        switch (m[0]) {
        case 'last':
        case 'next':
            switch (m[1].substring(0, 3)) {
            case 'yea':
                now.setFullYear(now.getFullYear() + num);
                break;
            case 'mon':
                now.setMonth(now.getMonth() + num);
                break;
            case 'wee':
                now.setDate(now.getDate() + (num * 7));
                break;
            case 'day':
                now.setDate(now.getDate() + num);
                break;
            case 'hou':
                now.setHours(now.getHours() + num);
                break;
            case 'min':
                now.setMinutes(now.getMinutes() + num);
                break;
            case 'sec':
                now.setSeconds(now.getSeconds() + num);
                break;
            default:
                var day;
                if (typeof(day = __is.day[m[1].substring(0, 3)]) != 'undefined') {
                    var diff = day - now.getDay();
                    if (diff == 0) {
                        diff = 7 * num;
                    } else if (diff > 0) {
                        if (m[0] == 'last') {
                            diff -= 7;
                        }
                    } else {
                        if (m[0] == 'next') {
                            diff += 7;
                        }
                    }
                    now.setDate(now.getDate() + diff);
                }
            }
            break;

        default:
            if (/\d+/.test(m[0])) {
                num *= parseInt(m[0], 10);

                switch (m[1].substring(0, 3)) {
                case 'yea':
                    now.setFullYear(now.getFullYear() + num);
                    break;
                case 'mon':
                    now.setMonth(now.getMonth() + num);
                    break;
                case 'wee':
                    now.setDate(now.getDate() + (num * 7));
                    break;
                case 'day':
                    now.setDate(now.getDate() + num);
                    break;
                case 'hou':
                    now.setHours(now.getHours() + num);
                    break;
                case 'min':
                    now.setMinutes(now.getMinutes() + num);
                    break;
                case 'sec':
                    now.setSeconds(now.getSeconds() + num);
                    break;
                }
            } else {
                return false;
            }
            break;
        }
        return true;
    };

    match = strTmp.match(/^(\d{2,4}-\d{2}-\d{2})(?:\s(\d{1,2}:\d{2}(:\d{2})?)?(?:\.(\d+))?)?$/);
    if (match != null) {
        if (!match[2]) {
            match[2] = '00:00:00';
        } else if (!match[3]) {
            match[2] += ':00';
        }

        s = match[1].split(/-/g);

        for (i in __is.mon) {
            if (__is.mon[i] == s[1] - 1) {
                s[1] = i;
            }
        }
        s[0] = parseInt(s[0], 10);

        s[0] = (s[0] >= 0 && s[0] <= 69) ? '20' + (s[0] < 10 ? '0' + s[0] : s[0] + '') : (s[0] >= 70 && s[0] <= 99) ? '19' + s[0] : s[0] + '';
        return parseInt(this.strtotime(s[2] + ' ' + s[1] + ' ' + s[0] + ' ' + match[2]) + (match[4] ? match[4] / 1000 : ''), 10);
    }

    var regex = '([+-]?\\d+\\s' + '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' + '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' + '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday)' + '|(last|next)\\s' + '(years?|months?|weeks?|days?|hours?|min|minutes?|sec|seconds?' + '|sun\\.?|sunday|mon\\.?|monday|tue\\.?|tuesday|wed\\.?|wednesday' + '|thu\\.?|thursday|fri\\.?|friday|sat\\.?|saturday))' + '(\\sago)?';

    match = strTmp.match(new RegExp(regex, 'gi')); // Brett: seems should be case insensitive per docs, so added 'i'
    if (match == null) {
        return false;
    }

    for (i = 0; i < match.length; i++) {
        if (!process(match[i].split(' '))) {
            return false;
        }
    }

    return (now.getTime() / 1000);
}