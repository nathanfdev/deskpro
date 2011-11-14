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
		$run_frequencies = array();
		foreach (Stat::getAvailableRunFrequencies() as $frequency) {
			$run_frequencies[$frequency] = ucwords($frequency);
		}

		$variations = array();
		foreach (Stat::getAvailableVariations() as $variation) {
			$variations[$variation] = ucwords($variation);
		}

		$builder->add('title', 'text');
		$builder->add('grouping_ref', 'choice', array(
			'choices' => Stat::getGroupingReferencesDisplay(),
			'empty_value' => true
		));
		$builder->add('run_frequency', 'choice', array(
			'choices' => $run_frequencies,
			'expanded' => true,
		));
		$builder->add('variation', 'choice', array(
			'choices' => $variations,
			'expanded' => true,
		));
		$builder->add('disabled', 'checkbox', array('required' => false));
	}

	public function getName()
	{
		return 'stat';
	}
}
