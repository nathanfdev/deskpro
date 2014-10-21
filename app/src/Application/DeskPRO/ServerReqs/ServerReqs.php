<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace Application\DeskPRO\ServerReqs;

use Application\DeskPRO\App;
use Application\InstallBundle\Install\ServerChecks;
use Doctrine\ORM\EntityManager;
use Orb\Util\Env;

class ServerReqs
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

	/** @var array */
	protected $web_checks = array();
	/** @var array */
	protected $cli_checks = array();

	/**
	 * @var \Application\InstallBundle\Install\ServerChecks
	 */

	protected $server_check;

	/**
	 * @var array
	 */

	protected $checksTable = array();

	public function __construct(EntityManager $em)
	{
		$this->em = $em;

		$this->server_check = new ServerChecks();
		$this->server_check->checkServer();

		$this->web_checks = $this->_generateMessages(array_merge(
			$this->server_check->getErrors(), $this->server_check->getOptionals()
		), true);

		if (file_exists(dp_get_data_dir() . '/cli-server-reqs-check.dat')) {

			$data = file_get_contents(dp_get_data_dir() . '/cli-server-reqs-check.dat');
			$data = @unserialize($data);

			$this->cli_checks = $this->_generateMessages($data['checks']);
		}
	}

	/**
	 * @return array
	 */

	public function getWebChecks()
	{
		return $this->web_checks;
	}

	/**
	 * @return array
	 */

	public function getCliChecks()
	{
		return $this->cli_checks;
	}

	/**
	 * @param array $errors
	 * @param bool $includeOptionals
	 * @return array
	 */
	protected function _generateMessages(array $errors, $includeOptionals = false)
	{
		$this->_generateCheckTable($includeOptionals);

		$result = array();

		foreach ($this->checksTable as $checkKey => $check) {

			$arr                = array();
			$arr['description'] = $check['description'];

			if (isset($errors[$checkKey])) {

				$arr['error']    = $check['error'];
				$arr['readMore'] = $check['readMore'];

				if (isset($check['recommendation'])) {

					$arr['recommendation'] = $check['recommendation'];
				}
			}

			$result[] = $arr;
		}

		return $result;
	}

	/**
	 * @param bool $includeOptionals
	 */
	protected function _generateCheckTable($includeOptionals = false)
	{
		$ini_path = Env::getPhpIniPath();

		$is_win   = App::getContainer()->getSystemService('instance_ability')->isWindows();
		$data_dir = dp_get_data_dir();

		$incorrect_paths = '';

		foreach (array('', 'backups', 'debug', 'files', 'logs', 'tmp') as $dir) {

			$path = dp_get_data_dir() . DIRECTORY_SEPARATOR . $dir;

			if (!is_dir($path)) {

				@mkdir($path, 0777, true);
				@chmod($path, 0777);
			}

			if (!is_dir($path)) {

				$incorrect_paths .= "&bull; $path does not exist<br/>";

			} elseif (!is_writable($path)) {

				$incorrect_paths .= "&bull; $path is not writable<br/>";
			}
		}

		$linux_solve_incorrect_paths = '';

		if (!$is_win) {

			$linux_solve_incorrect_paths = '<p>
					On Linux systems, you can run this command from the terminal:
					<code>chmod -R 0777 ' . $data_dir . '</code>
				</p>';
		}

		$this->checksTable = array(
			'php_version'            => array(
				'description' => 'Check that the <a href="http://php.net/">PHP</a> version is &gt;= 5.3.2',
				'error'       => 'DeskPRO requires PHP 5.3.2. You have ' . phpversion(),
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_php_version'),
			),
			'pdo_ext'                => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/pdo.installation.php">PDO extension</a> is enabled and has the MySQL driver installed',
				'error'       => 'DeskPRO requires the PDO extension and the MySQL driver',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_pdo_ext'),
			),
			'mbstring_ext'              => array(
				'description' => 'Check that <a href="http://php.net/manual/en/mbstring.installation.php">mbstring</a> extension is installed',
				'error'       => 'DeskPRO requires the mbstring extension to be installed and enabled.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_mbstring_ext'),
			),
			'iconv_ext'              => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/iconv.installation.php">iconv</a> or <a href="http://php.net/manual/en/mbstring.installation.php">mbstring</a> extension is installed',
				'error'       => 'DeskPRO requires the iconv or mbstring extension to be installed and enabled.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_iconv_ext'),
			),
			'json_ext'               => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/json.installation.php">json_encode extension</a> is installed',
				'error'       => 'DeskPRO requires the json_encode extension.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_json_ext'),
			),
			'session_start'          => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/session.installation.php">session extension</a> is installed',
				'error'       => 'DeskPRO requires the session extension.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_session_ext'),
			),
			'ctype_ext'              => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/ctype.installation.php">ctype extension</a> is installed',
				'error'       => 'DeskPRO requires ctype extension.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_ctype_ext'),
			),
			'dom_ext'                => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/dom.setup.php">dom extension</a> is installed',
				'error'       => 'DeskPRO requires DOM extension.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_dom_ext'),
			),
			'tokenizer_ext'          => array(
				'description' => 'Check that the <a href="http://php.net/manual/en/tokenizer.installation.php">tokenizer extension</a> is installed',
				'error'       => 'DeskPRO requires tokenizer extension.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_tokenizer_ext'),
			),
			'image_manip'            => array(
				'description' => 'Check that an image manipulation extension is installed (<a href="http://php.net/manual/en/imagick.installation.php">Imagick</a>, <a href="http://php.net/manual/en/gmagick.installation.php">Gmagick</a>, or <a href="http://php.net/manual/en/image.installation.php">GD</a>)',
				'error'       => 'DeskPRO requires one of the following extensions: Imagick, Gmagick or GD.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_image_manip'),
			),
			'php_functions'          => array(
				'description' => 'Check for disabled functions',
				'error'       => 'We have detected the <code><a href="http://php.net/manual/en/ini.core.php#ini.disable-functions">disable_functions</a></code> directive in your php.ini file ' . $ini_path . '. If you want to use the automatic upgrade feature, you must edit your php.ini and remove the disable_functions directive. These functions are required for automatic upgrades: escapeshellarg, exec, passthru, chdir and proc_open.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_disabled_functions'),
			),
			'memory_limit'           => array(
				'description' => 'Check that PHP\'s <a href="http://php.net/manual/en/ini.core.php#ini.memory-limit">memory limit</a> is at least 128 MB',
				'error'       => 'DeskPRO requires PHP\'s memory_limit option to be at least 128 MB. Edit your php.ini file (<code>' . $ini_path . '</code>) to increase the limit.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_memory_limit'),
			),
			'upload_tmp_dir'         => array(
				'description' => 'Check that the temporary upload directory is writable',
				'error'       => 'We have detected the <code><a href="http://php.net/manual/en/ini.core.php#ini.upload-tmp-dir">upload_tmp_dir</a></code> directive in your php.ini file (<code>' . $ini_path . '</code>) contains an invalid value. The temporary upload directory must be writable by the web server for uploads to be accepted. If you do not fix this problem, you will not be able to add attachments to tickets or articles or upload any other kind of file.',
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_upload_tmp_dir'),
			),
			'data_write'             => array(
				'description' => 'Check that the data directory is writable',
				'error'       => 'The data directory ' . $data_dir . ' and all sub-directories must be writable.<br/>' . $incorrect_paths . $linux_solve_incorrect_paths,
				'readMore'    => App::get('deskpro.service_urls')->get('dp.kb.install.error_data_dir'),
			),
			'openssl_ext'            => array(
				'description'    => 'Checking for the <a href="http://www.php.net/manual/en/openssl.installation.php">OpenSSL</a> extension',
				'error'          => 'We recommend installing the OpenSSL extension so you can use web resources that require a secure connection (such as Gmail or Google Apps, secure email servers, Facebook or Twitter).',
				'readMore'       => App::get('deskpro.service_urls')->get('dp.kb.install.error_openssl'),
				'recommendation' => true,
			),
			'magic_quotes_gpc_check' => array(
				'description'    => 'Checking if <a href="http://www.php.net/manual/en/security.magicquotes.disabling.php">magic_quotes_gpc</a> is disabled',
				'error'          => 'We recommend disabling <code>magic_quotes_gpc</code> in your php.ini for a small performance improvement. (Your php.ini file is located at <code>' . $ini_path . '</code>)',
				'readMore'       => App::get('deskpro.service_urls')->get('dp.kb.install.error_magic_quotes'),
				'recommendation' => true,
			),
		);

		$recommendOpcache = version_compare(phpversion(), '5.5.0', '<')
			? (\Orb\Util\Env::isWindows()
				? '<a href="http://www.php.net/manual/en/book.wincache.php">WinCache extension</a>'
				: '<a href="http://www.php.net/manual/en/apc.installation.php">APC extension</a>' )
			: '<a href="http://php.net/manual/book.opcache.php">OPcache extension</a>';

		$this->checksTable['apc_check'] = array(
			'description'    => 'Checking for opcode cache storage',
			'error'          => sprintf('We recommend installing the %s to dramatically improve performance.', $recommendOpcache),
			'readMore'       => App::get('deskpro.service_urls')->get('dp.kb.install.error_error_apc'),
			'recommendation' => true,
		);


		if ($includeOptionals) {
			$this->addOptionals();
		}
	}

	/**
	 * optional extensions
	 */
	protected function addOptionals()
	{
//		$this->checksTable['test'] = array(
//			'description'    => 'Checking test',
//			'error'          => 'We recommend test',
//			'readMore'       => 'read more',
//			'recommendation' => true,
//		);
	}
}