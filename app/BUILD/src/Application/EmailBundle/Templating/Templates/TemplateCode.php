<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Templating\Templates;

/**
 * Represents any template code.
 */
class TemplateCode
{
    /** @var string */
    private $code;

    public function __construct($code = null)
    {
        $this->code = '';

        if ($code) {
            $this->setCode($code);
        }
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     */
    public function setCode($code)
    {
        $this->code = trim($code);
    }
}
