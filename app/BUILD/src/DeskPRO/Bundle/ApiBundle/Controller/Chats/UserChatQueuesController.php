<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Chats;

use Application\DeskPRO\Entity\Setting;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\CrudController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Entity\UserChatQueue;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\UserChatQueueSettingsType;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\UserChatQueueType;
use DeskPRO\Bundle\VoiceBundle\Settings\ChatSettingsResolver;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class UserChatQueuesController.
 *
 * @ApiModes("all")
 * @Rest\Route("/user_chat_queues")
 * @ApiDoc(target="all", section="Chats", output="DeskPRO\Bundle\AppBundle\Entity\UserChatQueue")
 * @ApiDoc(
 *     target="postAction, putAction",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\UserChat\UserChatQueueType",
 *      "options"={
 *          "data"="DeskPRO\Bundle\AppBundle\Entity\UserChatQueue"
 *      }
 *     }
 * )
 * @ApiUserContext("admin", agent={"list", "get", "count"})
 */
class UserChatQueuesController extends CrudController
{
    public static $entity = UserChatQueue::class;
    public static $type   = UserChatQueueType::class;

    /**
     * @Rest\Get("/settings")
     *
     * @return View
     */
    public function getChatQueueSettingsAction()
    {
        return View::create($this->wrap($this->get('chat_settings_resolver')->getChatQueueSettings()));
    }

    /**
     * @Rest\Put("/settings")
     *
     * @param Request $request
     *
     * @throws InvalidFormException
     *
     * @return View
     */
    public function updateChatQueueSettingsAction(Request $request)
    {
        $form = $this->createForm(UserChatQueueSettingsType::class, $this->get('chat_settings_resolver')->getChatQueueSettings());
        $form->submit($request->request->all(), false);
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingsRepo */
        $settingsRepo = $this->getManager()->getRepository(Setting::class);
        $settingsRepo->updateSetting(ChatSettingsResolver::USER_CHAT_AGENT_TIMEOUT, $form->get('agent_timeout')->getData());
        $settingsRepo->updateSetting(ChatSettingsResolver::USER_CHAT_MAX_CHATS_COUNT, $form->get('max_chats_count')->getData());
        $settingsRepo->updateSetting(ChatSettingsResolver::USER_CHAT_DEFAULT_QUEUE, $form->get('default_queue')->getData());

        return View::create(null, Response::HTTP_NO_CONTENT);
    }
}
