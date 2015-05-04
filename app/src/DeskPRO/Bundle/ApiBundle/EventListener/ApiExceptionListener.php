<?php
/**************************************************************************\
 * | DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
 * | a British company located in London, England.                            |
 * |                                                                          |
 * | All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
 * |                                                                          |
 * | The license agreement under which this software is released              |
 * | can be found at http://www.deskpro.com/license                           |
 * |                                                                          |
 * | By using this software, you acknowledge having read the license          |
 * | and agree to be bound thereby.                                           |
 * |                                                                          |
 * | Please note that DeskPRO is not free software. We release the full       |
 * | source code for our software because we trust our users to pay us for    |
 * | the huge investment in time and energy that has gone into both creating  |
 * | this software and supporting our customers. By providing the source code |
 * | we preserve our customers' ability to modify, audit and learn from our   |
 * | work. We have been developing DeskPRO since 2001, please help us make it |
 * | another decade.                                                          |
 * |                                                                          |
 * | Like the work you see? Think you could make it better? We are always     |
 * | looking for great developers to join us: http://www.deskpro.com/jobs/    |
 * |                                                                          |
 * | ~ Thanks, Everyone at Team DeskPRO                                       |
 * \**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\EventListener;


use Application\DeskPRO\HttpKernel\HttpKernel;
use DeskPRO\Bundle\ApiBundle\Error\ErrorCodeFactory;
use DeskPRO\Bundle\ApiBundle\Error\ErrorMessageFactory;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use DeskPRO\Bundle\ApiBundle\View\ApiViewRepresentationFactory;
use FOS\RestBundle\View\View;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ApiExceptionListener implements EventSubscriberInterface
{
    public function onException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();
        $exception = $event->getException();

        $query = array();
        if ($request->query->has(JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM)) {
            $query[JsonHeadersResponseListener::INCLUDE_HEADERS_PARAM] = 1;
        }

        $sub_request = $request->duplicate(
            $query,
            null,
            array(
                '_controller' => 'DeskPRO\Bundle\ApiBundle\Controller\ExceptionController::showAction',
                'exception' => $exception
            )
        );
        $sub_request->setMethod('GET');

        $response = $event->getKernel()->handle($sub_request, HttpKernel::SUB_REQUEST, false);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::EXCEPTION => array('onException', 128)
        );
    }
}
