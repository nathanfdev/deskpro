<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Labels;

use Application\DeskPRO\Entity\LabelDef;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class LabelsController.
 *
 * @ApiModes("all")
 */
class LabelsController extends BaseController
{
    /**
     * Fetch labels for given entity types and you can filter labels by some term.
     *
     * @ApiDoc(
     *     section="Labels",
     *     resourceDescription="Operations about labels",
     *     description="Get all labels by types",
     *     requirements={
     *         {
     *             "name"="type",
     *             "requirement"="ticket|person|organization|feedback|news|chat|article|download",
     *             "description"="Which entity type labels we are searching?",
     *             "dataType"="string"
     *         }
     *     },
     *     filters={
     *         {
     *             "name"="term",
     *             "requirement"="\w",
     *             "description"="Filter label by given word",
     *             "dataType"="string"
     *         }
     *     },
     *     statusCodes={
     *         200="Returned if everything is ok",
     *         400="We will return this status in case your {type} wasn't found"
     *     },
     *     output="Application\DeskPRO\Entity\LabelDef"
     *
     * )
     * @ApiUnstable()
     * @Rest\Get(
     *     "/{type}_labels",
     *     name="api_person_labels_list",
     *     requirements={
     *         "type"="task|ticket|person|organization|feedback|news|chat|article|download"
     *     }
     * )
     *
     * @param Request $request
     * @param string  $type
     *
     * @return View
     */
    public function getLabelsAction(Request $request, $type)
    {
        switch ($type) {
            case 'task':
                $labelType = LabelDef::TYPE_TASKS;
                break;
            case 'ticket':
                $labelType = LabelDef::TYPE_TICKETS;
                break;
            case 'person':
                $labelType = LabelDef::TYPE_PEOPLE;
                break;
            case 'organization':
                $labelType = LabelDef::TYPE_ORGS;
                break;
            case 'feedback':
                $labelType = LabelDef::TYPE_FEEDBACK;
                break;
            case 'news':
                $labelType = LabelDef::TYPE_NEWS;
                break;
            case 'chat':
                $labelType = LabelDef::TYPE_CHATS;
                break;
            case 'article':
                $labelType = LabelDef::TYPE_ARTICLES;
                break;
            case 'download':
                $labelType = LabelDef::TYPE_DOWNLOADS;
                break;
            default:
                throw $this->createNotFoundException();
        }

        /* @ToDo move below functionality into LabelDef repository after removing old code */
        $qb = $this->getManager()->createQueryBuilder();
        $qb
            ->select('l')
            ->from(LabelDef::class, 'l')
            ->where('l.label_type = :type')
            ->setParameter('type', $labelType)
            ->orderBy('l.label', 'asc')
        ;

        $term = $request->get('term');
        if (null !== $term) {
            $qb->andWhere('l.label LIKE :term');
            $qb->setParameter('term', '%'.$term.'%');
        }

        return View::create($this->wrap($qb->getQuery()->getResult()));
    }
}
