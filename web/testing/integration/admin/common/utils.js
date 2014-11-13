function dp_get_webpath(path) {
	return '/web' + path;
}

function dp_get_testfile(path) {
	return 'http://localhost:8888/index.php?_sys=testfile&f=' + encodeURIComponent(path);
}