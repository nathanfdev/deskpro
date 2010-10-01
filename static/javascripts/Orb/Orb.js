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
		if (!$type(objcheck[part])) {
			objcheck[part] = {};
		}
		
		objcheck = objcheck[part];
	});
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
		id = prefix + $time() + Math.floor(Math.random()*1001);
	} while ($el(id));
	
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
 * Get an element. If the param IS an element, returns that.
 *
 * @param {String/HTMLElement} el The ID of an element, or an actual element to return as-is
 * @retrun {HTMLElement}
 */
Orb.getEl = function(el) {
	if ($type(el) == 'element') {
		return el;
	}
	
	return document.getElementById(el);
}
$el = function(el) { return Orb.getEl(el); };



/**
 * Sleep the client for a time. Note this actually freezes the client so should be used
 * very seldomly.
 *
 * @param {Integer} ms How many milliseconds to sleep for
 */
Orb.sleep = function(ms) {
	var start = new Date().getTime();
	for (var i = 0; i < 1e7; i++) {
		if ((new Date().getTime() - start) > ms){
			break;
		}
	}
}