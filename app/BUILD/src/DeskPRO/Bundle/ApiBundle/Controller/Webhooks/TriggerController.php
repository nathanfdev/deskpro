<?php namespace DeskPRO\Bundle\ApiBundle\Controller\Webhooks;

use Application\DeskPRO\Entity\TicketTrigger;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\ApiBundle\Controller\Webhooks\TriggerFormType;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;


/**
 * @ApiModes("all")
 * @Rest\Route("/webhooks/triggers")
 * @ApiDoc(target="all", section="Webhooks", output="Application\DeskPRO\Entity\TicketTrigger")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="\Application\DeskPRO\Entity\TicketTrigger",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\TicketTrigger"
 *      }
 *     }
 * )
 */
class TriggerController extends CrudController
{
    public static $exposeOnly = ['list', 'get', 'post', 'delete', 'put'];
    public static $entity     = TicketTrigger::class;
    public static $type     = TriggerFormType::class;

    /**
     * @param object $model
     * @param Request $request
     * @param array $options
     * @return \FOS\RestBundle\View\View
     */
    protected function handleForm($model, Request $request, array $options = [])
    {
        $options[TriggerFormType::OPTION_DEFAULT_TITLE] = 'Webhook trigger';
        $options[TriggerFormType::OPTION_ENABLE_WEBHOOK_PROPS] = true;
        return parent::handleForm($model, $request, $options);
    }
}
