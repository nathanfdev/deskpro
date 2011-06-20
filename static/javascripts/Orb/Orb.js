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
	string = string||'';
	return string.replace(/&/g, "&amp;")
		.replace(/>/g, "&gt;")
		.replace(/</g, "&lt;")
		.replace(/"/g, "&quot;");
};


/**
 * Link URLs in texts
 *
 * @param string
 */
Orb.linkUrls = function(string) {
	string = string||'';
	return string
		.replace(/(https?:\/\/[^\s]+)/gi, '<a href="$1">$1</a>');
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
};



// resize event -
// Based on http://benalman.com/code/projects/jquery-resize/examples/resize/
(function($,window,undefined){
  '$:nomunge'; // Used by YUI compressor.

  // A jQuery object containing all non-window elements to which the resize
  // event is bound.
  var elems = $([]),

    // Extend $.resize if it already exists, otherwise create it.
    jq_resize = $.resize = $.extend( $.resize, {} ),

    timeout_id,

    // Reused strings.
    str_setTimeout = 'setTimeout',
    str_resize = 'resize',
    str_data = str_resize + '-special-event',
    str_delay = 'delay',
    str_throttle = 'throttleWindow';

  // Property: jQuery.resize.delay
  //
  // The numeric interval (in milliseconds) at which the resize event polling
  // loop executes. Defaults to 250.

  jq_resize[ str_delay ] = 325;

  // Property: jQuery.resize.throttleWindow
  //
  // Throttle the native window object resize event to fire no more than once
  // every <jQuery.resize.delay> milliseconds. Defaults to true.
  //
  // Because the window object has its own resize event, it doesn't need to be
  // provided by this plugin, and its execution can be left entirely up to the
  // browser. However, since certain browsers fire the resize event continuously
  // while others do not, enabling this will throttle the window resize event,
  // making event behavior consistent across all elements in all browsers.
  //
  // While setting this property to false will disable window object resize
  // event throttling, please note that this property must be changed before any
  // window object resize event callbacks are bound.

  jq_resize[ str_throttle ] = false;

  // Event: resize event
  //
  // Fired when an element's width or height changes. Because browsers only
  // provide this event for the window element, for other elements a polling
  // loop is initialized, running every <jQuery.resize.delay> milliseconds
  // to see if elements' dimensions have changed. You may bind with either
  // .resize( fn ) or .bind( "resize", fn ), and unbind with .unbind( "resize" ).
  //
  // Usage:
  //
  // > jQuery('selector').bind( 'resize', function(e) {
  // >   // element's width or height has changed!
  // >   ...
  // > });
  //
  // Additional Notes:
  //
  // * The polling loop is not created until at least one callback is actually
  //   bound to the 'resize' event, and this single polling loop is shared
  //   across all elements.
  //
  // Double firing issue in jQuery 1.3.2:
  //
  // While this plugin works in jQuery 1.3.2, if an element's event callbacks
  // are manually triggered via .trigger( 'resize' ) or .resize() those
  // callbacks may double-fire, due to limitations in the jQuery 1.3.2 special
  // events system. This is not an issue when using jQuery 1.4+.
  //
  // > // While this works in jQuery 1.4+
  // > $(elem).css({ width: new_w, height: new_h }).resize();
  // >
  // > // In jQuery 1.3.2, you need to do this:
  // > var elem = $(elem);
  // > elem.css({ width: new_w, height: new_h });
  // > elem.data( 'resize-special-event', { width: elem.width(), height: elem.height() } );
  // > elem.resize();

  $.event.special[ str_resize ] = {

    // Called only when the first 'resize' event callback is bound per element.
    setup: function() {
      // Since window has its own native 'resize' event, return false so that
      // jQuery will bind the event using DOM methods. Since only 'window'
      // objects have a .setTimeout method, this should be a sufficient test.
      // Unless, of course, we're throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }

      var elem = $(this);

      // Add this element to the list of internal elements to monitor.
      elems = elems.add( elem );

      // Initialize data store on the element.
      $.data( this, str_data, { w: elem.width(), h: elem.height() } );

      // If this is the first element added, start the polling loop.
      if ( elems.length === 1 ) {
        loopy();
      }
    },

    // Called only when the last 'resize' event callback is unbound per element.
    teardown: function() {
      // Since window has its own native 'resize' event, return false so that
      // jQuery will unbind the event using DOM methods. Since only 'window'
      // objects have a .setTimeout method, this should be a sufficient test.
      // Unless, of course, we're throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }

      var elem = $(this);

      // Remove this element from the list of internal elements to monitor.
      elems = elems.not( elem );

      // Remove any data stored on the element.
      elem.removeData( str_data );

      // If this is the last element removed, stop the polling loop.
      if ( !elems.length ) {
        clearTimeout( timeout_id );
      }
    },

    // Called every time a 'resize' event callback is bound per element (new in
    // jQuery 1.4).
    add: function( handleObj ) {
      // Since window has its own native 'resize' event, return false so that
      // jQuery doesn't modify the event object. Unless, of course, we're
      // throttling the 'resize' event for window.
      if ( !jq_resize[ str_throttle ] && this[ str_setTimeout ] ) { return false; }

      var old_handler;

      // The new_handler function is executed every time the event is triggered.
      // This is used to update the internal element data store with the width
      // and height when the event is triggered manually, to avoid double-firing
      // of the event callback. See the "Double firing issue in jQuery 1.3.2"
      // comments above for more information.

      function new_handler( e, w, h ) {
        var elem = $(this),
          data = $.data( this, str_data );

        // If called from the polling loop, w and h will be passed in as
        // arguments. If called manually, via .trigger( 'resize' ) or .resize(),
        // those values will need to be computed.
        data.w = w !== undefined ? w : elem.width();
        data.h = h !== undefined ? h : elem.height();

        old_handler.apply( this, arguments );
      };

      // This may seem a little complicated, but it normalizes the special event
      // .add method between jQuery 1.4/1.4.1 and 1.4.2+
      if ( $.isFunction( handleObj ) ) {
        // 1.4, 1.4.1
        old_handler = handleObj;
        return new_handler;
      } else {
        // 1.4.2+
        old_handler = handleObj.handler;
        handleObj.handler = new_handler;
      }
    }

  };

  function loopy() {

    // Start the polling loop, asynchronously.
    timeout_id = window[ str_setTimeout ](function(){

      // Iterate over all elements to which the 'resize' event is bound.
      elems.each(function(){
        var elem = $(this),
          width = elem.width(),
          height = elem.height(),
          data = $.data( this, str_data );

		  console.log('check %o', elem);

        // If element size has changed since the last time, update the element
        // data store and trigger the 'resize' event.
        if ( width !== data.w || height !== data.h ) {
          elem.trigger( str_resize, [ data.w = width, data.h = height ] );
        }

      });

      // Loop.
      loopy();

    }, jq_resize[ str_delay ] );

  };

})(jQuery,this);