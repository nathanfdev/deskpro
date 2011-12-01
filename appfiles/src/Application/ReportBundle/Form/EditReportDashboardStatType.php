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
