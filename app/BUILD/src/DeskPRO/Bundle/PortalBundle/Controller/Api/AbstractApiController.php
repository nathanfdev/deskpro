<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * Class AbstractApiController.
 */
abstract class AbstractApiController extends FOSRestController
{
    /**
     * @param mixed $data
     *
     * @return ApiWrapper
     */
    protected function wrap($data)
    {
        return new ApiWrapper($data);
    }

    /**
     * @param Form $form
     *
     * @return View
     */
    protected function generateFormErrorsResponse(Form $form)
    {
        $errors = $this->get('form_error.form_errors_generator.api')->generateFormErrors($form);

        return new View($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param string $event_name
     * @param Event  $event
     */
    protected function dispatch($event_name, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($event_name, $event);
    }

    /**
     * @return null|\Symfony\Component\Security\Core\Authentication\Token\TokenInterface
     */
    protected function getToken()
    {
        $token = $this->get('security.token_storage')->getToken();
        if (!$token instanceof UsernamePasswordToken || $token->getProviderKey() !== 'portal_api') {
            throw new AccessDeniedHttpException('Invalid token');
        }

        return $token;
    }

    /**
     * @return Session|null
     */
    protected function getApiSession()
    {
        $session = $this->getDoctrine()->getRepository(Session::class)->find($this->getToken()->getCredentials());
        if (!$session) {
            throw new AccessDeniedHttpException('Invalid token');
        }
        $ss = $this->getContainer()->getSession();
        if ($ss->has('impersonate')) {
            $person = $this->getContainer()->get('doctrine.orm.default_entity_manager')->find(
                Person::class,
                $ss->get('impersonate')
            );
            if ($person) {
                $session->setPerson($person);
            }
        }

        return $session;
    }

    /**
     * @return DeskproContainer|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return \DeskPRO\Bundle\PortalBundle\Brand\BrandContainer
     */
    protected function getBrandContainer()
    {
        return $this->get('brand_stack')->getActive();
    }

    /**
     * @return EntityManager
     */
    protected function getManager()
    {
        return $this->get('doctrine.orm.default_entity_manager');
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    protected function getWidgetOption($key)
    {
        $dataStore = $this->getDoctrine()->getRepository(DataStore::class)->findOneBy([
            'name' => $this->getWidgetOptionDataStoreName(),
        ]);

        return $dataStore ? $dataStore->getData($key) : null;
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    protected function setWidgetOption($key, $value)
    {
        $dataStore = $this->getDoctrine()->getRepository(DataStore::class)->findOneBy([
            'name' => $this->getWidgetOptionDataStoreName(),
        ]);

        if (!$dataStore) {
            $dataStore = new DataStore();
            $dataStore->setName($this->getWidgetOptionDataStoreName());
        }

        $dataStore->setData($key, $value);
        $this->getManager()->persist($dataStore);
        $this->getManager()->flush($dataStore);
    }

    /**
     * @return string
     */
    protected function getWidgetOptionDataStoreName()
    {
        return 'dpWidgetOptions.'.$this->getToken()->getCredentials();
    }
}
