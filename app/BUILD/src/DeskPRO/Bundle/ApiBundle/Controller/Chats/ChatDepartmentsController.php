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

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\AbstractDepartmentsController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class ChatDepartmentsController.
 *
 * @ApiModes("all")
 * @Rest\Route("/chat_departments")
 * @ApiDoc(target="all", section="Departments", output="DeskPRO\Bundle\AppBundle\Serializer\Model\Department")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *       "class"="DeskPRO\Bundle\AppBundle\Form\Type\DepartmentType",
 *       "options"={
 *           "data"="Application\DeskPRO\Entity\Department",
 *           "type"="chat"
 *       }
 *     }
 * )
 */
class ChatDepartmentsController extends AbstractDepartmentsController
{
    protected static $property       = 'is_chat_enabled';
    protected static $departmentType = 'chat';

    /**
     * {@inheritdoc}
     */
    protected function getAllowedDepartments()
    {
        return $this->get('data.departments')->getChatDepartmentsForPerson($this->getUser());
    }
}
