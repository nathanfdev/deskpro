Orb.createNamespace('Orb.Util');

Orb.Util.Events = {

	__initEventsObj: function() {
		if (!this.__events) {
			this.__events = {};
			this.__events_tagged = {};
		}
	},

	setDefaultEventContext: function(context) {
		this.__events_default_context = context;
	},

	normalizeEventName: function(type) {
		return type.toLowerCase().replace(/^on/, '');
	},

	addEvent: function(type, fn, context, tags, beginning){

    tags = tags || [];
		this.__initEventsObj();

		type = this.normalizeEventName(type);
		if (!context) {
			context = this.__events_default_context;
		}

		if (!this.__events[type]) {
			this.__events[type] = [];
		}

		if (beginning && this.__events[type].length) {
			var newVal = [];
			newVal.push([fn, context]);
			for (var i = 0; i < this.__events[type].length; i++) {
				newVal.push(this.__events[type][i]);
			}

			this.__events[type] = newVal;
		} else {
			this.__events[type].push([fn, context]);
		}

		if (context && context.OBJ_ID) {
			tags.push(context.OBJ_ID);
		} else if (fn.OBJ_ID) {
			tags.push(fn.OBJ_ID);
		}

		if (tags && tags.length) {
			for (var i = 0; i < tags.length; i++) {
				if (!this.__events_tagged[tags[i]]) {
					this.__events_tagged[tags[i]] = [];
				}

				this.__events_tagged[tags[i]].push([type, fn, context]);
			}
		}

		return this;
	},

	addEvents: function(events, context, tags){
		for (var type in events) {
			this.addEvent(type, events[type], context, tags);
		}
		return this;
	},

	fireEvent: function(type, args, delay){
		var defaultContext, fn_info;

		this.__initEventsObj();

		type = this.normalizeEventName(type);
		if (!this.__events[type]) {
			return this;
		}

		defaultContext = this.__events_default_context || this;

		var argsArr = Array.from(args || []);
		if (argsArr.length < 1 && typeof args === 'object') {
			argsArr = [args];
		}

		for (var i = 0; i < this.__events[type].length; i++) {
			fn_info = this.__events[type][i];
			if (delay) {
				try {
					fn_info[0].delay(delay, fn_info[1] || defaultContext, argsArr);
				} catch (e) {
					console.error("Event Error %s: %o %s", type, e, e.stack || '');
				}
			} else {
				try {
					fn_info[0].apply(fn_info[1] || defaultContext, argsArr);
				} catch (e) {
					console.error("Event Error %s: %o %s", type, e, e.stack || '');
				}
			}
		}

		return this;
	},

	removeEvent: function(type, fn, context){
		var newFns = [], hasChange = false;

		this.__initEventsObj();

		type = this.normalizeEventName(type);
		if (!this.__events[type]) {
			return this;
		}

		if (!context) {
			context = null;
		}

		Array.each(this.__events[type], function(fn_info){
			if (fn_info[0] == fn && fn_info[1] == context) {
				hasChange = true;
			} else {
				newFns.push(fn_info);
			}
		});

		if (hasChange) {
			this.__events[type] = newFns;
		}

		return this;
	},

	removeEvents: function(events){
		var type;
		var self = this;
		if (!events) {
			events = [];
		}
		if (events.length === undefined) {
			events = [events];
		}

		this.__initEventsObj();

		events.forEach(function(type) {
      var fns = self.__events[type];
      if (!fns) return;
      for (var i = 0; i < fns.length; i++) {
        self.removeEvent(type, fns[i][0], fns[i][1]);
      }
		});

		return this;
	},

	removeTaggedEvents: function(tag) {
		if (!this.__events_tagged[tag]) return;

		Array.each(this.__events_tagged[tag], function (x) {
			this.removeEvent(x[0], x[1], x[2]);
		}, this);

    this.__events_tagged[tag] = null;
    delete this.__events_tagged[tag];
	},

	destroyEvents: function() {
    this.__events && this.removeEvents(Object.keys(this.__events));
		this.__events = {};
		this.__events_tagged = {};
	}
};
