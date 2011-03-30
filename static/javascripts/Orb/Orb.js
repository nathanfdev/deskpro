var Orb = {};

if (window.console === undefined) {
	window.console = {};
	['error', 'log', 'warn', 'info', 'debug'].each(function(v) {
		window.console[v] = function() { };
	});
}

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
 * Get an element. If the param IS an element, returns that.
 *
 * @param {String/HTMLElement} el The ID of an element, or an actual element to return as-is
 * @retrun {HTMLElement}
 */
Orb.getEl = function(el) {
	if (typeOf(el) == 'element') {
		return el;
	}
	
	return document.getElementById(el);
};
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
};

// I dont think this works :)
Orb.mouseInElement = function(mouseX, mouseY, el) {
	var pos = el.offset();
	var width = el.outerWidth();
	var height = el.outerHeight();
	
	if (mouseX < pos.left || mouseX > pos.left+width) {
		return false;
	}
	
	if (mouseY < pos.top || mouseY > pos.top+height) {
		return false;
	}
	
	return true;
};



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
		if (z > highest) {
			highest = z;
		}
	});
	
	return highest;
};



/**
 * Escape special HTML characters.
 * 
 * @param string
 */
Orb.escapeHtml = function(string) {
	return string.replace(/&/g, "&amp;")
		.replace(/>/g, "&gt;")
		.replace(/</g, "&lt;")
		.replace(/"/g, "&quot;");
};



/**
 * Simple way to load Javascript and CSS files on-demand.
 *
 * Usage:
 * <code>
 *  Orb.resourceLoader.loadScript('whatever.js');
 *  Orb.resourceLoader.loadBatch([{
 *  	type: 'script',
 *  	src: 'whatever.js'
 *  }, {
 *  	type: 'css',
 *  	src: 'whatever.css'
 *  }], function() { alert("All resources loaded"); });
 * </code>
 */
Orb.resourceLoader = {
	batches: {},
	batchesCallback: {},
	
	/**
	 * Load a new Javascript source
	 *
	 * @param {String} src The path or full URL to the source file
	 * @param {Function} callback The function to execute when the file has been loaded
	 */
	loadScript: function(src, callback) {
		this.loadBatch([{
			type: 'script',
			url: src
		}], callback);
	},



	/**
	 * Load a new CSS stylesheet
	 *
	 * @param {String} url The path or full URL to the CSS file
	 * @param {Function} callback The function to execute when the file has been loaded
	 */
	loadStylesheet: function(url, callback) {
		this.loadBatch([{
			type: 'css',
			url: url
		}], callback);
	},
	
	
	
	/**
	 * Load a number of resources all at once, and be notified when they've all finished
	 * loading.
	 *
	 * `resources` must be a hash of `type` being 'script' or 'stylesheet', and `url` being the
	 * path or full URL to the file.
	 *
	 * @param {Object} resources Descriptions of each resource
	 * @param {Function} callback The function to execute when all files have been loaded
	 */
	loadBatch: function(resources, callback) {
	
		var batchId = Orb.uuid();
		var head = $('head');
		
		this.batches[batchId] = [];
		this.batchesCallback[batchId] = callback;
		
		var res = null;
		while (res = resources.shift()) {
			var resourceId = Orb.uuid();
			
			var fn = function() {
				Orb.resourceLoader._resourceDoneLoading(batchId, resourceId);
			};
			
			if (res.type == 'script') {
				var tag = document.createElement('script');
				tag.type = "text/javascript";
				tag.src = res.url;
			} else if (res.type == 'stylesheet') {
				var tag = document.createElement('link');
				tag.rel = "stylesheet";
				tag.type = 'text/css';
				tag.href = res.url;
				tag.media = "screen";
				
				if (res.media != undefined) {
					tag.media = res.media;
				}
			}
			
			tag.onreadystatechange= function () {
				if (this.readyState == 'complete') fn();
			}
			tag.onload = fn;
			
			this.batches[batchId].push(resourceId);
		}
		
	},
	
	_resourceDoneLoading: function(batchId, resourceId) {
		if (this.batches[batchId] == undefined) {
			return false;
		}
		
		this.batches[batchId].erase(resourceId);
		
		if (!this.batches[batchId].length) {
			var callback = this.batchesCallback[batchId];
			
			delete this.batches[batchId];
			delete this.batchesCallback[batchId];
			
			callback();
		}
	}
};



/**
 * There is no way to attach a single click handler and a double-click handler.
 * So to do it, we have to emulate double-click detection by setting a timeout.
 * If a second click happens before the timeout, then we can run the double-click callback.
 * If no second click happens and the timeout expires, then we can run the original.
 *
 * @param {Function} single_click_callback The single-click function
 * @param {Function} double_click_callback The double-click function
 * @param {Integer} timeout How long the user has to make a second-click (default 250)
 */
$.fn.single_double_click = function(single_click_callback, double_click_callback, timeout) {
	timeout = timeout || 250;
	return this.each(function() {
	    var clicks = 0;
		var self = this;

		// ie triggers dblclick instead of click if they are fast
	    if ($.browser.msie) {
	        $(this).bind("dblclick", function(event) {
	            clicks = 2;
	            double_click_callback.call(self, event);
	        });
	        $(this).bind("click", function(event) {
	            setTimeout(function() {
	                if (clicks != 2) {
	                    single_click_callback.call(self, event);
	                }
	                clicks = 0;
	            }, timeout);
	        });
	
	    } else {
	        $(this).bind("click", function(event) {
	            clicks++;
	            if (clicks == 1) {
	                setTimeout(function() {
	                    if (clicks == 1) {
	                        single_click_callback.call(self, event);
	                    } else {
	                        double_click_callback.call(self, event);
	                    }
	                    clicks = 0;
	                }, timeout);
	            }
	        });
	    }
	});
}