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

use DeskPRO\Bundle\ApiBundle\Error\ApiErrors;
use DeskPRO\Bundle\AppBundle\Validator\Constraints\Type;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\FormError;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;

class ValidatorErrorCodeFactory
{
    public static $static_replacements = array(
        'This form should not contain extra fields.' => ApiErrors::EXTRA_FIELDS
    );

    public function getConstraintErrorCode(ConstraintViolation $constraint)
    {
        if ($code = $constraint->getMessage()) {
            return $this->filterCode($code);
        }

        return ApiErrors::CONSTRAINT_FALLBACK;
    }

    public function getFormErrorCode(FormError $form_error)
    {
        $cause = $form_error->getCause();
        if ($cause instanceof ConstraintViolation) {
            if ($cause->getCause() instanceof TransformationFailedException) {
                return ApiErrors::INVALID_DATA_TYPE;
            }
        }

        if ($code = $form_error->getMessage()) {
            return $this->filterCode($code);
        }

        return ApiErrors::CONSTRAINT_FALLBACK;
    }

    /**
     * @param $code
     * @return mixed
     */
    private function filterCode($code)
    {
        if (array_key_exists($code, self::$static_replacements)) {
            $code = self::$static_replacements[$code];
        }

        return $code;
    }
}
