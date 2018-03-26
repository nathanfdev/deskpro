<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Templating\Templates;

class EmailTemplateFile extends TemplateFile
{
    /**
     * @var \Application\DeskPRO\Templating\Templates\EmailTemplateCode
     */
    private $email_code;

    private function initCode()
    {
        if ($this->email_code !== null) {
            return;
        }

        $this->email_code = new EmailTemplateCode($this->getContent());
    }

    public function getSubject()
    {
        $this->initCode();

        return $this->email_code->getSubject();
    }

    public function getBody()
    {
        $this->initCode();

        return $this->email_code->getBody();
    }
}
