<?php

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\ApiDoc\Annotation\ApiUnstable;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiModes;
use DeskPRO\Bundle\AppBundle\Annotation\ActionPermissions\Annotation\ApiUserContext;
use DeskPRO\Bundle\AppBundle\Form\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationType;
use DeskPRO\Bundle\AppBundle\Notification\Event\People\UpdateOnlineEvent;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SessionController.
 *
 * @ApiModes("all")
 */
class SessionController extends BaseController
{
    /**
     * @Rest\Post("/get_session", name="api_post_get_session")
     *
     * @ApiUnstable()
     * @ApiUserContext("open")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getSessionAction(Request $request)
    {
        $form = $this->get('form.factory')->createNamedBuilder(null, AuthenticationType::class)->getForm();
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $email = $form->getData()['email'];

        /** @var \Application\DeskPRO\EntityRepository\Person $person_repository */
        $person_repository = $this->getRepository(Person::class);
        $person            = $person_repository->findOneByEmail($email);

        $session_code = $request->cookies->get('dpsid-agent');

        $request->getSession()->set('auth_person_id', $person->getId());
        $data = session_encode();

        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->getDoctrine()->getRepository(Session::class);
        $session            = $session_repository->getSessionFromCode($session_code);
        if (!$session) {
            $session = new Session();
        }

        $session->setData($data);
        $session->setPerson($person);

        $em = $this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();

        $online_data = $this->get('data.agent')->getAgentsOnlineStatus();
        $this->get('event_dispatcher')->dispatch(UpdateOnlineEvent::EVENT_NAME, new UpdateOnlineEvent($online_data));

        $response = new JsonResponse([
            'sessionId' => $session->getSessionCode(),
        ]);
        $response->headers->setCookie(new Cookie('dpsid-agent', $session->getSessionCode()));

        return $response;
    }
}
