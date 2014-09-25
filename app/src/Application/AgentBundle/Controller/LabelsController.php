<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
1|                                                                          |
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
 * @subpackage AgentBundle
 */

namespace Application\AgentBundle\Controller;

use Application\AgentBundle\Controller\Helper\ArticleResults;
use Application\DeskPRO\App;
use Application\DeskPRO\ContentRevision\Util as ContentRevisionUtil;
use Application\DeskPRO\ContentSearch\RelatedContentFinder;
use Application\DeskPRO\Entity\Article;
use Application\DeskPRO\Entity\ArticleComment;
use Application\DeskPRO\Entity\ArticlePendingCreate;
use Application\DeskPRO\Publish\RelatedContentUpdate;
use Orb\Data\ContentTypes;
use Orb\Util\Arrays;
use Orb\Util\Strings;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles label definitions
 */
class LabelsController extends AbstractController
{
	public function listDefinitionsAction()
	{
		$rep = $this->em->getRepository('DeskPRO:LabelDef');
		return $this->createJsonResponse($rep->getAllDefinitions());
	}
}
