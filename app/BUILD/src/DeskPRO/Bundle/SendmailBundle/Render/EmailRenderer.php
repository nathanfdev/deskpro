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

namespace DeskPRO\Bundle\SendmailBundle\Render;

use Application\DeskPRO\Templating\Templates\EmailTemplateCode;
use DeskPRO\Bundle\AppBundle\Serializer\Sideload\SideloadSerializationContext;
use DeskPRO\Bundle\SendmailBundle\View\Model\EmailBaseType;
use JMS\Serializer\Serializer;
use Symfony\Component\Templating\EngineInterface;

class EmailRenderer
{
    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * @var EngineInterface
     */
    private $templateEngine;

    public function __construct(Serializer $serializer, EngineInterface $engine)
    {
        $this->setSerializer($serializer);
        $this->setTemplateEngine($engine);
    }

    /**
     * @return Serializer
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @param Serializer $serializer
     *
     * @return EmailRenderer
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;

        return $this;
    }

    /**
     * @return EngineInterface
     */
    public function getTemplateEngine()
    {
        return $this->templateEngine;
    }

    /**
     * @param EngineInterface $templateEngine
     *
     * @return EmailRenderer
     */
    public function setTemplateEngine($templateEngine)
    {
        $this->templateEngine = $templateEngine;

        return $this;
    }

    /**
     * @param string        $templateName
     * @param EmailBaseType $model
     *
     * @return EmailTemplateCode
     */
    public function render($templateName, EmailBaseType $model)
    {
        $vars = $this->getSerializer()->toArray($model, new SideloadSerializationContext());

        return new EmailTemplateCode($this->getTemplateEngine()->render($templateName, $vars));
    }

    /**
     * @param EmailBaseType $model
     *
     * @return array
     */
    public function getStructure(EmailBaseType $model)
    {
        //        $metadataFactory = $this->getSerializer()->getMetadataFactory();
//        $propertyNamingStrategy = new CamelCaseNamingStrategy();
//        $docCommentExtractor = new DocCommentExtractor();
//        $parser = new JmsMetadataParser($metadataFactory, $propertyNamingStrategy, $docCommentExtractor);
//        return $parser->parse(['class' => get_class($model), 'groups' => []]);
        $this->get('dp_api_doc.parser.jms_metadata_parser');

        return $this->getSerializer()->toArray($model, new SideloadSerializationContext());
    }
}
