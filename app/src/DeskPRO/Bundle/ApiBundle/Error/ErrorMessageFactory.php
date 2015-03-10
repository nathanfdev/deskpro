<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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

namespace DeskPRO\Bundle\ApiBundle\Error;

use Application\DeskPRO\Translate\Translate;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Form\Extension\Validator\Constraints\Form as FormConstraint;

class ErrorMessageFactory
{
    /**
     * @var Translate
     */
    private $translate;

    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    public function createMessage($error_code, array $params = array())
    {
        if ($message = $this->translate->phrase('api.error_codes.'.$error_code, $params)) {
            return $message;
        }

        return $error_code;
    }

    public function createFormErrorMessage($error_code, FormError $form_error)
    {
        $params = $this->parseParams($form_error->getMessageParameters());

        if ($form_error->getMessage() === 'This form should not contain extra fields.') {
            $error_code = ApiErrors::EXTRA_FIELDS;
        }

        return $this->createMessage($error_code, $params);
    }

    protected function parseParams(array $array = array())
    {
        $new_array = array();

        foreach ($array as $key => $val) {
            preg_match('#\{\{\s*([a-zA-Z0-9_]+)\s*\}\}#', $key, $matches);
            if (isset($matches[1])) {
                $key = $matches[1];
            }
            $new_array[$key] = $val;
        }

        return $new_array;
    }
}
