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

namespace Application\DeskPRO\Templating;

use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine as BaseEngine;
use Application\DeskPRO\ResourceScanner\TemplateFiles;
use Application\DeskPRO\App;

class Engine extends BaseEngine
{
	/**
	 * @var array()
	 */
	protected $_template_files_map;

	protected function _initTemplateFilesMap()
	{
		if ($this->_template_files_map !== null) {
			return;
		}

		$tf = new TemplateFiles(true);
		$this->_template_files_map = $tf->genTemplateMap();
	}

	/**
	 * @return \Application\DeskPRO\ResourceScanner\TemplateFiles
	 */
	public function getTemplateFilesMap()
	{
		$this->_initTemplateFilesMap();
		return $this->_template_files_map;
	}

	/**
	 * Checks to see if a template is a template shipped with DeskPRO.
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isDefaultTemplate($name)
	{
		$this->_initTemplateFilesMap();
		return isset($this->_template_files_map[$name]);
	}


	/**
	 * Check if a template is custom
	 *
	 * @param string $name
	 * @return bool
	 */
	public function isCustomTemplate($name)
	{
		$id = App::getDb()->fetchColumn("
			SELECT id
			FROM templates
			WHERE name = ?
			LIMIT 1
		", array($name));

		return $id ? true : false;
	}


	/**
	 * Get the default source code for a template
	 *
	 * @param string $name
	 * @return string
	 */
	public function getDefaultSource($name)
	{
		$this->_initTemplateFilesMap();
		$path = isset($this->_template_files_map[$name]) ? $this->_template_files_map[$name]['path'] : null;

		if (!$path) {
			return '';
		}

		return file_get_contents($path);
	}


	/**
	 * Get the source code for a template. This will return the custom source
	 * if its been customised, or the default if not.
	 *
	 * @param string $name
	 * @return string
	 */
	public function getSource($name)
	{
		$source = App::getDb()->fetchColumn("
			SELECT template_code
			FROM templates
			WHERE name = ?
			LIMIT 1
		", array($name));

		if ($source === false) {
			$source = $this->getDefaultSource($name);
		}

		return $source ?: '';
	}
}