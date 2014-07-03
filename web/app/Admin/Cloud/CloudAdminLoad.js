define([
	'Admin/Cloud/Main/Ctrl/Home',
	'Admin/Cloud/License/Ctrl/License'
], function() {
	if (window.DP_IS_CLOUD) {
		console.info("Cloud Mode Enabled");
	}
})