Modernizr.addTest('osmac', function() {
	if (!navigator || !navigator.appVersion) {
		return false;
	}

	return (navigator.appVersion.indexOf("Mac")!=-1);
});