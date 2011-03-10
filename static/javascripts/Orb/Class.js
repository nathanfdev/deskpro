if (!Orb) var Orb = {};

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

	for (var name in properties) {
		if (properties.prototype && !properties.prototype.hasOwnProperty(name)) {
			continue;
		}

		var value = properties[name];

		if (typeof value == 'function') {
			if (checkParentUse(value)) {
				value = (function(func, name) {
					return function() {
						this.parent = parent_proto[name];
						return func.apply(this, arguments);
					};
				})(value, name);
			}
			proto[name] = value;
		} else {
			throw "Error: Non-function property in class. Set properties in an initializer method, never in the class body!";
		}
	}

	var newClass = function() {

		if (newClass.__is_prototyping) {
			return this;
		}

		if (this.initialize) {
			this.initialize.apply(this, arguments);
		}

		// Easy reference to the class object
		// Ex to use the set ClassVars easier
		this.CLASS = newClass;
		this.SUPER = parent_class;

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

	return newClass;
};