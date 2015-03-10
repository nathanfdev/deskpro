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


use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\ApiBundle\Error\Exception\InvalidFormException;
use FOS\RestBundle\View\View;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\FlattenException as HttpFlattenException;
use Symfony\Component\Debug\Exception\FlattenException as DebugFlattenException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Zend\Server\Reflection\ReflectionClass;

class ExceptionController extends BaseController
{
    public function showAction(\Exception $exception)
    {
        $errors_array = array();
        if ($exception instanceof InvalidFormException) {
            $errors_array = $this->generateFormErrors($exception->getForm());
        }

        $status = $exception instanceof HttpException ? $exception->getStatusCode() : 500;
        $code = $this->getErrorCodeFactory()->getErrorCodeForException($exception);
        $message = $this->getErrorMessageFactory()->createMessage($code);

        // $exception has "getHeaders()" that we are interested in using

        $representation = $this->createErrorRepresentation(
            $status,
            $code,
            $message,
            $errors_array
        );

        return View::create($representation, $status);
    }

    private function generateFormErrors(FormInterface $form)
    {
        $array = array();
        foreach ($form->getErrors(true, true) as $error) {
            $constraint_violation = $error->getCause();
            if (!$constraint_violation->getPropertyPath()) {
                // TODO: we should collect these into an array and use the "_global" field name
                // to display global errors
                continue;
            }
            // strip "data." from the beginning
            $property = substr($constraint_violation->getPropertyPath(), 5);
            $array[$property] = array(
                'code' => $code = $constraint_violation->getMessage(),
                'message' => $this->getErrorMessageFactory()->createMessage($code),
            );
        }

        return $array;
    }

    /**
     * @return \DeskPRO\Bundle\ApiBundle\Error\ErrorCodeFactory
     */
    protected function getErrorCodeFactory()
    {
        return $this->get('error_code_factory');
    }

    /**
     * @return \DeskPRO\Bundle\ApiBundle\Error\ErrorMessageFactory
     */
    protected function getErrorMessageFactory()
    {
        return $this->get('error_message_factory');

    }
}
