<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Apps;

use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Entity\ZapierHook;
use DeskPRO\Bundle\AppBundle\Form\Type\Zapier\ZapierHookType;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PeopleController.
 *
 * @ApiModes("all")
 * @Rest\Route("/apps/zapier/hooks")
 * @ApiDoc(target="all", section="Apps", output="DeskPRO\Bundle\AppBundle\Entity\ZapierHook")
 * @ApiDoc(
 *     target="postAction,putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\Zapier\ZapierHookType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\ZapierHook"
 *      }
 *     }
 * )
 */
class ZapierHooksController extends CrudController
{
    public static $exposeOnly = ['post', 'delete'];
    public static $entity     = ZapierHook::class;
    public static $type       = ZapierHookType::class;

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
