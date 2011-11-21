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

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

use Orb\Util\Arrays;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;

class EditLanguageType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$builder->add('title', 'text');

		$pack_choices = new \Symfony\Component\Form\Extension\Core\ChoiceList\ArrayChoiceList(function() {
			$pack_reader = new \Application\DeskPRO\ResourceScanner\LanguagePacks();
			return $pack_reader->getPacks();
		});

		$builder->add('language_package', 'choice', array(
			'choice_list' => $pack_choices
		));

		$builder->add('locale', 'text');
	}

	public function getName()
	{
		return 'language';
	}
}
