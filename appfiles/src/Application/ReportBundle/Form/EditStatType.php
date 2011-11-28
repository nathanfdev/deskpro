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

namespace Application\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Application\DeskPRO\Entity\Stat;

class EditStatType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$variations = array();
		foreach (Stat::getAvailableVariations() as $variation) {
			$variations[$variation] = ucwords($variation);
		}

		$builder->add('title', 'text');
		$builder->add('variation', 'choice', array(
			'choices' => $variations,
			'expanded' => true,
		));
		$builder->add('starred', 'checkbox', array('required' => false));
		$builder->add('disabled', 'checkbox', array('required' => false));
	}

	public function getName()
	{
		return 'stat';
	}
}
