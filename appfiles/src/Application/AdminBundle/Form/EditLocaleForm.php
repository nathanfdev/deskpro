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

class EditLocaleForm extends \Symfony\Component\Form\Form
{
	protected function configure()
	{
		$this->addRequiredOption('locale');

		$this->add(new Form\TextField('title'));
		$this->add(new Form\TextField('locale'));

		$lang_options = App::getDb()->fetchAllKeyValue("
			SELECT id, title
			FROM languages
			ORDER BY title ASC
		");

		if ($lang_options) {
			$this->add(new Form\ChoiceField('language_id', array('choices' => $lang_options)));
		}
	}
}