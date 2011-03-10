Orb.createNamespace('Orb.Util');

Orb.Util.Events = {
	
	__initEventsObj: function() {
		if (!this.__events) this.__events = {};
	},
	
	normalizeEventName: function(type) {
		return type.toLowerCase().replace(/^on/, '');
	},

	addEvent: function(type, fn, internal){
		type = this.normalizeEventName(type);
		this.__initEventsObj();
		this.__events[type] = (this.__events[type] || []).include(fn);
		if (internal) fn.internal = true;
		return this;
	},

	addEvents: function(events){
		for (var type in events) this.addEvent(type, events[type]);
		return this;
	},

	fireEvent: function(type, args, delay){
		type = this.normalizeEventName(type);
		this.__initEventsObj();
		var events = this.__events[type];
		if (!events) return this;
		args = Array.from(args);
		events.each(function(fn){
			if (delay) fn.delay(delay, this, args);
			else fn.apply(this, args);
		}, this);
		return this;
	},
	
	removeEvent: function(type, fn){
		type = this.normalizeEventName(type);
		this.__initEventsObj();
		var events = this.__events[type];
		if (events && !fn.internal){
			var index =  events.indexOf(fn);
			if (index != -1) delete events[index];
		}
		return this;
	},

	removeEvents: function(events){
		var type;
		if (typeOf(events) == 'object'){
			for (type in events) this.removeEvent(type, events[type]);
			return this;
		}

		this.__initEventsObj();
		for (type in this.__events){
			if (events && events != type) continue;
			var fns = this.__events[type];
			for (var i = fns.length; i--;) if (i in fns){
				this.removeEvent(type, fns[i]);
			}
		}
		return this;
	}
};