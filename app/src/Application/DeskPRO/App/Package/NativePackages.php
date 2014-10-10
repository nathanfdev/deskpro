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
 * @category Entities
 */

namespace Application\DeskPRO\App\Package;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Doctrine\ORM\EntityManager;

class NativePackages
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $em;

	/**
	 * @var \Application\DeskPRO\BlobStorage\DeskproBlobStorage
	 */
	private $blob_storage;

	/**
	 * @var string
	 */
	private $root_path;

	/**
	 * @var string[]
	 */
	private $native_names;

	/**
	 * @param EntityManager      $em
	 * @param DeskproBlobStorage $blob_storage
	 * @param string             $root_path
	 */
	public function __construct(EntityManager $em, DeskproBlobStorage $blob_storage, $root_path = null)
	{
		$this->em = $em;
		$this->blob_storage = $blob_storage;

		if (!$root_path) {
			$root_path = DP_ROOT . '/apps';
		}
		$this->root_path = $root_path;
	}


	/**
	 * @return string[]
	 */
	public function getPackageNames()
	{
		if ($this->native_names !== null) {
			return $this->native_names;
		}

		$this->native_names = array();
		$dir = dir($this->root_path);

		while (($f = $dir->read()) !== false) {
			$manifest_path = $this->root_path . '/' . $f . '/manifest.json';
			if (file_exists($manifest_path)) {
				$this->native_names[] = $f;
			}
		}

		return $this->native_names;
	}
}