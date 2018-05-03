<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Glossary;

use Application\DeskPRO\Entity\GlossaryWordDefinition;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Form\Type\Glossary\GlossaryWordDefinitionType;
use FOS\RestBundle\Controller\Annotations as Rest;

/**
 * Class GlossaryController.
 *
 * @ApiModes("all")
 * @Rest\Route("/glossary")
 * @ApiDoc(target="all", section="Glossary", output="Application\DeskPRO\Entity\GlossaryWordDefinition")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Glossary\GlossaryWordDefinitionType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\GlossaryWordDefinition"
 *      }
 *     }
 * )
 */
class GlossaryController extends CrudController
{
    public static $entity    = GlossaryWordDefinition::class;
    public static $type      = GlossaryWordDefinitionType::class;
    public static $listOrder = 'asc';
}
