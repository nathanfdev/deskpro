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
 * @category Entities
 */

namespace Application\DeskPRO\Plugin\Package;

use Application\DeskPRO\Entity\PluginDef;

class Package
{
	/**
	 * @var string
	 */
	private $path;

	/**
	 * @var Manifest
	 */
	private $manifest;

	public function __construct($path)
	{
		$this->path = $path;

		$reader = ManifestReader::newFromFile($path . '/manifest.json');
		if ($reader->isError()) {
			throw new \InvalidArgumentException(sprintf(
				"Invalid manifest: %s %s",
				$reader->getErrorCode(),
				$reader->getErrorDetailAsString()
			));
		}
		$this->manifest = $reader->getManifest();
	}


	/**
	 * @return PluginDef
	 */
	public function createPluginDef()
	{
		$def = new PluginDef();
		$def->title        = $this->manifest->getTitle();
		$def->author_name  = $this->manifest->getAuthorName();
		$def->author_email = $this->manifest->getAuthorEmail();
		$def->author_link  = $this->manifest->getAuthorLink();
		$def->api_version  = $this->manifest->getApiVersion();
		$def->version      = $this->manifest->getVersion();
		$def->version_name = $this->manifest->getVersionName();
		$def->is_single    = $this->manifest->getIsSingle();

		if (strpos($this->path, DP_ROOT.'/plugins') === 0) {
			$def->native_name = basename($this->path);
		}

		return $def;
	}

	/**
	 * @return Manifest
	 */
	public function getManifest()
	{
		return $this->manifest;
	}


	/**
	 * @return string
	 */
	public function getIconFile()
	{
		return $this->path . '/res/icons/app.png';
	}
}