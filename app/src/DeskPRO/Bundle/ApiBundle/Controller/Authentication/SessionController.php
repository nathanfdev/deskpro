<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller\Authentication;

use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\ApiBundle\Controller\BaseController;
use DeskPRO\Bundle\ApiBundle\Security\Authentication\ApiAuthenticator;
use DeskPRO\Bundle\AppBundle\Error\ApiErrors;
use DeskPRO\Bundle\AppBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\AppBundle\Form\Type\AuthenticationType;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class SessionController.
 */
class SessionController extends BaseController
{
    /**
     * @Post("/get_session", name="api_post_get_session")
     *
     * @param Request $request
     *
     * @return View
     */
    public function getSessionAction(Request $request)
    {
        $form = $this->createForm(new AuthenticationType());
        $form->submit($request->request->all());

        if (!$form->isValid()) {
            throw new InvalidFormException($form);
        }

        $data     = $form->getData();
        $email    = $data['email'];
        $password = $data['password'];

        // todo temporary controller
        // todo just for agents for now
        $auth_result = $this->get('dp_authentication_manager.agent')->authenticateFormLogin($email, $password);

        if (!$auth_result->isValid()) {
            if (!$auth_result->isValid()) {
                /** @var \Application\DeskPRO\EntityRepository\Person $person_repository */
                $person_repository = $this->getRepository('DeskPRO:Person');
                $person            = $person_repository->findOneByEmail($email);
                if (!$person) {
                    $this->throwNoPerson();
                }

                $this->throwBadCredentials();
            }
        }

        $identity  = $auth_result->getIdentity();
        $person_id = $identity->getIdentity();

        if (!$person_id) {
            $this->throwNoPerson();
        }

        $em     = $this->get('doctrine.orm.default_entity_manager');
        $person = $em->getRepository('DeskPRO:Person')->find($person_id);

        if (!$person) {
            $this->throwNoPerson();
        }

        $session_code = $request->cookies->get('dpsid-agent');

        $request->getSession()->set('auth_person_id', $person->getId());
        $data = session_encode();

        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->getDoctrine()->getRepository('DeskPRO:Session');
        $session            = $session_repository->getSessionFromCode($session_code);
        if (!$session) {
            $session = new Session();
        }

        $session->setData($data);
        $session->setPerson($person);

        $em = $this->getDoctrine()->getManager();
        $em->persist($session);
        $em->flush();

        return View::create(null, Response::HTTP_OK);
    }

    protected function throwNoPerson()
    {
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ApiErrors::NO_PERSON);
    }

    protected function throwBadCredentials()
    {
        throw new UnauthorizedHttpException(ApiAuthenticator::HTTP_REALM, ApiErrors::BAD_CREDENTIALS);
    }
}
