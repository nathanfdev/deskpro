<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace Application\DeskPRO\ServerFileUploads;

use Application\DeskPRO\App;
use Doctrine\ORM\EntityManager;
use Orb\Util\Env;
use Orb\Util\Numbers;
use Orb\Util\OptionsArray;

class ServerFileUploads
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */
	protected $em;


	/**
	 * @param EntityManager $em
	 */
	public function __construct(EntityManager $em)
	{
		$this->em = $em;
	}


	/**
	 * @return array
	 */
	public function getPhpVars()
	{
		$php_vars = array();

		foreach (array('file_uploads', 'upload_tmp_dir', 'upload_max_filesize', 'post_max_size') as $var) {

			$php_vars[$var] = @ini_get($var);
		}

		$php_vars['upload_tmp_dir_real'] = Env::getUploadTempDir();
		$php_vars['memory_limit']        = Env::getMemoryLimit();
		$php_vars['memory_limit_real']   = DP_REAL_MEMSIZE;

		return $php_vars;
	}


	/**
	 * @return string
	 */
	public function getEffectiveMaxUploadSize()
	{
		$effective_max = Env::getEffectiveMaxUploadSize();

		$result = Numbers::getFilesizeDisplayParts($effective_max, 'cs');
		$result = ($result['number'] > 1 ?
			floor($result['number']) :
			$result['number'])
			. ' ' . $result['symbol'];

		return $result;
	}


	/**
	 * @return string
	 */
	public function getUrlToLearnPhpIni()
	{
		return App::get('deskpro.service_urls')->get('dp.kb.editing_php_ini');
	}


	/**
	 * @return string
	 */
	public function getPhpIniPath()
	{
		return Env::getPhpIniPath();
	}


	/**
	 * @return array
	 */
	public function getRestrictions()
	{
		return array(
			'attach_user_maxsize'    => App::getSetting('core.attach_user_maxsize'),
			'attach_agent_maxsize'   => App::getSetting('core.attach_agent_maxsize'),
			'attach_user_not_exts'   => App::getSetting('core.attach_user_not_exts'),
			'attach_user_must_exts'  => App::getSetting('core.attach_user_must_exts'),
			'attach_agent_not_exts'  => App::getSetting('core.attach_agent_not_exts'),
			'attach_agent_must_exts' => App::getSetting('core.attach_agent_must_exts'),
		);
	}


	/**
	 * @return array
	 */
	public function getMovingFiles()
	{
		$moving_id = App::getContainer()->getSetting('core.filesystem_move_from_id');

		if ($moving_id) {

			if ($moving_id < 1) {

				$count_done = 0;

			} else {

				$count_done = App::getDb()->fetchColumn(
					"SELECT COUNT(*) FROM blobs WHERE id < ?",
					array($moving_id)
				);
			}

			$count_todo = App::getDb()->fetchColumn("SELECT COUNT(*) FROM blobs", array($moving_id));

			if (!$count_todo) {

				$count_todo = 1;
			}

			$count_left       = $count_todo - $count_done;
			$count_percentage = floor(($count_done / $count_todo) * 100);

		} else {

			$count_done = $count_todo = $count_left = $count_percentage = 0;
			$count_todo = App::getDb()->fetchColumn("SELECT COUNT(*) FROM blobs");
		}

		$total_size          = App::getDb()->fetchColumn("SELECT SUM(filesize) FROM blobs");
		$total_size_readable = Numbers::filesizeDisplay($total_size);

		return array(
			'id'               => $moving_id,
			'count_done'       => $count_done,
			'count_left'       => $count_left,
			'count_percentage' => $count_percentage,
			'count_todo'       => $count_todo,
			'total_size'       => $total_size_readable,
		);
	}


	/**
	 * @return bool
	 */
	public function getStorageMethod()
	{
		return App::getContainer()->getSetting('core.filestorage_method');
	}


	/**
	 * @return string
	 */
	public function getFileStoragePath()
	{
		return   App::getContainer()->getBlobDir();
	}


	/**
	 * @return string
	 */
	public function getFileUploaderUrl()
	{
		return App::getRouter()->generate('api_server_file_uploads');
	}


	/**
	 * @param $file
	 * @return array
	 */
	public function getUploadResults($file)
	{
		$upload_failed   = false;
		$is_tmp_writable = false;
		$attach_url      = '';

		$accept = App::getContainer()->getAttachmentAccepter();
		$error  = $accept->getError($file, 'agent');

		if ($error) {

			if ($error['error_code'] == 'no_file') {

				$is_tmp_writable = Env::getUploadTempDir() && is_writable(Env::getUploadTempDir());
			}

			$upload_failed = App::getContainer()->getTranslator()->phrase(
				'agent.general.attach_error_' . $error['error_code'],
				$error
			);

		} else {

			$attach_url = $accept->accept($file)->getDownloadUrl();
		}

		return array(
			'upload_failed'     => $upload_failed,
			'uploaded_file_url' => $attach_url,
			'is_tmp_writable'   => $is_tmp_writable,
		);
	}


	/**
	 * @param array $options
	 * @throws \Doctrine\DBAL\DBALException
	 * @throws \Exception
	 */
	public function switchStorage(array $options)
	{
		$options = new OptionsArray($options);

		$settings = App::$container->getSettingsHandler();
		$db = App::$container->getDb();

		$method = $options->get('method', 'db');

		switch ($options->get('method', 'db')) {
			case 'db':
				$settings->setSetting('core.filestorage_method', 'db');
				$db->executeUpdate("UPDATE blobs SET storage_loc_pref = 'db' WHERE storage_loc != 'db'");
				break;

			case 'fs':
				$settings->setSetting('core.filestorage_method', 'fs');
				$db->executeUpdate("UPDATE blobs SET storage_loc_pref = 'fs' WHERE storage_loc != 'fs'");
				break;

			case 's3':
				$settings->setSetting('core.filestorage_method', 's3');
				$db->executeUpdate("UPDATE blobs SET storage_loc_pref = 's3' WHERE storage_loc != 's3'");
				$settings->setSetting('core.filestorage_s3_key',    $options->get('s3_key', null));
				$settings->setSetting('core.filestorage_s3_secret', $options->get('s3_secret', null));
				$settings->setSetting('core.filestorage_s3_bucket', $options->get('s3_bucket', null));

				// Need to clear CSS blobs too, since the URLs will change
				\Application\DeskPRO\Style\RefreshStylesheets::refresh($this->container);

				break;
		}

		if ($method != 's3') {
			$settings->setSetting('core.filestorage_s3_key',    null);
			$settings->setSetting('core.filestorage_s3_secret', null);
			$settings->setSetting('core.filestorage_s3_bucket', null);
		}

		$settings->setSetting('core.filesystem_move_from_id', '-1');
	}


	/**
	 * @return array
	 */
	public function switchStorageStatus()
	{
		$transfer = $this->getMovingFiles();

		$to_method = App::$container->getSettingsHandler()->get('core.filestorage_method');

		if(!empty($transfer['id'])) {

			$status  = 'progress';

			switch ($to_method) {
				case 'db': $message = 'Currently transferring files to the database'; break;
				case 'fs': $message = 'Currently transferring files to the filesystem'; break;
				case 's3': $message = 'Currently transferring files AmazonS3'; break;
			}

			$message .= $transfer['count_done'] . ' of ' .  $transfer['count_todo'];
			$message .= ' (' . $transfer['count_percentage'] . '%) files have been processed.';

		} else {

			$status  = 'completed';
			$message = 'Transferring done!';
		}

		return array(
			'status'  => $status,
			'message' => $message
		);
	}
}