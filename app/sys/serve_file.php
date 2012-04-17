<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace DeskPRO\Kernel;

if (!defined('DP_ROOT')) exit('No access');

require_once DP_ROOT.'/src/Orb/Data/ContentTypes.php';

/**
 * Database and filesystem-stored files are served through this file.
 *
 * Every blob has an "authcode" which doubely serves as a sort of password,
 * but it also embeds the blobs own ID and other required data.
 *
 * Database Files
 * --------------
 *
 * There is nothing very special about database-stored files. The blobs authcode is:
 * (id)(password)(0)
 *
 * That is the blob ID, a random string, and zero. The trailing zero tells this script
 * that it needs to fetch it from the database rather than the filesystem.
 *
 * Filesystem Files
 * ----------------
 *
 * Files in the filesystem are stored under the file root in folders counting up from 0. Each
 * folder has 1000 files in it.
 *
 * The authcode is:
 * (folder)(password)(id)(namehash)
 *
 * The "namehash" is a specil hash of the file filename. Since we don't connect to the database,
 * there's no way to know what the "real" filename of a file is. We output URLs with the correct filename in it
 * (aka it's a template-time thing), but we use the namehash to verify it is correct as an anti-spoofing mechanism.
 *
 * The hash is six characters, the three is part of a sha1 and the second three is part of an md5. While probably
 * possible to spoof the name still, using two different hashing functions should make it relatively hard.
 *
 * Since we can now trust the filename, we can use it to guess a mime-type based on extension, and send the correct headers,
 * all without connecting to the database.
 */
class FilestorageLoader
{
	protected $base_url;
	protected $path_info;
	protected $request_uri;

	/**
	 * @var \PDO
	 */
	protected $pdo;

	public function run()
	{
		#------------------------------
		# Normalize env
		#------------------------------

		setlocale(LC_CTYPE, 'C');
		date_default_timezone_set('UTC');
		ini_set('default_charset', 'UTF-8');

		#------------------------------
		# Undo magic quotes
		#------------------------------

		// Check exists since its gone in PHP 5.4
		if (function_exists('get_magic_quotes_gpc')) {
			ini_set('magic_quotes_runtime', 0);

			if (get_magic_quotes_gpc()) {
				$clean_fn = function(&$v) {
					$v = stripslashes($v);
				};

				array_walk_recursive($_GET,     $clean_fn);
				array_walk_recursive($_POST,    $clean_fn);
				array_walk_recursive($_COOKIE,  $clean_fn);
				array_walk_recursive($_REQUEST, $clean_fn);
			}
		}

		#------------------------------
		# Load config
		#------------------------------

		global $DP_CONFIG;
		require DP_CONFIG_FILE;

		if (!isset($DP_CONFIG) || !is_array($DP_CONFIG)) {
			$DP_CONFIG = array();
		}

		if (!isset($DP_CONFIG['db'])) $DP_CONFIG['db'] = array();
		if (!isset($DP_CONFIG['db']['host']))      $DP_CONFIG['db']['host']      = DP_DATABASE_HOST;
		if (!isset($DP_CONFIG['db']['user']))      $DP_CONFIG['db']['user']      = DP_DATABASE_USER;
		if (!isset($DP_CONFIG['db']['password']))  $DP_CONFIG['db']['password']  = DP_DATABASE_PASSWORD;
		if (!isset($DP_CONFIG['db']['dbname']))    $DP_CONFIG['db']['dbname']    = DP_DATABASE_NAME;

		#------------------------------
		# Run appropriate action
		#------------------------------

		try {
			$pathinfo = $this->getPathInfo();

			// Default avatar: /avatar/default
			if (preg_match('#^/avatar/default#', $pathinfo)) {
				$this->defaultAvatarAction();

			// Person avatar: /avatar/13
			} elseif (preg_match('#^/avatar/([0-9]+)#', $pathinfo, $m)) {
				$this->personAvatarAction($m[1]);

			// Default org avatar: /o-avatar/default
			} elseif (preg_match('#^/o-avatar/default#', $pathinfo)) {
				$this->defaultOrgAvatarAction();

			// Org avatar: /o-avatar/13
			} elseif (preg_match('#^/o-avatar/([0-9]+)#', $pathinfo, $m)) {
				$this->orgAvatarAction($m[1]);

			// User CSS
			} elseif (preg_match('#^/res-user/main.css#', $pathinfo, $m)) {
				$this->userCssAction();

			// sitemap.xml
			} elseif (preg_match('#^/sitemap.xml#', $pathinfo, $m)) {
				$this->sitemapXmlAction();

			// A filesystem blob like /123AJKJKHSD1244AXC/filename.zip
			// That is: /(batch)(authcode)(id)(namehash)/name.zip
			//0XNSNTQHTNR43DD567
			} elseif (preg_match('#^/([0-9]+)([A-Z]+)([0-9]+)([A-Z0-9]{6})/(.*?)$#', $pathinfo, $m)) {
				$this->handleFilesystemBlobRequest(
					$m[1],
					$m[2],
					$m[3],
					$m[4],
					$m[5]
				);

			// A database-stored bloblike /123AHSDHJGSD0/filename.zip
			// That is (id)(authcode0)
			// The trailing 0 denotes it as a database storage authcode
			} elseif (preg_match('#^/([0-9]+)([A-Z]+0)/(.*?)$#', $pathinfo, $m)) {
				$this->handleDbBlobRequest($m[1], $m[2], $m[3]);
			} elseif (preg_match('#^/gradient$#', $pathinfo)) {
				$this->handleGradientRequest();
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "File not found. (1)";
			}
		} catch (\Exception $exception) {

			header("HTTP/1.1 500 Internal Server Error");
			echo "An error occurred.";

			if (isset($DP_CONFIG['debug']['dev'])) {
				echo "\n\n[{$exception->getCode()}] {$exception->getMessage()}\n\n";

				$backtrace = $exception->getTrace();
				$trace = self::formatBacktrace($backtrace);
				echo $trace;
			}
		}
	}


