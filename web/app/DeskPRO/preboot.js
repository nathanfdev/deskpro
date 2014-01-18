(function() {
	function createXHR() {
		var xhr;
		if (window.ActiveXObject){
			try {
				xhr = new ActiveXObject("Microsoft.XMLHTTP");
			} catch(e) {
				xhr = null;
			}
		} else {
			xhr = new XMLHttpRequest();
		}
		return xhr;
	}

	window.onerror = function(message, url, linenumber) {
		var xhr = createXHR(),
			postData = [];
		if (!xhr) return;

		postData.push('message=' + encodeURIComponent(message));
		postData.push('script_file=' + encodeURIComponent(url));
		postData.push('script_line=' + encodeURIComponent(linenumber));
		postData = postData.join('&');

		xhr.open('POST', DP_BASE_API_URL+'/log-js-error', true)
		xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
		xhr.setRequestHeader('X-DeskPRO-API-Token', DP_API_TOKEN);
		xhr.send(postData)
	};
})();