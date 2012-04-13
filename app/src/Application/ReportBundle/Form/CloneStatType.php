<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
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
 * @subpackage AdminBundle
 */

namespace Application\ReportBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilder;
use Application\DeskPRO\Entity\Stat;

class CloneStatType extends AbstractType
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
			'empty_value' => false
		));
		$builder->add('run_frequency', 'choice', array(
			'choices' => $run_frequencies,
			'expanded' => true,
		));
		$builder->add('variation', 'choice', array(
			'choices' => $variations,
			'expanded' => true,
		));
	}

	public function getName()
	{
		return 'stat';
	}
}