	/**
	 * Serve user CSS blob
	 */
	public function userCssAction()
	{
		$sth = $this->getPdo()->prepare("
			SELECT blobs.*
			FROM styles
			LEFT JOIN blobs ON blobs.id = styles.css_blob_id
			WHERE styles.id = 1
			LIMIT 1
		");
		$sth->execute();
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!$blob || (isset($_GET['reload']) && filemtime(DP_ROOT . '/src/Application/UserBundle/Resources/views/Css/main.css.twig') > strtotime($blob['date_created']))) {
			$container = $this->bootFullSystem();
			$css = $container->get('templating')->render('UserBundle:Css:main.css.twig', array());

			$desc = $container->getFilestorage()->createRandomPath();
			$desc->write($css, array(
				'content_type' => 'text/css',
				'filename' => 'main.css'
			));
			$blob_id = $desc->getPath();

			$container->getDb()->update('styles', array('css_blob_id' => $blob_id), array('id' => 1));

			$sth = $this->getPdo()->prepare("
				SELECT blobs.*
				FROM blobs
				WHERE blobs.id =?
				LIMIT 1
			");
			$sth->execute(array($blob_id));
			$blob = $sth->fetch(\PDO::FETCH_ASSOC);
		}

		$this->showBlob($blob);
	}


	/**
	 * Generates a gradient image on the fly
	 */
	public function handleGradientRequest()
	{
		if (!function_exists('imagepng') || (!function_exists('imagecreatetruecolor') && !function_exists('imagecreate'))) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found (no_image_manip)";
			return;
		}

		require DP_ROOT . '/src/Orb/Util/Numbers.php';
		require DP_ROOT . '/src/Orb/Images/Util.php';

		$start_color = isset($_REQUEST['start_color']) ? (string)$_REQUEST['start_color'] : '000000';
		$end_color   = isset($_REQUEST['end_color'])   ? (string)$_REQUEST['end_color']   : '000000';

		$get_rgb = function($color) {
			// Not rgb(
			if (!strpos($color, '(') || !strpos($color, ')')) {
				$color = preg_replace('#[^a-fA-F0-9]#', '', $color);
				if (strlen($color) == 6 || strlen($color) == 3) {
					$color = \Orb\Util\Numbers::hex2rgb($color);
					if ($color) {
						$color = 'rgb(' . implode(',', $color) . ')';
					} else {
						$color = 'rgb(0,0,0)';
					}
				} else {
					$color = 'rgb(0,0,0)';
				}
			}

			if (preg_match('#rgb\((.*?),(.*?),(.*?)\)#i', $color, $m)) {
				$rgb = array(
					'red'   => (int)trim($m[1]),
					'green' => (int)trim($m[2]),
					'blue'  => (int)trim($m[3]),
				);
				return $rgb;
			} else {
				return array('red' => 0, 'green' => 0, 'blue' => 0);
			}
		};

		$start_color = $get_rgb($start_color);
		$end_color   = $get_rgb($end_color);

		$size = isset($_REQUEST['size']) ? (int)$_REQUEST['size'] : 20;
		if ($size < 1) $size = 20;
		if ($size > 1000) $size = 1000;

		$direction = isset($_REQUEST['direction']) ? $_REQUEST['direction'] : 'vertical';
		if ($direction != 'vertical' && $direction != 'horizontal') {
			$direction = 'vertical';
		}

		$im = \Orb\Images\Util::getGradientImage($size, $start_color, $end_color, $direction, 1, 1);

		$desc = implode('-',$start_color) . '_' . implode('-', $end_color) . '_' . $direction . '_' . $size . '.png';

		header('Last-Modified: ' . date('D, d M Y H:i:s', 1366187634).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', 1366187657).' GMT');
		header('Cache-Control: max-age=31556926,public');
		header('Content-Disposition: inline; filename=' . $desc);
		header("Content-type: image/png");
		imagepng($im);
		exit;
	}


	/**
	 * Serve the sitemap.xml file
	 */
	public function sitemapXmlAction()
	{
		$sth = $this->getPdo()->prepare("
			SELECT *
			FROM blobs
			WHERE sys_name = 'sitemap_xml'
		");
		$sth->execute();
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!$blob) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found.";
			return;
		}

		$this->showBlob($blob);
	}


