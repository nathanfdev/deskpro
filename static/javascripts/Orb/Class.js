if (!Orb) var Orb = {};

Orb.Class_Instances = {};
Orb.Class_GC_Callbacks = [];
Orb.Class_GC_PrintDebug = false;
Orb.Class_GC_Start = function(timeout) {
	/*
	if (Orb.Class_GC_Interval) {
		console.warn('[GC] GC already restarted. Clearing cycle timeout and starting again.');
		window.clearTimeout(Orb.Class_GC_Interval);
	}

	// Default to 10 seconds
	if (!timeout) {
		timeout = 10000;
	}

	Orb.Class_GC_Interval = window.setInterval(function() {
		Orb.Class_GC_Cycle();
	}, timeout);
	*/
}

Orb.Class_GC_Cycle = function() {
	var i, l;

	if (this.isRunning) return;
	this.isRunning = true;

	if (Orb.Class_GC_PrintDebug) {
		console.log('[GC] Cycle');
	}

	Object.each(Orb.Class_Instances, function(obj) {
		if (obj.OBJ_DESTROYED) {
			Orb.Class_GC_Cycle_Class(obj);
		}
	});

	this.isRunning = false;
};

Orb.Class_GC_Cycle_Class = function(obj) {

	var id = obj.OBJ_ID, i = null;

	if (obj.OBJ_DESTROYED && !obj.OBJ_DONE_DESTROYED) {
		obj.OBJ_DONE_DESTROYED = true;

		if (Orb.Class_GC_PrintDebug) {
			console.log('[GC] Destroyed %s: %o', id, obj);
		}

		for (i = 0, l = Orb.Class_GC_Callbacks.length; i < l; i++) {
			Orb.Class_GC_Callbacks[i](obj, id);
		}

		delete Orb.Class_Instances[id];
	}
};

Orb.Class = function(properties) {

	//------------------------------
	// DisableParentCall: true
	//
	// These utils are used to detect when a
	// function should implement a parent call
	//------------------------------

	// If the special DisableParentCall directive is used,
	// we always disable it.
	if (properties.DisableParentCall) {
		function checkParentUse(obj) {
			return false;
		}

	// Otherwise, we can know if its neccessary by checking for it's
	// usage. Most browsers support turning a function into a string,
	// for those that dont we can just play it safe and assume parent
	// is used.
	} else {
		var do_parent_detect = (function() {xyz}).toString().indexOf('xyz') != -1;
		function checkParentUse(obj) {
			if (!do_parent_detect) {
				return true;
			}

			return obj.toString().indexOf('this.parent(') != -1;
		}
	}

	delete properties.DisableParentCall;

	//------------------------------
	// Extends: SomeClass
	//
	// Copies everything from this class
	// into this new one we're making
	//------------------------------

	if (!properties.Extends) {
		properties.Extends = function() {};
	}

	var parent_class = properties.Extends;
	var parent_proto = parent_class.prototype;
	parent_class.__is_prototyping = true;
	var proto = new parent_class;
	delete parent_class.__is_prototyping;

	delete properties.Extends;


	//------------------------------
	// Implements: SomeMixin
	//
	// Copies all properties from the mix-in into
	// this new class we're making
	//------------------------------

	if (properties.Implements) {
		for (var i = 0, n = properties.Implements.length; i != n; ++i) {
			var mixin = properties.Implements[i];
			for (var name in mixin) {
				if (!mixin.prototype || mixin.prototype.hasOwnProperty(name)) {
					if (typeof mixin[name] == 'function') {
						proto[name] = mixin[name];
					}
				}
			}
		}
	}

	delete properties.Implements;


	//------------------------------
	// ClassVars: {}
	//
	// Copies all of these properties
	// over to the class object
	//------------------------------

	var static_props = null;
	if (properties.ClassVars) {
		static_props = properties.ClassVars;
		delete properties.ClassVars;
	}


	//------------------------------
	// Actually copies this classes properties
	// and methods now
	//------------------------------

	if (properties.destroy) {
		properties.__destroy = properties.destroy;
		properties.destroy = (function(old) {
			return function() {
				if (!this.OBJ_DESTROYED) {
					old.apply(this);
				}
				this.OBJ_DESTROYED = true;

				Orb.Class_GC_Cycle_Class(this);
			};
		})(properties.__destroy);
	} else {
		properties.destroy = (function() {
			return function() {
				this.OBJ_DESTROYED = true;
				Orb.Class_GC_Cycle_Class(this);
			};
		})();
	}

	for (var name in properties) {
		if (properties.prototype && !properties.prototype.hasOwnProperty(name)) {
			continue;
		}

		var value = properties[name];

		if (typeof value == 'function') {
			if (name != 'destroy' && checkParentUse(value)) {
				value = (function(func, name) {
					return function() {
						this.parent = parent_proto[name];
						return func.apply(this, arguments);
					};
				})(value, name);
			}
			proto[name] = value;
		} else {
			console.error("[Orb.Class] Non-function property in class: %o extends %o", this, properties);
			throw "Error: Non-function property in class";
			return;
		}
	}

	var newClass;
	newClass = function() {
		if (newClass.__is_prototyping) {
			return this;
		}

		// Easy reference to the class object
		// Ex to use the set ClassVars easier
		this.CLASS  = newClass;
		this.SUPER  = parent_class;
		this.OBJ_ID = Orb.uuid();
		this.OBJ_DESTROYED = false;

		Orb.Class_Instances[this.OBJ_ID] = this;

		if (this.initialize) {
			this.initialize.apply(this, arguments);
		}

		return this;
	}

	if (static_props) {
		for (name in static_props) {
			if (!static_props.prototype || static_props.prototype.hasOwnProperty(name)) {
				newClass[name] = static_props[name];
			}
		}
	}

	newClass.prototype = proto;
	newClass.constructor = newClass;

	delete properties;
	delete static_props;
	delete name;
	delete value;
	delete checkParentUse;

	return newClass;
};
