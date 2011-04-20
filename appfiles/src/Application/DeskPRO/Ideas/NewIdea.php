<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage UserBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Ideas;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Visitor;

/**
 * New idea acts as the processor and domain object for a newidea form
 */
class NewIdea
{
	public $category_id = 0;
	public $title = '';
	public $content = '';
	public $votes = 1;

	/**
	 * @var \Application\DeskPRO\Entity\Person
	 */
	protected $person;

	/**
	 * @var \Application\DeskPRO\Entity\Visitor
	 */
	protected $visitor;

	public function __construct(Person $person = null, Visitor $visitor = null)
	{
		if ($person AND $person['id']) {
			$this->person = $person;
		}

		$this->visitor = $visitor;
	}
}