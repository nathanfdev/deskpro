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

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Application\DeskPRO\Translate\Translate;
use Symfony\Component\Form\FormError;

/**
 * Class ErrorMessageFactory.
 */
class ErrorMessageFactory
{
    const PREFIX_API          = 'api.error_codes.';
    const PREFIX_PORTAL_FORMS = 'portal.forms.error_';

    /**
     * @var Translate
     */
    private $translate;

    /**
     * Constructor.
     *
     * @param Translate $translate
     */
    public function __construct(Translate $translate)
    {
        $this->translate = $translate;
    }

    /**
     * @param string $codePrefix
     * @param string $errorCode
     * @param array  $params
     *
     * @return string
     */
    public function createMessage($codePrefix, $errorCode, array $params = [])
    {
        $message = $this->translate->phrase($codePrefix.$errorCode, $params);

        return $message ?: $errorCode;
    }

    /**
     * @param string    $codePrefix
     * @param string    $errorCode
     * @param FormError $formError
     *
     * @return string
     */
    public function createFormErrorMessage($codePrefix, $errorCode, FormError $formError)
    {
        $params = $this->parseParams($formError->getMessageParameters());

        if ($formError->getMessage() === 'This form should not contain extra fields.') {
            $errorCode = ErrorsCodes::EXTRA_FIELDS;
        }

        return $this->createMessage($codePrefix, $errorCode, $params);
    }

    /**
     * @param array $array
     *
     * @return array
     */
    public function parseParams(array $array = [])
    {
        $new = [];
        foreach ($array as $key => $val) {
            preg_match('#\{\{\s*([a-zA-Z0-9_]+)\s*\}\}#', $key, $matches);
            if (isset($matches[1])) {
                $key = $matches[1];
            }

            $new[$key] = $val;
        }

        return $new;
    }
}
