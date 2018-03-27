<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Templating\Templates;

abstract class Template
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var \Application\DeskPRO\Templating\Templates\TemplateCode
     */
    private $template_code;

    /**
     * @var \Application\DeskPRO\Templating\Templates\TemplateCode
     */
    private $orig_template_code;

    /**
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    abstract public function exists();

    /**
     * @return string
     */
    abstract public function isCustom();

    /**
     * @return string
     */
    abstract public function getContent();

    /**
     * @return string
     */
    abstract public function getOriginalContent();

    /**
     * @return mixed
     */
    abstract public function getOriginalName();

    /**
     * @return string
     */
    abstract public function getType();

    /**
     * @return EmailTemplateCode|TemplateCode
     */
    public function getTemplateCode()
    {
        if ($this->template_code !== null) {
            return $this->template_code;
        }

        if ($this->getType() == 'email') {
            $this->template_code = new EmailTemplateCode($this->getContent());
        } else {
            $this->template_code = new TemplateCode($this->getContent());
        }

        return $this->template_code;
    }

    /**
     * @return EmailTemplateCode|TemplateCode
     */
    public function getOriginalTemplateCode()
    {
        if (!$this->isCustom()) {
            return;
        }

        if ($this->orig_template_code !== null) {
            return $this->orig_template_code;
        }

        if ($this->getType() == 'email') {
            $this->orig_template_code = new EmailTemplateCode($this->getOriginalContent());
        } else {
            $this->orig_template_code = new TemplateCode($this->getOriginalContent());
        }

        return $this->orig_template_code;
    }
}
