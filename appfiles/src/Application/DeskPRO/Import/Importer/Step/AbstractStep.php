<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Import
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Import\Importer\Step;

use Application\DeskPRO\Import\Importer\AbstractImporter;

abstract class AbstractStep
{
	/**
	 * @var Application\DeskPRO\Import\Importer\AbstractImporter
	 */
	protected $importer;

	/**
	 * @param \Application\DeskPRO\Import\Importer\AbstractImporter $impoter
	 */
	public function __construct(AbstractImporter $impoter)
	{
		$this->importer = $impoter;
	}


	/**
	 * Get a short title for the step
	 * @return string
	 */
	abstract public function getTitle();


	/**
	 * Actually do the import work
	 */
	abstract public function run();


	/**
	 * Get the ID for this step
	 *
	 * @return string
	 */
	public function getId()
	{
		$basename = \Orb\Util\Util::getBaseClassname($this);
		$name = strtolower(str_replace('Step', '', $basename));

		return $this->importer->getId() . '_' . $name;
	}
}
