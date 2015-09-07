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
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\Get;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Application\DeskPRO\Entity\LabelDef;

/**
 * Class LabelsController
 */
class LabelsController extends BaseController
{
    /**
     * @ApiDoc(
     *      description="Get all labels by types",
     *      statusCodes={
     *          200="Success"
     *      }
     * )
     * @Get(
     *     "/{type}_labels",
     *     name="api_person_labels_list",
     *     requirements={
     *         "type"="person|organization|feedback"
     *     }
     * )
     * @param Request $request
     * @param string $type
     * @return View
     * @throws \LogicException
     */
    public function getLabelsAction(Request $request, $type)
    {
        /** @ToDo move below functionality into LabelDef repository after removing old code */
        $qb = $this->getDoctrine()->getManager()->createQueryBuilder();
        $qb
            ->select('l')
            ->from('DeskPRO:LabelDef', 'l')
            ->where('l.label_type = :type')
            ->setParameter('type', $type);
        $term = $request->get('term');
        if(null !== $term){
            $qb
                ->andWhere('l.label LIKE :term')
                ->setParameter('term', $term.'%');
        }
        $definitions = $qb->getQuery()->getResult();
        $labels = array_map([$this, 'labelDefinitionToString'], $definitions);

        return View::create(
            $this->createRepresentation($labels),
            Response::HTTP_OK
        );
    }

    /**
     * @param LabelDef $def
     * @return string
     */
    private function labelDefinitionToString(LabelDef $def)
    {
        return $def->getLabel();
    }
}
