<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\ApiBundle\Controller\Tickets;

use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Form\Type\Tickets\TicketType;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\Route;

/**
 * Class TicketsController.
 *
 * @Route("/tickets")
 */
class TicketsController extends CrudController
{
    public static $entity = Ticket::class;
    public static $type   = TicketType::class;

    /**
     * {@inheritdoc}
     */
    protected function persistModel($model)
    {
        // Overwrite base method to make sure ticket is persisted and has ID before persisting labels. Label uses
        // ticket id as part of its' composite primary key and requires it to be available when persisting a new label.
        if (!$model->getId()) {

            // use reflection instead of getter method because in different entities methods behave
            // differently (can return ArrayCollection, array of label object or array of strings)
            $reflection = new \ReflectionProperty(static::$entity, 'labels');
            $reflection->setAccessible(true);
            $labels = $reflection->getValue($model);
            $reflection->setValue($model, new ArrayCollection([]));
            parent::persistModel($model);
            $model->setLabels($labels->toArray());
        }

        return parent::persistModel($model);
    }
}
