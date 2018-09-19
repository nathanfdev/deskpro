<?php

namespace DeskPRO\Bundle\MessengerBundle\Controller;

use Application\DeskPRO\Entity\ChatConversation;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiDoc;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatConversationType;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class ChatController.
 *
 * @ApiModes("all")
 * @ApiUserContext("open")
 * @ApiDoc(
 *     target="createChat",
 *     input={
 *      "class"="DeskPRO\Bundle\AppBundle\Form\Type\UserChat\ChatConversationType",
 *      "options"={
 *          "data"="Application\DeskPRO\Entity\ChatConversation"
 *      }
 *     }
 * )
 * @Rest\Route("/chat")
 */
class ChatController extends BaseController
{
    /**
     * @param Request $request
     * @Rest\Post("")
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function createChatAction(Request $request)
    {
        $chatConversation = new ChatConversation();

        $form = $this->container->get('form.factory')->create(ChatConversationType::class, $chatConversation);
        $form->submit($request->request->all());
        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $this->em()->persist($chatConversation);
        $this->em()->flush();

        return View::create($this->wrap($chatConversation), Response::HTTP_CREATED);
    }

    /**
     * @param $idToken
     *
     * @ApiDoc(
     *     section="Messenger",
     *     resourceDescription="ending chat",
     *     statusCodes={
     *         200="Returned if everything is ok"
     *     },
     *     requirements={
     *          {
     *              "name"="idToken",
     *              "requirement"="[a-zA-Z0-9\\-]+",
     *              "description"="id-accessToken to find a chat",
     *              "dataType"="string"
     *          }
     *      }
     * )
     *
     * @Rest\Patch("/{idToken}/end", requirements={"idToken"="(\d+)\-([a-zA-Z0-9]{30})"})
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     *
     * @return View
     */
    public function endChatAction($idToken)
    {
        $chat = $this->findChatByIdToken($idToken);

        $chat->setEndedBy(ChatConversation::ENDED_USER)->setStatus(ChatConversation::STATUS_ENDED);

        $this->em()->persist($chat);
        $this->em()->flush();

        return View::create(['success' => true]);
    }

    /**
     * @param $idToken
     *
     * @return ChatConversation|null|object
     */
    private function findChatByIdToken($idToken)
    {
        list($id, $accessToken) = explode('-', $idToken);
        $chat                   = $this->getRepository(ChatConversation::class)->findOneBy(['id' => $id, 'accessToken' => $accessToken]);
        if (!$chat) {
            throw $this->createEntityNotFoundExceptionMessage(ChatConversation::class, $idToken);
        }

        return $chat;
    }

    /**
     * @return EntityManager
     */
    protected function em()
    {
        /** @var EntityManager $em */
        $em = $this->get('doctrine.orm.default_entity_manager');

        return $em;
    }
}
