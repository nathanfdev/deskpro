<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\People;

use Application\DeskPRO\Entity\PersonNote;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudSubController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\People\PersonNoteType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PersonNotesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/people/{parentId}/notes")
 * @ApiDoc(target="all", section="People", output="Application\DeskPRO\Entity\PersonNote")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\People\PersonNoteType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\PersonNote",
 *          "person"="Application\DeskPRO\Entity\Person",
 *          "agent"="Application\DeskPRO\Entity\Person"
 *      }
 *     }
 * )
 */
class PersonNotesController extends CrudSubController
{
    public static $entity         = PersonNote::class;
    public static $type           = PersonNoteType::class;
    public static $parentProperty = 'person';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->findParentOr404(),
            'agent'  => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
