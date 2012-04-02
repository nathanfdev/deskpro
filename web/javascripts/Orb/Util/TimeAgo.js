Orb.createNamespace('Orb.Util');

Orb.Util.TimeAgo = {

	_watchEls: [],
	_watchTimer: null,

	/**
	 * How often to update the elements
	 */
	refreshPeriod: 60000,//1min

	phrases: {
		'sec_less': 'less than a second',
		'sec':      '1 second',
		'secs':     '{0} seconds',
		'min':      '1 minute',
		'mins':     '{0} minutes',
		'hour':     '1 hour',
		'hours':    '{0} hours',
		'day':      '1 day',
		'days':     '{0} days',
		'week':     '1 week',
		'weeks':    '{0} weeks',
		'month':    '1 month',
		'months':   '{0} months',
		'year':     '1 year',
		'years':    '{0} years',
        'ago':      'ago'
	},


	/**
	 * Get the full words for a given date.
	 *
	 * @param date
	 */
	get: function(date) {
		return this.getForMs(this.getDateDiff(date));
	},


	/**
	 * Apply to an array of elements.
	 *
	 * @param $els
	 */
	applyToElements: function(els) {
		var self = this;
		els.each(function(el) {
			self._refreshElements([el]);
			self._watchEls.push(el);
		});

		if (this._watchTimer === null) {
			window.setInterval(this._refreshElements.bind(this), this.refreshPeriod);
		}
	},


	/**
	 * APply to a jQuery collection
	 *
	 * @param $els
	 */
	applyToJquery: function($els) {
		this.applyToElements($els.toArray());
	},


	_refreshElements: function(els) {
		if (!els) els = this._watchEls;

		var self = this;

		els.each(function(el) {

			// Could be removed, just skip it
			// might be reinserted later
			if (!el.parentNode) {
				return;
			}

			el = $(el);

			if (!el.data("timeago")) {

				var isTime = el.get(0).tagName.toLowerCase() == 'time';
				var iso8601 = isTime && el.attr('datetime') ? el.attr('datetime') : el.attr('title');

				if (!iso8601 || typeof iso8601 != 'string') {
					return;
				}

				var s = iso8601.replace(/\.\d\d\d+/,""); // remove milliseconds
				s = s.replace(/-/,"/").replace(/-/,"/");
				s = s.replace(/T/," ").replace(/Z/," UTC");
				s = s.replace(/([\+-]\d\d)\:?(\d\d)/," $1$2"); // -04:00 -> -0400

				el.data("timeago", { datetime: new Date(s) });

				var titleText = $.trim(el.text());
				if (titleText.length > 0) el.attr("title", titleText);
			}

			var data = el.data('timeago');
			if (!isNaN(data.datetime)) {
                var text = self.get(data.datetime);
                if (!el.data('timeago-no-ago')) {
                    text += ' ' + self.phrases['ago'];
                }
				el.text(text);
			}
		});
	},


	/**
	 * Get the relative date info for ms.
	 *
	 * @param int ms
	 */
	getRelativeInfo: function(ms) {
		var secs = 0, mins = 0, hours = 0, days = 0, years = 0;

		secs = parseInt(ms / 1000);

		years = parseInt(secs / 29030400);
		secs -= years * 29030400;

		days = parseInt(secs / 86400);
		secs -= days * 86400;

		hours = parseInt(secs / 3600);
		secs -= hours * 3600;

		mins = parseInt(secs / 60);
		secs -= mins * 60;

		return {
			'secs':    secs,
			'mins':    mins,
			'hours':   hours,
			'days':    days,
			'years':   years
		}
	},


	/**
	 * Get the full words for given ms.
	 *
	 * @param ms
	 */
	getForMs: function(ms) {
		var info = this.getRelativeInfo(ms);

		var total_secs = parseInt(ms / 1000);

		// less than 120 secons: 20 seconds
		if (total_secs <= 120) {
			return this.getPhraseFor('sec', info.secs).replace('{0}', info.secs);

		// less than 120 minutes: 20 minutes
		} else if (total_secs <= 1200) {
			return this.getPhraseFor('min', info.mins).replace('{0}', info.mins);

		// less than 24 hours: 2 1/2 hours
		} else if (total_secs <= 86400) {
			var fraction;
			if (info.mins <= 15) {
				fraction = '';
			} else if (info.mins <= 30) {
				fraction = '1/4';
			} else if (info.mins <= 45) {
				fraction = '1/2';
			} else if (info.mins <= 60) {
				fraction = '3/4';
			}

			var phrase_num = info.hours;
			var phrase_hours = info.hours + '';

			// Inc to override plural for ex 1 1/2 hours
			if (fraction !== '') {
				phrase_num += 1;
				phrase_hours += ' ' + fraction;
			}

			return this.getPhraseFor('hour', phrase_num).replace('{0}', phrase_hours);

		// less than 3 days: 2 days 2 hours
		} else if (total_secs <= 259200) {
			var phrase_days = this.getPhraseFor('day', info.days).replace('{0}', info.days);
			if (info.hours > 0) {
				phrase_days += ' ' + this.getPhraseFor('hour', info.hours).replace('{0}', info.hours);
			}

			return phrase_days;

		// less than 1 month: 5 days
		} else if (total_secs <= 2419200) {
			return this.getPhraseFor('day', info.days).replace('{0}', info.days);

		// less than 3 months: 5 weeks
		} else if (total_secs <= 7257600) {
			var weeks = parseInt(info.days / 7);
			return this.getPhraseFor('week', weeks).replace('{0}', weeks);

		// less than a year: 8 months
		} else if (total_secs <= 29030400) {
			var months = parseInt(info.days / 30);
			return this.getPhraseFor('month', info.months).replace('{0}', months);

		// less than 5 years: 1 year 3 months
		} else if (total_secs <= 145152000) {
			var phrase_years = this.getPhraseFor('year', info.years).replace('{0}', info.years);
			if (info.months > 0) {
				phrase_years += ' ' + this.getPhraseFor('month', info.months).replace('{0}', info.months);
			}

			return phrase_years;

		// more than 5 years: 8 years
		} else {
			return this.getPhraseFor('year', info.years).replace('{0}', info.years);
		}
	},


	/**
	 * Get the difference in ms or s for a date and current date.
	 *
	 * @param date
	 * @param bool secs True to return seconds instead of ms
	 */
	getDateDiff: function(date, secs) {
		var diff = (new Date().getTime() - date.getTime());
		if (secs) {
			diff /= 1000;
		}
		return diff;
	},

	/**
	 * Get the phrase for a time denomination
	 *
	 * @param string type
	 * @param int num
	 */
	getPhraseFor: function(type, num) {

		if (type == 'sec' && num <= 0) {
			return this.phrases['sec_less'];
		}

		var k = type;
		if (num != 1) {
			k += 's';
		}

		return this.phrases[k];
	}
};

if (jQuery) {
	jQuery.fn.timeago = function() {
		Orb.Util.TimeAgo.applyToJquery(this);
		return this;
	};
}
