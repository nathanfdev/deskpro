<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\PortalBundle\Themes\Base\Controller;


use Application\PortalBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class FeedbackController extends AbstractController
{
	public function listAction(Request $request)
	{
		$count = $request->get('count', 5);

		$feedback  = $this->getFeedbackRepo()->getNewest(false, $count);
		$total = $this->getFeedbackRepo()->countNotClosedNotHidden();

		$template = 'Theme:Feedback:list.html.twig';
		if ('1' == $request->query->get('render_small')) {
			$template = 'Theme:Feedback:list_small.html.twig';
		}

		return $this->render(
			$template, array(
				'count_feedback' => $total,
				'feedback'    => $feedback
			)
		);
	}


	/**
	 * @return \Application\DeskPRO\EntityRepository\Feedback
	 */
	protected function getFeedbackRepo()
	{
		return $this->getDoctrine()->getRepository('DeskPRO:Feedback');
	}
}
