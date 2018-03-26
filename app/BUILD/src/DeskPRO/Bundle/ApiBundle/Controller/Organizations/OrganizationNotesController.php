<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Organizations;

use Application\DeskPRO\Entity\OrganizationNote;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationNoteType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class OrganizationNotesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/organizations/{parentId}/notes")
 * @ApiDoc(target="all", section="Organizations", output="Application\DeskPRO\Entity\OrganizationNote")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Organizations\OrganizationNoteType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\OrganizationNote",
 *          "organization"="Application\DeskPRO\Entity\Organization",
 *          "agent"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class OrganizationNotesController extends CrudSubController
{
    public static $entity         = OrganizationNote::class;
    public static $parentProperty = 'organization';
    public static $type           = OrganizationNoteType::class;

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'organization' => $this->findParentOr404(),
            'agent'        => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
