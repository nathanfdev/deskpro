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
use Application\DeskPRO\Entity\ReportDashboard;

class EditReportDashboardStatType extends AbstractType
{
	public function buildForm(FormBuilder $builder, array $options)
	{
		$chart_types = ReportDashboard::getChartList();

		$data_points = array_combine(range(1, 60), range(1, 60));

		$builder->add('title');
		$builder->add('chart_type', 'choice', array(
			'choices'   => $chart_types,
		));
		$builder->add('show_legend', 'choice', array(
			'required' => false,
			'choices'  => array(0 => 'No', 1 => 'Yes'),
			'multiple'  => false,
			'expanded'  => true,
		));
		$builder->add('display_grouping', 'choice', array(
			'required' => false,
			'choices'  => array(0 => 'No', 1 => 'Yes'),
			'multiple'  => false,
			'expanded'  => true,
		));
		$builder->add('number_data_points', 'choice', array(
			'choices'   => $data_points,
		));
	}

	public function getName()
	{
		return 'report_dashboard_stat';
	}
}
