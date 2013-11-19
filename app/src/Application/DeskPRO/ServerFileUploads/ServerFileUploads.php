<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
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

namespace Application\DeskPRO\ServerFileUploads;

use Application\DeskPRO\App;

use Orb\Util\Env;
use Orb\Util\Numbers;

use Doctrine\ORM\EntityManager;

class ServerFileUploads
{
	/**
	 * @var \Application\DeskPRO\ORM\EntityManager
	 */

	protected $em;

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

		$result = Numbers::getFilesizeDisplayParts($effective_max);
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
}