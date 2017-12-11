<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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
            return 'SendmailBundle:'.$this->templateFile;
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
}
