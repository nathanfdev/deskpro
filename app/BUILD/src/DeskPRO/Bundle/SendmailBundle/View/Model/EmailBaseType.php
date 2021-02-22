<?php

namespace DeskPRO\Bundle\SendmailBundle\View\Model;

use DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person;
use JMS\Serializer\Annotation as JMS;
use ReflectionClass;
use Zend\Filter\Word\CamelCaseToUnderscore;

abstract class EmailBaseType
{
    /**
     * Email recipient.
     *
     * @JMS\Type("DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person")
     *
     * @var Person
     */
    protected $recipient;

    /**
     * Email recipients.
     *
     * @JMS\Type("array<DeskPRO\Bundle\AppBundle\Serializer\Model\Person\Person>")
     *
     * @var array
     */
    protected $recipients;

    protected $templateFile = '';

    /**
     * Site Url.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $siteUrl;

    /**
     * Site Name.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $siteName;

    /**
     * DeskPro Url.
     *
     * @JMS\Type("string")
     *
     * @var string
     */
    protected $deskproUrl;

    public function getTemplate()
    {
        $reflect   = new ReflectionClass($this);
        $inflector = new CamelCaseToUnderscore();

        if ($this->templateFile) {
            if (strpos($this->templateFile, 'SendmailBundle:') !== 0) {
                return 'SendmailBundle:'.$this->templateFile;
            }

            return $this->templateFile;
        }

        return 'SendmailBundle:email.'.strtolower($inflector->filter($reflect->getShortName())).'.html.twig';
    }

    /**
     * Should not be used in general cases.
     *
     * @param $templateFile
     */
    public function setTemplateFile($templateFile)
    {
        $this->templateFile = $templateFile;
    }

    public function setRecipient(Person $recipient)
    {
        $this->recipient = $recipient;
    }

    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @param $siteUrl
     */
    public function setSiteUrl($siteUrl)
    {
        $this->siteUrl = $siteUrl;
    }

    /**
     * @param $siteName
     */
    public function setSiteName($siteName)
    {
        $this->siteName = $siteName;
    }

    /**
     * @param string $deskproUrl
     */
    public function setDeskproUrl($deskproUrl)
    {
        $this->deskproUrl = $deskproUrl;
    }

    /**
     * @return string
     */
    public function getEventCodeType()
    {
        return '';
    }
}
