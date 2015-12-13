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

/**
 * DeskPRO.
 */
namespace DeskPRO\Bundle\PortalBundle\Controller\Api;

use DeskPRO\Bundle\PortalBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class AbstractApiController.
 */
abstract class AbstractApiController extends AbstractController
{
    /**
     * @param $data
     *
     * @return array
     */
    protected function dataSerialize($data)
    {
        return $this->get('data_serializer')->serialize($data);
    }

    /**
     * @param Form $form
     *
     * @return JsonResponse
     */
    protected function generateFormErrorsResponse(Form $form)
    {
        $generator = $this->get('api_error.form_errors_generator');
        $errors    = $generator->generateFormErrors($form);

        return new JsonResponse($errors, Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param string $event_name
     * @param Event  $event
     */
    protected function dispatch($event_name, Event $event)
    {
        $this->get('event_dispatcher')->dispatch($event_name, $event);
    }
}
