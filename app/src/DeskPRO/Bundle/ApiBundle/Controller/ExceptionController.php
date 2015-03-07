<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 */

namespace DeskPRO\Bundle\ApiBundle\Controller;


use FOS\RestBundle\View\View;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException as HttpFlattenException;
use Symfony\Component\Debug\Exception\FlattenException as DebugFlattenException;

class ExceptionController extends BaseController
{
    public function showAction(Request $request, $exception)
    {
        /**
         * Validates that the exception that is handled by the Exception controller is either:
         * a DebugFlattenException
         * or HttpFlattenException
         * No type hinting due to a BC change in symfony/symfony 2.3.5.
         */
        if (!$exception instanceof DebugFlattenException && !$exception instanceof HttpFlattenException) {
            throw new \InvalidArgumentException(sprintf(
                'ExceptionController::showAction can only accept some exceptions (%s, %s), "%s" given',
                'Symfony\Component\HttpKernel\Exception\FlattenException',
                'Symfony\Component\Debug\Exception\FlattenException',
                get_class($exception)
            ));
        }

        $status = $exception->getStatusCode();
        $code = array_key_exists($status, Response::$statusTexts) ? Response::$statusTexts[$status] : 'error';

        // $code above is the default code (just the http response code standard message)
        // however, we will be using our own method of creating a response code
        // which will be the $exception->getMessage(). We then translate that code
        // with our translator to make the message!

        // if exception->getPrevious() instanceof InvalidFormException, we will
        // fetch the form from the exception and pass it along in the errors to create
        // the representation.

        $representation = $this->createErrorRepresentation(
            $status,
            $code,
            $exception->getMessage(),
            array()
        );

        return View::create($representation, $status);
    }
}
