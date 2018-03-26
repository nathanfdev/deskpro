<?php

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
