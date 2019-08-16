<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Content;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\RequireAgentPermissions;
use DeskPRO\Bundle\AppBundle\Entity\ContentTemplate;
use DeskPRO\Bundle\AppBundle\Form\Type\Content\ContentTemplateType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ContentTemplatesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/content_templates")
 * @ApiDoc(target="all", section="Content", output="Application\DeskPRO\Entity\ContentTemplate")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Content\ContentTemplateType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ContentTemplate"
 *      }
 *     }
 * )
 * @RequireAgentPermissions()
 */
class ContentTemplatesController extends CrudController
{
    public static $entity    = ContentTemplate::class;
    public static $type      = ContentTemplateType::class;
    public static $listSort  = 'id';
    public static $listOrder = 'asc';

    /**
     * {@inheritdoc}
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options = array_merge($options, [
            'person' => $this->getUser(),
        ]);

        return parent::handleForm($model, $request, $options);
    }
}
