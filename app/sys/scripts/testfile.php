<?php if (!defined('DP_ROOT')) exit('No access');

require DP_ROOT.'/sys/load_config.php';
dp_load_config();

if (0 && !@$GLOBALS['DP_USING_TESTING_CONFIG']) {
	header("Content-Type: text/plain");
	echo "Not in testing mode.";
	exit(1);
}

if (empty($_GET['f'])) {
	header("Content-Type: text/plain");
	echo "No file specified";
	exit(1);
}

function get_test_file_path($path)
{
	$path = realpath(DP_WEB_ROOT. '/web/' . trim($path, '/'));
	if (!$path || !is_file($path) || strpos($path, DP_WEB_ROOT) !== 0) {
		return null;
	}

	return $path;
}

$path = get_test_file_path($_GET['f']);

if (!$path) {
	header("Content-Type: text/plain");
	echo "File does not exist";
	exit(1);
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
switch ($ext) {
	case 'html': header('Content-Type: text/html'); break;
	case 'js':   header('Content-Type: text/javascript'); break;
	case 'json': header('Content-Type: application/json'); break;
	case 'xml':  header('Content-Type: text/xml'); break;
	default: header('Content-Type: text/plain'); break;
}

$content = file_get_contents($path);

function process_content_replacements($content)
{
	return preg_replace_callback('/<!\-\-#include\s*file="(.*?)"\s*\-\->/', function($m) {
		$path = get_test_file_path(trim($m[1]));
		if ($path) {
			return process_content_replacements(file_get_contents($path));
		} else {
			return '<!-- include error -- unknown file: ' . htmlspecialchars($m[1]) . ' -->';
		}
	}, $content);
}

if ($ext == 'html') {
	$content = process_content_replacements($content);
}

echo $content;
exit;