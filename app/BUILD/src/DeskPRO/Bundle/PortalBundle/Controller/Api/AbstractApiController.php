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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Session;
use DeskPRO\Bundle\AppBundle\Serializer\ApiWrapper;
use DeskPRO\Bundle\AppBundle\Form\Error\ErrorMessageFactory;
use Doctrine\ORM\EntityManager;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Class AbstractApiController.
 */
abstract class AbstractApiController extends FOSRestController
{
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
        $generator = $this->get('form_error.form_errors_generator');
        $errors    = $generator->generateFormErrors($form, ErrorMessageFactory::PREFIX_API);

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
     * @param Request $request
     *
     * @return Session|null
     */
    protected function getApiSession(Request $request)
    {
        /** @var \Application\DeskPRO\EntityRepository\Session $session_repository */
        $session_repository = $this->getDoctrine()->getRepository('DeskPRO:Session');
        $session_code       = $request->query->get('__sid');

        $session = null;
        if ($session_code) {
            $session = $session_repository->getSessionFromCode($session_code);
        }
        if (!$session) {
            throw new BadRequestHttpException('User session not found');
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
}
