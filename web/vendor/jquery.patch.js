// hotfix for 1.7 to allow appending of array of jq objects
;(function(jQuery) {
	var old = jQuery.fn.append;
	jQuery.fn.append = function () {
		// Flatten any nested arrays
		var args = [].concat.apply([], arguments);
		if (Object.prototype.toString.call(args[0]) === '[object Array]') args = args[0];
		return old.apply(this, args);
	};
})(jQuery);