	/**
	 * Render a persons avatar.
	 *
	 * @deprecated If you have a person record, then use picture_blob_id and directly link to the avatar
	 * @param $person_id
	 * @return mixed
	 */
	public function personAvatarAction($person_id)
	{
		$sth = $this->getPdo()->prepare("
			SELECT *
			FROM blobs
			LEFT JOIN people ON (blobs.id = people.picture_blob_id)
			WHERE people.id = :person_id
		");
		$sth->execute(array('person_id' => $person_id));
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!$blob) {
			$this->defaultAvatarAction();
			return;
		}

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob, $size);
	}

	/**
	 * Render an org avatar.
	 *
	 * @deprecated If you have a org record, then use picture_blob_id and directly link to the avatar
	 * @param $person_id
	 * @return mixed
	 */
	public function orgAvatarAction($org_id)
	{
		$sth = $this->getPdo()->prepare("
			SELECT *
			FROM blobs
			LEFT JOIN organizations ON (blobs.id = organizations.picture_blob_id)
			WHERE organizations.id = :org_id
		");
		$sth->execute(array('org_id' => $org_id));
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!$blob) {
			$this->defaultOrgAvatarAction();
			return;
		}

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob, $size);
	}


	/**
	 * Serve the default avatar
	 */
	public function defaultAvatarAction()
	{
		$name = 'picture-default';
		if (isset($_GET['is_agent'])) {
			$name = 'picture-default-agent';
		}

		$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE sys_name = :sys_name");
		$sth->execute(array('sys_name' => $name));
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		// The default avatar blob hasnt been inserted yet, default it from the resources dir now
		if (!$blob) {
			$container = $this->bootFullSystem();
			$desc = $container->getSystemService('filestorage')->createRandomPath();
			$desc->write(file_get_contents(DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.jpeg'), array(
				'content_type' => 'image/jpeg',
				'filename' => $name . '.jpeg',
				'sys_name' => $name,
			));

			$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE id = :id");
			$sth->execute(array('id' => $desc->getPath()));
			$blob = $sth->fetch(\PDO::FETCH_ASSOC);
		}

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob, $size);
	}


	/**
	 * Serve the default org avatar
	 */
	public function defaultOrgAvatarAction()
	{
		$name = 'orgpicture-default';

		$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE sys_name = :sys_name");
		$sth->execute(array('sys_name' => $name));
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		// The default avatar blob hasnt been inserted yet, default it from the resources dir now
		if (!$blob) {
			$container = $this->bootFullSystem();
			$desc = $container->getSystemService('filestorage')->createRandomPath();
			$desc->write(file_get_contents(DP_ROOT.'/src/Application/DeskPRO/Resources/assets/'.$name.'.jpeg'), array(
				'content_type' => 'image/jpeg',
				'filename' => $name . '.jpeg',
				'sys_name' => $name,
			));

			$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE id = :id");
			$sth->execute(array('id' => $desc->getPath()));
			$blob = $sth->fetch(\PDO::FETCH_ASSOC);
		}

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob, $size);
	}


	/**
	 * @param int $blob_id
	 * @param string $blob_auth
	 * @param string $blob_filename
	 */
	protected function handleFilesystemBlobRequest($batch, $authcode, $blob_id, $namehash, $filename)
	{
		#------------------------------
		# See if we need to resize
		#------------------------------

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		if ($size) {
			$this->showBlob($blob_id, $size);
			return;
		}

		#------------------------------
		# If its a simple file request we
		# can serve it without a db connection
		#------------------------------

		global $DP_CONFIG;

		if (isset($DP_CONFIG['folder_files'])) {
			$base_path = $GLOBALS['DP_CONFIG']['folder_files'];
		} else {
			$base_path = DP_WEB_ROOT . '/data/files';
		}

		$filepath = $base_path . DIRECTORY_SEPARATOR . $batch . DIRECTORY_SEPARATOR . $batch.$authcode . $blob_id . $namehash;

		$check_namehash = strtoupper(substr(sha1($filename . $blob_id), 0, 3));
		$check_namehash .= strtoupper(substr(md5($filename . $blob_id), 0, 3));

		// Invalid hash, or the file doesnt exist on disk
		if ($check_namehash != $namehash || !file_exists($filepath)) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found. (2)";
			return;
		}

		$mimetype = \Orb\Data\ContentTypes::getContentTypeFromFilename($filename);
		if (!$mimetype) {
			$mimetype = 'application/octet-stream';
		}

		$content_disposition = 'attachment';
		if (!isset($_GET['dl']) && \Orb\Data\ContentTypes::isImageContentType($mimetype)) {
			$content_disposition = 'inline';
		}

		header('Content-Type: ' . $mimetype . '; filename=' . $filename);
		header('Content-Length: ' . filesize($filepath));
		header('Content-Disposition: '.$content_disposition.'; filename=' . $filename);
		header('Last-Modified: ' . date('D, d M Y H:i:s', strtotime('2010-01-01')).' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
		header('Cache-Control: max-age=31556926,private');

		if (isset($DP_CONFIG['filestorage_use_xsendfile']) && $DP_CONFIG['filestorage_use_xsendfile']) {
			header("X-Sendfile: $filepath");
		} else {
			$fh = fopen($filepath, 'r');
			while (!feof($fh)) {
				echo fread($fh, 8192);
				flush();
			}
			fclose($fh);
		}
	}

	protected function handleDbBlobRequest($blob_id, $authseg, $filename)
	{
		$authcode = $blob_id . $authseg;

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob_id, $size, $authcode);
	}


	/**
	 * Renders a blob
	 *
	 * @param $blob
	 * @param null $size
	 */
	protected function showBlob($blob, $size = null, $blob_auth = null)
	{
		#------------------------------
		# Fetch the blob
		#------------------------------

		if (!is_array($blob)) {

			$blob_id = $blob;

			$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE id = :id");
			$sth->execute(array('id' => $blob_id));
			$blob = $sth->fetch(\PDO::FETCH_ASSOC);

			if (!$blob || ($blob_auth && $blob['authcode'] != $blob_auth)) {
				header("HTTP/1.0 404 Not Found");
				echo "File not found. (3)";
				return;
			}
		}

		$blob_id = $blob['id'];

		#------------------------------
		# Serve the file
		#------------------------------

		if (!isset($blob['filename_safe'])) {
			$filename_safe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $blob['filename']);
			$filename_safe = preg_replace('#\-{2,}#', '-', $filename_safe);
			$blob['filename_safe'] = $filename_safe;
		}

		$is_image = false;
		switch ($blob['content_type']) {
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/gif':
			case 'image/png':
				$is_image = true;
				break;
		}

		if ($is_image && $size) {
			$sth = $this->getPdo()->prepare("SELECT * FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name = :sys_name");
			$sth->execute(array('original_blob_id' => $blob_id, 'sys_name' => "blob-$blob_id-$size"));
			$sub_blob = $sth->fetch(\PDO::FETCH_ASSOC);

			// Already have the cached resized blob
			if ($sub_blob) {
				$sub_blob['filename_safe'] = $blob['filename_safe'];
				$blob = $sub_blob;

			// Generate the resized blob and save it now
			} else {
				$blob = $this->createSizedBlob($blob, $size, $this->getPdo());
			}
		}

		if ($blob['storage_loc'] == 'fs') {
			$this->sendFromFilesystem($blob);
		} else {
			$this->sendFromDatabase($blob, $this->getPdo());
		}
	}


	/**
	 * Send general file headers.
	 *
	 * Blobs never change so we can enable aggresive cache control on files served.
	 *
	 * @param $blob
	 */
	protected function sendHeaders($blob)
	{
		header('Content-Type: ' . $blob['content_type'] . '; filename=' . $blob['filename_safe']);
		header('Content-Length: ' . $blob['filesize']);

		switch ($blob['content_type']) {
			case 'image/jpg':
			case 'image/jpeg':
			case 'image/gif':
			case 'image/png':
				if (isset($_GET['dl'])) {
					header('Content-Disposition: attachment; filename=' . $blob['filename_safe']);
				} else {
					header('Content-Disposition: inline; filename=' . $blob['filename_safe']);
				}
				break;
			default:
				header('Content-Disposition: attachment; filename=' . $blob['filename_safe']);
		}

		$d = \DateTime::createFromFormat('Y-m-d H:i:s', $blob['date_created']);
		header('Last-Modified: ' . $d->format('D, d M Y H:i:s').' GMT');
		header('Expires: ' . date('D, d M Y H:i:s', strtotime('+1 year')).' GMT');
		header('Cache-Control: max-age=31556926,private');
	}


	/**
	 * Send a file that is stored in the filesystem
	 *
	 * @param $blob
	 */
	protected function sendFromFilesystem($blob)
	{
		global $DP_CONFIG;

		$this->sendHeaders($blob);

		if (isset($DP_CONFIG['folder_files'])) {
			$base_path = $GLOBALS['DP_CONFIG']['folder_files'];
		} else {
			$base_path = DP_WEB_ROOT . '/data/files';
		}

		$filepath = $base_path . DIRECTORY_SEPARATOR . $blob['save_path'];

		if (!file_exists($filepath)) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found. (4)";
			return;
		}

		if (isset($DP_CONFIG['filestorage_use_xsendfile']) && $DP_CONFIG['filestorage_use_xsendfile']) {
			header("X-Sendfile: $filepath");
		} else {
			$fh = fopen($filepath, 'r');
			while (!feof($fh)) {
				echo fread($fh, 8192);
				flush();
			}
			fclose($fh);
		}
	}


	/**
	 * Send a file that is stored in the database
	 *
	 * @param $blob
	 */
	public function sendFromDatabase($blob)
	{
		$this->sendHeaders($blob);

		$sth = $this->getPdo()->prepare("SELECT data FROM blobs_storage WHERE blob_id = :blob_id ORDER BY id DESC");
		$sth->execute(array('blob_id' => $blob['id']));

		while (($seg = $sth->fetchColumn(0)) !== false) {
			echo $seg;
			flush();
		}

		$sth->closeCursor();
	}


	/**
	 * Resize a blob. This needs to load the entire environment.
	 */
	protected function createSizedBlob($blob_info, $size)
	{
		$container = $this->bootFullSystem();

		$blob = $container->getEm()->find('DeskPRO:Blob', $blob_info['id']);

		$desc = $container->getSystemService('filestorage')->getFileDescriptor($blob['id']);
		$file = $desc->get();

		if (!$file) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found. (no_exist)";
			exit;
		}

		$image = $container->getImagine()->load($file);
		$image->resize(new \Imagine\Image\Box($size, $size));
		$file = $image->get($blob->getImageType());

		$desc = $container->getSystemService('filestorage')->createRandomPath();
		$desc->write($file, array(
			'content_type' => $blob->content_type,
			'filename' => $blob->filename,
			'sys_name' => 'blob-' . $blob->id . '-' . $size,
			'original_blob_id' => $blob->id,
		));

		$new_blob_info = $container->getDb()->fetchAssoc("SELECT * FROM blobs WHERE id = ?", array($desc->getPath()));
		$new_blob_info['filename_safe'] = $blob->getFilenameSafe();

		return $new_blob_info;
	}


	/**
	 * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
	 */
	protected function bootFullSystem()
	{
		static $container;

		if (!$container) {
			global $DP_CONFIG;
			$env = 'prod';
			$debug = false;

			if (isset($DP_CONFIG['debug']['dev']) && $DP_CONFIG['debug']['dev']) {
				$env = 'dev';
				$debug = true;
			}

			require DP_ROOT . '/sys/KernelBooter.php';
			\DeskPRO\Kernel\KernelBooter::bootstrapLib(true);

			// Used in the connection factory for the doctrine connection,
			// so it doesnt try and connect twice
			$GLOBALS['DP_DEFAULT_CONNECTION_PDO'] = $this->getPdo();

			$kernel_class = 'DeskPRO\\Kernel\\UserKernel';
			define('DP_INTERFACE', 'sys');

			$kernel = new $kernel_class($env, $debug);
			$kernel->boot();

			/** @var $container \Application\DeskPRO\DependencyInjection\DeskproContainer */
			$container = $kernel->getContainer();
		}

		return $container;
	}


	/**
	 * @return \PDO
	 */
	public function getPdo()
	{
		if ($this->pdo) {
			return $this->pdo;
		}

		global $DP_CONFIG;
		$this->pdo = new \PDO("mysql:dbname={$DP_CONFIG['db']['dbname']};host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
		$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

		return $this->pdo;
	}


	####################################################################################################################
	# Request Helpers
	####################################################################################################################

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getPathInfo()
	{
		if ($this->path_info !== null) {
			return $this->path_info;
		}

		$baseUrl = $this->getBaseUrl();

		if (null === ($requestUri = $this->getRequestUri())) {
			return '/';
		}

		$pathInfo = '/';

		// Remove the query string from REQUEST_URI
		if ($pos = strpos($requestUri, '?')) {
			$requestUri = substr($requestUri, 0, $pos);
		}

		if ((null !== $baseUrl) && (false === ($pathInfo = substr(urldecode($requestUri), strlen(urldecode($baseUrl)))))) {
			// If substr() returns false then PATH_INFO is set to an empty string
			return '/';
		} elseif (null === $baseUrl) {
			return $requestUri;
		}

		$this->path_info = (string)$pathInfo;
		return $this->path_info;
	}


	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getBaseUrl()
	{
		if ($this->base_url !== null) {
			return $this->base_url;
		}

		$filename = basename((isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : null));

		if (basename((isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null)) === $filename) {
			$baseUrl = (isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : null);
		} elseif (basename((isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null)) === $filename) {
			$baseUrl = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : null);
		} elseif (basename((isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null)) === $filename) {
			$baseUrl = (isset($_SERVER['ORIG_SCRIPT_NAME']) ? $_SERVER['ORIG_SCRIPT_NAME'] : null); // 1and1 shared hosting compatibility
		} else {
			// Backtrack up the script_filename to find the portion matching
			// php_self
			$path    = (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
			$file    = (isset($_SERVER['SCRIPT_FILENAME']) ? $_SERVER['SCRIPT_FILENAME'] : '');
			$segs    = explode('/', trim($file, '/'));
			$segs    = array_reverse($segs);
			$index   = 0;
			$last    = count($segs);
			$baseUrl = '';
			do {
				$seg     = $segs[$index];
				$baseUrl = '/'.$seg.$baseUrl;
				++$index;
			} while (($last > $index) && (false !== ($pos = strpos($path, $baseUrl))) && (0 != $pos));
		}

		// Does the baseUrl have anything in common with the request_uri?
		$requestUri = $this->getRequestUri();

		if ($baseUrl && 0 === strpos($requestUri, $baseUrl)) {
			// full $baseUrl matches
			return $baseUrl;
		}

		if ($baseUrl && 0 === strpos($requestUri, dirname($baseUrl))) {
			// directory portion of $baseUrl matches
			return rtrim(dirname($baseUrl), '/');
		}

		$truncatedRequestUri = $requestUri;
		if (($pos = strpos($requestUri, '?')) !== false) {
			$truncatedRequestUri = substr($requestUri, 0, $pos);
		}

		$basename = basename($baseUrl);
		if (empty($basename) || !strpos($truncatedRequestUri, $basename)) {
			// no match whatsoever; set it blank
			return '';
		}

		// If using mod_rewrite or ISAPI_Rewrite strip the script filename
		// out of baseUrl. $pos !== 0 makes sure it is not matching a value
		// from PATH_INFO or QUERY_STRING
		if ((strlen($requestUri) >= strlen($baseUrl)) && ((false !== ($pos = strpos($requestUri, $baseUrl))) && ($pos !== 0))) {
			$baseUrl = substr($requestUri, 0, $pos + strlen($baseUrl));
		}

		$this->base_url = rtrim($baseUrl, '/');
		return $this->base_url;
	}


	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getRequestUri()
    {
		if ($this->request_uri !== null) {
			return $this->request_uri;
		}

        $requestUri = '';

        if ((isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null) && false !== stripos(PHP_OS, 'WIN')) {
            // check this first so IIS will catch
            $requestUri = (isset($_SERVER['X_REWRITE_URL']) ? $_SERVER['X_REWRITE_URL'] : null);
        } elseif ((isset($_SERVER['IIS_WasUrlRewritten']) ? $_SERVER['IIS_WasUrlRewritten'] : null) == '1' && (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null) != '') {
            // IIS7 with URL Rewrite: make sure we get the unencoded url (double slash problem)
            $requestUri = (isset($_SERVER['UNENCODED_URL']) ? $_SERVER['UNENCODED_URL'] : null);
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $requestUri = (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null);
            // HTTP proxy reqs setup request uri with scheme and host [and port] + the url path, only use url path
            $schemeAndHttpHost = $this->getScheme().'://'.$this->getHttpHost();
            if (strpos($requestUri, $schemeAndHttpHost) === 0) {
                $requestUri = substr($requestUri, strlen($schemeAndHttpHost));
            }
        } elseif (isset($_SERVER['ORIG_PATH_INFO'])) {
            // IIS 5.0, PHP as CGI
            $requestUri = (isset($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : null);
            if ((isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null)) {
                $requestUri .= '?'.(isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : null);
            }
        }

        $this->request_uri = $requestUri;
		return $this->request_uri;
    }

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getScheme()
	{
		return $this->isSecure() ? 'https' : 'http';
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function isSecure()
	{
		return (
			(strtolower((isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null)) == 'on' || (isset($_SERVER['HTTPS']) ? $_SERVER['HTTPS'] : null) == 1)
			||
			((isset($_SERVER['SSL_HTTPS']) ? $_SERVER['SSL_HTTPS'] : null) == 1)
		);
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getHttpHost()
	{
		$scheme = $this->getScheme();
		$port   = $this->getPort();

		if (('http' == $scheme && $port == 80) || ('https' == $scheme && $port == 443)) {
			return $this->getHost();
		}

		return $this->getHost().':'.$port;
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getPort()
	{
		return (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null);
	}

	/**
	 * @see \Symfony\Component\HttpFoundation\Request
	 */
	public function getHost()
	{
		if (!$host = (isset($_SERVER['HOST']) ? $_SERVER['HOST'] : null)) {
			if (!$host = (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : null)) {
				$host = (isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '');
			}
		}

		// Remove port number from host
		$host = preg_replace('/:\d+$/', '', $host);

		return trim($host);
	}

	public static function formatBacktrace(array $backtrace)
	{
		$trace = '';
		foreach($backtrace as $k=>$v){

			$line = "#$k ";

			if (isset($v['object'])) {
				$line .= get_class($v['object']) . "::";
			} elseif (isset($v['class'])) {
				$line .= $v['class'] . "::";
			}

			$line .= "{$v['function']}(";

			if (!empty($v['args'])) {
				$line .= self::varToString($v['args']);
			}

			$line .= ")";

			if (!empty($v['file'])) {
				$line .= " called at [{$v['file']}:{$v['line']}]";
			}

			$line .= "\n";

			$trace .= $line;
		}

		return $trace;
	}

	public static function varToString($var)
    {
        if (is_object($var)) {
            return sprintf('[object](%s)', get_class($var));
        }
        if (is_array($var)) {
            $a = array();
            foreach ($var as $k => $v) {
                $a[] = sprintf('%s => %s', $k, self::varToString($v));
            }
            return sprintf("[array](%s)", implode(', ', $a));
        }
        if (is_resource($var)) {
            return '[resource]';
        }
        return str_replace("\n", '', var_export((string) $var, true));
    }
}

$file_loader = new FilestorageLoader();
$file_loader->run();
