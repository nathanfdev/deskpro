<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\AdminBundle\Form;

use \Application\DeskPRO\App;
use \Application\DeskPRO\Entity;

use \Orb\Util\Arrays;

use \Symfony\Component\Form;

class EditStyleForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->addRequiredOption('style');

		$this->add(new Form\TextField('title'));
		$this->add(new Form\TextField('note'));

		$style = $this->getOption('style');
		if (!$style['id']) {
			$parent_options = App::getDb()->fetchAllKeyValue("
				SELECT id, title
				FROM styles
				ORDER BY title ASC
			");

			if ($parent_options) {
				Arrays::unshiftAssoc($parent_options, 0, '(none)');
				$this->add(new Form\ChoiceField('parent_id', array('choices' => $parent_options)));
			}
		}
	}
}