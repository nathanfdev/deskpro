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

namespace DeskPRO\Bundle\AppBundle\Form\Error;

use Application\DeskPRO\Translate\Translate;
use Symfony\Component\Form\FormError;

/**
 * Class ErrorMessageFactory.
 */
class ErrorMessageFactory
{
    /**
     * @var Translate
     */
    private $translate;

    /**
     * @var string
     */
    private $prefix;

    /**
     * Constructor.
     *
     * @param Translate $translate
     * @param string    $prefix
     */
    public function __construct(Translate $translate, $prefix)
    {
        $this->translate = $translate;
        $this->prefix    = $prefix;
    }

    /**
     * @param string $errorCode
     * @param array  $params
     *
     * @return string
     */
    public function createMessage($errorCode, array $params = [])
    {
        return $this->translate->phrase($this->prefix.$errorCode, $params) ?: $errorCode;
    }

    /**
     * @param string    $errorCode
     * @param FormError $formError
     *
     * @return string
     */
    public function createFormErrorMessage($errorCode, FormError $formError)
    {
        return $this->createMessage($errorCode, $this->parseParams($formError->getMessageParameters()));
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
