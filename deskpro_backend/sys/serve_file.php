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
		# Config and DB connection
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

		$this->pdo = new \PDO("mysql:dbname={$DP_CONFIG['db']['dbname']};host={$DP_CONFIG['db']['host']}", $DP_CONFIG['db']['user'], $DP_CONFIG['db']['password']);
		$this->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

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

			// Any other blob: /123-AUTH/filename.zip
			} elseif (preg_match('#^/([0-9]+)\-([A-Z0-9]+)/?(.*?)$#', $pathinfo, $m)) {
				$this->handleBlobRequest($m[1], $m[2], $m[3]);
			} else {
				header("HTTP/1.0 404 Not Found");
				echo "File not found.";
			}
		} catch (\Exception $e) {
			header("HTTP/1.1 500 Internal Server Error");
			echo "An error occurred.";
		}
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
		$sth = $this->pdo->prepare("
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
		$sth = $this->pdo->prepare("
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

		$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE sys_name = :sys_name");
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

			$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE id = :id");
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

		$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE sys_name = :sys_name");
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

			$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE id = :id");
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
	protected function handleBlobRequest($blob_id, $blob_auth, $blob_filename)
	{
		#------------------------------
		# Fetch and verify the blob
		#------------------------------

		$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE id = :id");
		$sth->execute(array('id' => $blob_id));
		$blob = $sth->fetch(\PDO::FETCH_ASSOC);

		if (!$blob || $blob['authcode'] != $blob_auth) {
			header("HTTP/1.0 404 Not Found");
			echo "File not found.";
			return;
		}

		$filename_safe = preg_replace('#[^a-zA-Z0-9\-_\.]#', '-', $blob['filename']);
		$filename_safe = preg_replace('#\-{2,}#', '-', $filename_safe);
		$blob['filename_safe'] = $filename_safe;

		if ($blob_filename != $filename_safe) {
			// Invalid filename, redirect to the correct one
			$url = $this->getScheme().'://'.$this->getHttpHost() . $this->getBaseUrl() . '/' . $blob['id'] . '-' . $blob['authcode'] . '/' . $filename_safe;
			header("HTTP/1.1 301 Moved Permanently");
			header("Location: $url");
			return;
		}

		#------------------------------
		# Serve the file
		#------------------------------

		$size = null;
		if (isset($_GET['s']) && is_numeric($_GET['s']) && $_GET['s'] > 1 && $_GET['s'] < 201) {
			$size = $_GET['s'];
		}

		$this->showBlob($blob, $size);
	}


	/**
	 * Renders a blob
	 *
	 * @param $blob
	 * @param null $size
	 */
	protected function showBlob($blob, $size = null)
	{
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
			$sth = $this->pdo->prepare("SELECT * FROM blobs WHERE original_blob_id = :original_blob_id AND sys_name = :sys_name");
			$sth->execute(array('original_blob_id' => $blob_id, 'sys_name' => "blob-$blob_id-$size"));
			$sub_blob = $sth->fetch(\PDO::FETCH_ASSOC);

			// Already have the cached resized blob
			if ($sub_blob) {
				$sub_blob['filename_safe'] = $blob['filename_safe'];
				$blob = $sub_blob;

			// Generate the resized blob and save it now
			} else {
				$blob = $this->createSizedBlob($blob, $size, $this->pdo);
			}
		}

		if ($blob['storage_loc'] == 'fs') {
			unset($this->pdo);
			$this->sendFromFilesystem($blob);
		} else {
			$this->sendFromDatabase($blob, $this->pdo);
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
				header('Content-Disposition', 'inline; filename=' . $blob['filename_safe']);
				break;
			default:
				header('Content-Disposition', 'attachment; filename=' . $blob['filename_safe']);
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
			$base_path = DP_WEB_ROOT . '/data_files';
		}

		$filepath = $base_path . DIRECTORY_SEPARATOR . $blob['save_path'];

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

		$sth = $this->pdo->prepare("SELECT data FROM blobs_storage WHERE blob_id = :blob_id ORDER BY id DESC");
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
			\DeskPRO\Kernel\KernelBooter::bootstrapLib(false);

			// Used in the connection factory for the doctrine connection,
			// so it doesnt try and connect twice
			$GLOBALS['DP_DEFAULT_CONNECTION_PDO'] = $this->pdo;

			$kernel_class = 'DeskPRO\\Kernel\\SysKernel';
			define('DP_INTERFACE', 'sys');

			$kernel = new $kernel_class($env, $debug);
			$kernel->boot();

			/** @var $container \Application\DeskPRO\DependencyInjection\DeskproContainer */
			$container = $kernel->getContainer();
		}

		return $container;
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
}

$file_loader = new FilestorageLoader();
$file_loader->run();
