<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\ChatConversation;
use Application\DeskPRO\Entity\DataStore;
use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

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
     * @param Person|mixed $person
     * @param string       $key
     *
     * @return mixed
     */
    protected function getWidgetOption($person, $key)
    {
        if (!$person instanceof Person || !$person->getId()) {
            return;
        }

        $dataStore = $this->getDoctrine()->getRepository(DataStore::class)->findOneBy([
            'name' => 'dpWidgetOptions.'.$person->getId(),
        ]);

        return $dataStore ? $dataStore->getData($key) : null;
    }

    /**
     * @param Person|mixed $person
     * @param string       $key
     * @param mixed        $value
     */
    protected function setWidgetOption($person, $key, $value)
    {
        if (!$person instanceof Person || !$person->getId()) {
            return;
        }

        $dataStore = $this->getDoctrine()->getRepository(DataStore::class)->findOneBy([
            'name' => 'dpWidgetOptions.'.$person->getId(),
        ]);

        if (!$dataStore) {
            $dataStore = new DataStore();
            $dataStore->setName('dpWidgetOptions.'.$person->getId());
        }

        $dataStore->setData($key, $value);
        $this->getManager()->persist($dataStore);
        $this->getManager()->flush($dataStore);
    }

    /**
     * @return ChatConversation
     */
    protected function getLastChat()
    {
        $person       = $this->getUser();
        $storedChatId = $this->getWidgetOption($person, 'chat_id');
        if ($storedChatId) {
            list($storedChatId) = explode(':', $storedChatId);

            $conversation = $this->getManager()->getRepository(ChatConversation::class)->find($storedChatId);
            if ($conversation && !$conversation->getDateEnded()) {
                return $conversation;
            }
        }

        return;
    }

    /**
     * {@inheritdoc}
     */
    public function getUser()
    {
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($jwtPayload = $request->headers->get('X-Jwt-Token')) {
            $person = $this->get('widget_jwt_decoder')->getPersonFromJwtPayload($jwtPayload);
        } else {
            $person = parent::getUser();
        }

        return $person;
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Person
     */
    protected function getPersonRepository()
    {
        return $this->getDoctrine()->getRepository(Person::class);
    }
}
