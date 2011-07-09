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

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class NewCommentFormType extends AbstractType
{
	protected $person;

	public function __construct($person)
	{
		$this->person = $person;
	}

	public function buildForm(FormBuilder $builder, array $options)
	{
		if (!$this->person['id']) {
			$builder->add('name', 'text');
			$builder->add('email', 'text');
		}
		$builder->add('content', 'textarea');
	}

	public function getName()
	{
		return 'new_comment';
	}
}