<?php

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
