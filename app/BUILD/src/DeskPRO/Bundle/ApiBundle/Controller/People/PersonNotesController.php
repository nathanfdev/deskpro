<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\PersonNote;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDocSection;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\OutputEntity;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\PersonNoteType;
use FOS\RestBundle\Controller\Annotations;

/**
 * Class PersonNotesController.
 *
 * @ApiDocSection("People")
 * @OutputEntity("Application\DeskPRO\Entity\PersonNote")
 * @ApiModes("all")
 * @Annotations\Route("/people/{parentId}/notes")
 */
class PersonNotesController extends CrudSubController
{
    public static $exposeOnly     = ['list', 'post', 'put', 'delete'];
    public static $entity         = PersonNote::class;
    public static $type           = PersonNoteType::class;
    public static $parentProperty = 'person';
}
