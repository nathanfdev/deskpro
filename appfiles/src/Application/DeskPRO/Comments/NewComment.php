<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage Comments
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DeskPRO\Comments;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

class NewComment
{
	protected $class;
	protected $assignments;

	public $name = '';
	public $email = '';
	public $content = '';

	public function __construct($class, array $assignments)
	{
		$this->class = $class;
		$this->assignments = $assignments;
	}

	public function save()
	{
		$obj                  = new $this->class();
		$obj['name']          = $this->name;
		$obj['email']         = $this->email;
		$obj['content']       = $this->content;
		$obj['status']        = 'visible';
		$obj['date_created']  = new \DateTime();

		foreach ($this->assignments as $k => $v) {
			$obj[$k] = $v;
		}

		App::getOrm()->persist($obj);
		App::getOrm()->flush();

		return $obj;
	}
}