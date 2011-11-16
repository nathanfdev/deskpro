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

		$builder->add('chart_type', 'choice', array(
			'choices'   => $chart_types,
		));
	}

	public function getName()
	{
		return 'report_dashboard_stat';
	}
}
