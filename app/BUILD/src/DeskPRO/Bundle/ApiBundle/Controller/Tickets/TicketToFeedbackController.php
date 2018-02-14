<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\TicketToFeedback;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketToFeedbackType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TicketFeedbackLinksController.
 *
 * @ApiModes("all")
 * @Rest\Route("/tickets/{parentId}/feedback_links")
 * @ApiDoc(target="all", section="Tickets", output="DeskPRO\Bundle\AppBundle\Entity\TicketToFeedback")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketToFeedbackType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\TicketToFeedback",
 *          "ticket"="Application\DeskPRO\Entity\Ticket",
 *          "feedback"="Application\DeskPRO\Entity\Feedback",
 *          "person"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class TicketToFeedbackController extends AbstractTicketsCrudSubController
{
    public static $entity         = TicketToFeedback::class;
    public static $type           = TicketToFeedbackType::class;
    public static $parentProperty = 'ticket';
    public static $listSort       = 'id';
    public static $listOrder      = 'asc';
    public static $sortOptions    = [
        'date_created' => 'date_created',
        'date'         => 'date_created', // alias
    ];

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'ticket' => $this->findParentOr404(),
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
