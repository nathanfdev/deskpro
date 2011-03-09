if (!Orb) var Orb = {};

/**
 * Use Orb.Class for simple inhertiance and mix-in capabilities with Javascript.
 * 
 * <code>
 * var Animal = Orb.Class({
 * 	colorStr: 'black',
 * 	
 * 	color: function() {
 * 		alert(this.colorStr);
 * 	},
 * 	sound: function() {
 * 		alert('rawr');
 * 	}
 * });
 * 
 * var Dog = Orb.Class({
 * 	Extends: Animal,
 * 	ClassVars: {
 * 		BLAH: 'test'
 * 	},
 * 	
 * 	sound: function() {
 * 		this.parent();
 * 		alert(this.CLASS.BLAH);
 * 	}
 * });
 * </code>
*/
Orb.Class = function(properties) {
	
	// Special initializer that disables constructor when we're
	// calling from within a subclass constructor
	Orb.Class.is_initializing = false;

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
	
	if (properties.Extends) {
		var parent_proto = properties.Extends.prototype;
		Orb.Class.is_initializing = true;
		var proto = new properties.Extends;
		Orb.Class.is_initializing = false;
	} else {
		var parent_proto = {};
		var proto = new (function() { });
	}
	
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
					proto[name] = mixin[name];
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

		if (typeof value == 'function' && checkParentUse(value)) {
			value = (function(func, name) {
				return function() {
					this.parent = parent_proto[name];
					func.apply(this, arguments);
				};
			})(value, name);
		}
		
		proto[name] = value;
	}
		
	var newClass = function() {
		if (Orb.Class.is_initializing) {
			return;
		}

		if (this.initialize) {
			this.initialize.apply(this, arguments);
		}
		
		// Easy reference to the class object
		// Ex to use the set ClassVars easier
		this.CLASS = newClass;
		
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