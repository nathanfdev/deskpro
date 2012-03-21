Modernizr.addTest('osmac', function() {
	if (!navigator || !navigator.appVersion) {
		return false;
	}

	return (navigator.appVersion.indexOf("Mac")!=-1);
});

Modernizr.addTest('browser-ie', function() {
	if (!navigator || !navigator.appVersion) {
		return false;
	}

	return (navigator.appVersion.toLowerCase().indexOf("msie")!=-1);
});